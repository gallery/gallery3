<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Gallery_Controller_Rest_Items extends Controller_Rest {
  /**
   * This resource represents a Model_Item object.  If the item is an album, it's
   * considered both an object *and* a collection whose members are the album's items.
   *
   * GET displays an item (if neither "ancestors_for" nor "urls" are given)
   *   random=true
   *     Return a single random item.
   *   scope=direct (default), scope=all
   *     Return member items that are immediately under this one ("direct") or anywhere
   *     under this one ("all").
   *   name=<substring>
   *     Only return member items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit member items to the types in this list (e.g. "type=photo,movie").
   *   The id defaults to the root item if neither "ancestors_for" nor "urls" are given.
   *
   * GET displays a collection of items (if "ancestors_for" or "urls" are given)
   *   ancestors_for=url
   *     Return the ancestors of the specified item.  If specified,
   *     all other query parameters described below will be ignored.
   *     This is typically used to create breadcrumbs for an item.
   *   urls=["url1","url2","url3"]
   *     Return items that match the specified urls.  If specified,
   *     the "start" and "num" parameters will be ignored.
   *     This is typically used to return the member detail.
   *   name=<substring>
   *     Only return member items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit member items to the types in this list (e.g. "type=photo,movie").
   *   Unlike other collections, "expand_members" defaults to true (backward-compatible with v3.0).
   *
   * PUT
   *   entity
   *     Edit the item
   *   file
   *     Replace the item's data file (only for photos or movies)
   *   members
   *     Reorder the items in an album (only for albums with sort_column=weight)
   *
   * POST
   *   entity
   *     Add an item (required).  A parent album is required, which can be specified either
   *     by the <id> or a "parent" field with the parent's REST URL.
   *   file
   *     Add an item's data file (required for movies and photos)
   *
   * DELETE removes the item entirely (no parameters accepted).
   */

  /**
   * GET the item's entity.
   */
  public static function get_entity($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    $data = $item->as_array();

    // Convert "parent_id" to "parent" REST URL.
    if ($item->parent->loaded()) {
      $data["parent"] = RestAPI::url("items", $item->parent_id);
    }
    unset($data["parent_id"]);

    // Convert "album_cover_item_id" to "album_cover" REST URL.
    if ($item->album_cover()) {
      $data["album_cover"] = RestAPI::url("items", $item->album_cover_item_id);
    }
    unset($data["album_cover_item_id"]);

    // Convert "owner_id" to "owner" REST URL.
    $owner = Identity::lookup_user($item->owner_id);
    if (Identity::can_view_profile($owner)) {
      $data["owner"] = RestAPI::url("users", $owner->id);
    }
    unset($data["owner_id"]);

    // Generate/remove the full-size fields.
    if (Access::can("view_full", $item) && !$item->is_album()) {
      $data["file_url"] =
        RestAPI::url("data", $id, array("size" => "full", "m" => filemtime($item->file_path())));
      $data["file_size"] = filesize($item->file_path());
      if (Access::user_can(Identity::guest(), "view_full", $item)) {
        $data["file_url_public"] = $item->file_url(true);
      }
    } else {
      unset($data["width"], $data["height"]);
    }

    // Generate/remove the resize fields.
    if (Access::can("view", $item) && $item->is_photo()) {
      $data["resize_url"] =
        RestAPI::url("data", $id, array("size" => "resize", "m" => filemtime($item->resize_path())));
      $data["resize_size"] = filesize($item->resize_path());
      if (Access::user_can(Identity::guest(), "view", $item)) {
        $data["resize_url_public"] = $item->resize_url(true);
      }
    } else {
      unset($data["resize_width"], $data["resize_height"]);
    }

    // Generate/remove the thumb fields.
    if (Access::can("view", $item) && $item->has_thumb()) {
      $data["thumb_url"] =
        RestAPI::url("data", $id, array("size" => "thumb", "m" => filemtime($item->thumb_path())));
      $data["thumb_size"] = filesize($item->thumb_path());
      if (Access::user_can(Identity::guest(), "view", $item)) {
        $data["thumb_url_public"] = $item->thumb_url(true);
      }
    } else {
      unset($data["thumb_width"], $data["thumb_height"]);
    }

    $data["can_edit"] = Access::can("edit", $item);
    $data["can_add"] = Access::can("add", $item);
    $data["web_url"] = $item->abs_url();

    // Elide some internal-only data that is going to cause confusion in the client.
    $non_rest_keys = array("relative_path_cache", "relative_url_cache", "left_ptr",
      "right_ptr", "thumb_dirty", "resize_dirty", "weight");
    foreach (array_keys($data) as $key) {
      // Remove non-rest keys and view_1, view_2, etc.
      if (in_array($key, $non_rest_keys) || preg_match("/^view_\d+/", $key)) {
        unset($data[$key]);
      }
    }

    return $data;
  }

  /**
   * PUT the item's entity (and possibly file).  This edits the item model.
   */
  public static function put_entity($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    // Get the entity, check the type.
    $entity = $params["entity"];
    if (property_exists($entity, "type")) {
      throw Rest_Exception::factory(400, array("type" => "read_only"));
    }

    // If parent set, re-parent the item.
    if (property_exists($entity, "parent")) {
      list ($tmp_type, $tmp_id) = RestAPI::resolve($entity->parent);
      if ($tmp_type != "items") {
        throw Rest_Exception::factory(400, array("parent" => "invalid"));
      }

      $tmp = ORM::factory("Item", $tmp_id);
      Access::required("add", $tmp);

      $item->parent_id = $tmp_id;
    }

    switch ($item->type) {
    case "photo":
    case "movie":
      // Replace the data file, if specified.
      if (!empty($params["file"])) {
        $item->set_data_file($params["file"]["tmp_name"]);
      }

      $fields = array("name", "title", "description", "slug", "captured",
        "view_count", "thumb_dirty", "resize_dirty");
      break;

    case "album":
      // Change the album cover, if specified.
      if (property_exists($entity, "album_cover")) {
        list ($tmp_type, $tmp_id) = RestAPI::resolve($entity->album_cover);
        if ($tmp_type != "items") {
          throw Rest_Exception::factory(400, array("album_cover" => "invalid"));
        }

        $tmp = ORM::factory("Item", $tmp_id);
        Access::required("view", $tmp);

        $item->album_cover_item_id = $tmp_id;
      }

      $fields = array("name", "title", "description", "slug", "sort_column", "sort_order",
        "view_count", "thumb_dirty");
      break;

    default:
      throw Rest_Exception::factory(400, array("type" => "invalid"));
    }

    // Add the allowed entity fields.
    foreach ($fields as $field) {
      if (property_exists($entity, $field)) {
        $item->$field = $entity->$field;
      }
    }

    $item->save();
  }

  /**
   * POST an item's entity (and possibly file).  This generates a new item model.
   */
  public static function post_entity($id, $params) {
    if (!isset($id) && property_exists($params["entity"], "parent")) {
      list ($p_type, $p_id, $p_params) = RestAPI::resolve($params["entity"]->parent);
      if ($p_type != "items") {
        throw Rest_Exception::factory(400, array("parent" => "invalid"));
      }
      $id = $p_id;
    } else {
      throw Rest_Exception::factory(400, array("parent" => "required"));
    }

    $parent = ORM::factory("Item", $id);
    Access::required("add", $parent);

    // Get the entity, check the type (catch it here before we look for it and fire a 500).
    $entity = $params["entity"];
    if (!property_exists($entity, "type")) {
      throw Rest_Exception::factory(400, array("type" => "required"));
    }

    // Build the item model.
    $item = ORM::factory("Item");
    $item->parent_id = $id;
    $item->type = $entity->type;

    switch ($item->type) {
    case "photo":
    case "movie":
      // Process the data file, and (pre-)set the item name from the filename.
      // If specified in the entity, this will be overwritten.
      if (empty($params["file"])) {
        throw Rest_Exception::factory(400, array("file" => "required"));
      }
      $item->set_data_file($params["file"]["tmp_name"]);
      $item->name = $params["file"]["name"];

      $fields = array("name", "title", "description", "slug", "captured");
      break;

    case "album":
      $fields = array("name", "title", "description", "slug", "sort_column", "sort_order");
      break;

    default:
      throw Rest_Exception::factory(400, array("type" => "invalid"));
    }

    // Add the allowed entity fields.
    foreach ($fields as $field) {
      if (property_exists($entity, $field)) {
        $item->$field = $entity->$field;
      }
    }

    $item->save();

    // Success!  Return the new resource triad.
    return array("items", $item->id);
  }

  /**
   * DELETE the item.
   */
  public static function delete($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    $item->delete();
  }

  /**
   * GET the members of the items collection.
   */
  public static function get_members($id, $params) {
    $types = Arr::get($params, "type");
    $name = Arr::get($params, "name");

    $data = array();
    if ($ancestors_for = Arr::get($params, "ancestors_for")) {
      // Members are the ancestors of the url given.
      list ($i_type, $i_id, $i_params) = RestAPI::resolve($ancestors_for);
      if ($i_type != "items") {
        throw Rest_Exception::factory(400, array("ancestors_for" => "invalid"));
      }

      $item = ORM::factory("Item", $i_id);
      Access::required("view", $item);

      $members = $item->parents->viewable()->find_all();
      foreach ($members as $member) {
        $data[] = array("items", $member->id);
      }
    } else if ($urls = Arr::get($params, "urls")) {
      // Members are taken from a list of urls, filtered by name and type.
      // @todo: json_decode is what was used in 3.0, but should we allow comma-separated lists, too?
      foreach (json_decode($urls, true) as $url) {
        list ($m_type, $m_id, $m_params) = RestAPI::resolve($url);
        if ($m_type != "items") {
          throw Rest_Exception::factory(400, array("urls" => "invalid"));
        }

        $member = ORM::factory("Item", $m_id);
        Access::required("view", $member);

        if ((empty($types) || in_array($member->type, $types)) &&
            (empty($name) || (strpos($member->name, $name) !== false))) {
          $data[] = array("items", $member->id);
        }
      }
    } else {
      $item = ORM::factory("Item", $id);
      Access::required("view", $item);

      // Only albums can have member lists.
      if (!$item->is_album()) {
        return null;
      }

      $scope = Arr::get($params, "scope", "direct");
      if (!in_array($scope, array("direct", "all"))) {
        throw Rest_Exception::factory(400, array("scope" => "invalid"));
      }

      $members = ($scope == "direct") ? $item->children : $item->descendants;
      $members->viewable()
        ->limit(Arr::get($params, "num", static::$default_params["num"]))
        ->offset(Arr::get($params, "start", static::$default_params["start"]));

      if (isset($types)) {
        $members->where("type", "IN", $types);
      }

      if (isset($name)) {
        $members->where("name", "LIKE", "%" . Database::escape_for_like($name) . "%");
      }

      $data = array();
      $key = 0;
      $use_weights = (($item->sort_column == "weight") && ($scope == "direct"));
      foreach ($members->find_all() as $member) {
        // If the album's sort is "weight", use the weights as the array keys.
        $data[$use_weights ? $member->weight : $key++] =
          array("items", $member->id);
      }
    }

    return $data;
  }

  /**
   * PUT the item's members.  This reorders the items by their weights.
   */
  public static function put_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    if (!$item->is_album() || ($item->sort_column != "weight")) {
      throw Rest_Exception::factory(400, array("members" => "cannot_reorder"));
    }

    // Resolve our members list into an array of weights => ids.
    $members_array = RestAPI::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        return (($type == "items") && (ORM::factory("Item", $id)->parent_id == $data)) ? $id : null;
      }, $item->id);

    // Sort members by their weights (given by their keys).
    ksort($members_array);

    // We're clear to go - this might be a race condition, so use DB over ORM to be a bit faster.
    // Even if we lose the race, it's relatively harmless and the action is idempotent (i.e. just
    // sending the same request again should fix it).
    foreach ($members_array as $m_weight => $m_id) {
      if (DB::select()
          ->from("items")
          ->where("parent_id", "=", $item->id)
          ->where("id", "<>", $m_id)
          ->where("weight", "=", $m_weight)
          ->execute()->count()) {
        // One of its siblings already has this weight - make a hole.
        DB::update("items")
          ->set(array("weight" => DB::expr("`weight` + 1")))
          ->where("parent_id", "=", $item->id)
          ->where("weight", ">=", $m_weight)
          ->execute();
      }
      // Update the member weight.  We check parent_id again to make sure the item hasn't been
      // reparented (i.e. protect against "losing the race").
      DB::update("items")
        ->set(array("weight" => $m_weight))
        ->where("id", "=", $m_id)
        ->where("parent_id", "=", $item->id)
        ->execute();
    }
  }

  /**
   * Override Controller_Rest::before() to use the "random" parameter, expand members
   * by default for "urls" and "ancestors_for", and GET the root item by default.
   */
  public function before() {
    parent::before();

    // If the "random" parameter is set, get a random item id.
    if ($this->request->query("random")) {
      // This doesn't always work, so keep trying until it does...
      do {
        $id = Item::random_query()->offset(0)->limit(1)->find()->id;
      } while (!$id);

      $this->rest_id = $id;

      // Remove the "random" query parameter so it doesn't appear in URLs downstream.
      $query = $this->request->query();
      unset($query["random"]);
      $this->request->query($query);
    }

    if ($this->request->query("urls") || $this->request->query("ancestors_for")) {
      // If "urls" or "ancestors_for" parameters are set, expand members by default.
      static::$default_params["expand_members"] = true;
    } else if (($this->request->method() == HTTP_Request::GET) && empty($this->rest_id)) {
      // For GET, default to the root item if no id is given.
      $this->rest_id = Item::root()->id;
    }
  }
}
