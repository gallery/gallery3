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
class Gallery_Rest_Items extends Rest {
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
   *   urls=["url1","url2","url3"]   or   urls=url1,url2,url3
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
  public function get_entity() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("view", $item);

    $data = $item->as_array();

    // Convert "parent_id" to "parent" REST URL.
    if ($item->parent->loaded()) {
      $data["parent"] = Rest::factory("Items", $item->parent_id)->url();
    }
    unset($data["parent_id"]);

    // Convert "album_cover_item_id" to "album_cover" REST URL.
    if ($item->album_cover()) {
      $data["album_cover"] = Rest::factory("Items", $item->album_cover_item_id)->url();
    }
    unset($data["album_cover_item_id"]);

    // Convert "owner_id" to "owner" REST URL.
    $owner = Identity::lookup_user($item->owner_id);
    if (Identity::can_view_profile($owner)) {
      $data["owner"] = Rest::factory("Users", $owner->id)->url();
    }
    unset($data["owner_id"]);

    // Generate/remove the full-size fields.
    if (Access::can("view_full", $item) && !$item->is_album()) {
      $m = file_exists($item->file_path()) ? filemtime($item->file_path()) : 0;
      $data["file_url"] = Rest::factory("Data", $this->id,
        array("size" => "full", "m" => $m))->url();
      $data["file_size"] = file_exists($item->file_path()) ? filesize($item->file_path()) : 0;
      if (Access::user_can(Identity::guest(), "view_full", $item)) {
        $data["file_url_public"] = $item->file_url(true);
      }
    } else {
      unset($data["width"], $data["height"]);
    }

    // Generate/remove the resize fields.
    if (Access::can("view", $item) && $item->is_photo()) {
      $m = file_exists($item->resize_path()) ? filemtime($item->resize_path()) : 0;
      $data["resize_url"] = Rest::factory("Data", $this->id,
        array("size" => "resize", "m" => $m))->url();
      $data["resize_size"] = file_exists($item->resize_path()) ? filesize($item->resize_path()) : 0;
      if (Access::user_can(Identity::guest(), "view", $item)) {
        $data["resize_url_public"] = $item->resize_url(true);
      }
    } else {
      unset($data["resize_width"], $data["resize_height"]);
    }

    // Generate/remove the thumb fields.
    if (Access::can("view", $item)) {
      $m = file_exists($item->thumb_path()) ? filemtime($item->thumb_path()) : 0;
      $data["thumb_url"] = Rest::factory("Data", $this->id,
        array("size" => "thumb", "m" => $m))->url();
      $data["thumb_size"] = file_exists($item->thumb_path()) ? filesize($item->thumb_path()) : 0;
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
  public function put_entity() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    // Get the entity, check the type.
    $entity = $this->params["entity"];
    if (property_exists($entity, "type")) {
      throw Rest_Exception::factory(400, array("type" => "read_only"));
    }

    // If parent set, re-parent the item.
    if (property_exists($entity, "parent")) {
      $parent_rest = RestAPI::resolve($entity->parent);
      if (!$parent_rest || ($parent_rest->type != "Items")) {
        throw Rest_Exception::factory(400, array("parent" => "invalid"));
      }

      $parent = ORM::factory("Item", $parent_rest->id);
      Access::required("add", $parent);

      $item->parent_id = $parent->id;
    }

    switch ($item->type) {
    case "photo":
    case "movie":
      // Replace the data file, if specified.
      if (!empty($this->params["file"])) {
        $item->set_data_file($this->params["file"]["tmp_name"]);
      }

      $fields = array("name", "title", "description", "slug", "captured",
        "view_count", "thumb_dirty", "resize_dirty");
      break;

    case "album":
      // Change the album cover, if specified.
      if (property_exists($entity, "album_cover")) {
        $album_cover_rest = RestAPI::resolve($entity->album_cover);
        if (!$album_cover_rest || ($album_cover_rest->type != "Items")) {
          throw Rest_Exception::factory(400, array("album_cover" => "invalid"));
        }

        $album_cover = ORM::factory("Item", $album_cover_rest->id);
        Access::required("view", $album_cover);

        $item->album_cover_item_id = $album_cover->id;
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
  public function post_entity() {
    if (empty($this->id)) {
      if (property_exists($this->params["entity"], "parent")) {
        $parent_rest = RestAPI::resolve($this->params["entity"]->parent);
        if (!$parent_rest || ($parent_rest->type != "Items")) {
          throw Rest_Exception::factory(400, array("parent" => "invalid"));
        }
        $parent_id = $parent_rest->id;
      } else {
        throw Rest_Exception::factory(400, array("parent" => "required"));
      }
    } else {
      $parent_id = $this->id;
      $this->id = null;
    }

    $parent = ORM::factory("Item", $parent_id);
    Access::required("add", $parent);

    // Get the entity, check the type (catch it here before we look for it and fire a 500).
    $entity = $this->params["entity"];
    if (!property_exists($entity, "type")) {
      throw Rest_Exception::factory(400, array("type" => "required"));
    }

    // Build the item model.
    $item = ORM::factory("Item");
    $item->parent_id = $parent->id;
    $item->type = $entity->type;

    switch ($item->type) {
    case "photo":
    case "movie":
      // Process the data file, and (pre-)set the item name from the filename.
      // If specified in the entity, this will be overwritten.
      if (empty($this->params["file"])) {
        throw Rest_Exception::factory(400, array("file" => "required"));
      }
      $item->set_data_file($this->params["file"]["tmp_name"]);
      $item->name = $this->params["file"]["name"];

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

    // Success!
    $this->id = $item->id;
  }

  /**
   * DELETE the item.
   */
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    $item->delete();
  }

  /**
   * GET the members of the items collection.
   */
  public function get_members() {
    $types = Arr::get($this->params, "type");
    $name = Arr::get($this->params, "name");

    $data = array();
    if ($ancestors_for = Arr::get($this->params, "ancestors_for")) {
      // Members are the ancestors of the url given.
      $item_rest = RestAPI::resolve($ancestors_for);
      if (!$item_rest || ($item_rest->type != "Items")) {
        throw Rest_Exception::factory(400, array("ancestors_for" => "invalid"));
      }

      $item = ORM::factory("Item", $item_rest->id);
      Access::required("view", $item);

      $members = $item->parents->viewable()->find_all();
      foreach ($members as $member) {
        $data[] = Rest::factory("Items", $member->id);
      }

      // Special case: "num" and "start" are ignored.
      $this->members_info["count"] = count($data);
      $this->members_info["num"] = null;
      $this->members_info["start"] = null;
    } else if ($urls = Arr::get($this->params, "urls")) {
      // Members are taken from a list of urls, filtered by name and type.
      // In 3.0, these were json-encoded.  In 3.1, we also allow comma-separated lists,
      // similar to other query params, and use a leading [ or { as a semaphore for json.
      $urls = ((substr($urls, 0, 1) == "[") || (substr($urls, 0, 1) == "{")) ?
        json_decode($urls, true) : explode(",", trim($urls, ","));

      foreach ($urls as $url) {
        $item_rest = RestAPI::resolve($url);
        if (!$item_rest || ($item_rest->type != "Items")) {
          throw Rest_Exception::factory(400, array("urls" => "invalid"));
        }

        $member = ORM::factory("Item", $item_rest->id);
        Access::required("view", $member);

        if ((empty($types) || in_array($member->type, $types)) &&
            (empty($name) || (stripos($member->name, $name) !== false))) {
          $data[] = Rest::factory("Items", $member->id);
        }
      }

      // Special case: "num" and "start" are ignored.
      $this->members_info["count"] = count($data);
      $this->members_info["num"] = null;
      $this->members_info["start"] = null;
    } else {
      $item = ORM::factory("Item", $this->id);
      Access::required("view", $item);

      // Only albums can have member lists.
      if (!$item->is_album()) {
        return null;
      }

      $scope = Arr::get($this->params, "scope", "direct");
      if (!in_array($scope, array("direct", "all"))) {
        throw Rest_Exception::factory(400, array("scope" => "invalid"));
      }

      $members = ($scope == "direct") ? $item->children : $item->descendants;
      $members->viewable();

      if (isset($types)) {
        $members->where("type", "IN", $types);
      }

      if (isset($name)) {
        $members->where("name", "LIKE", "%" . Database::escape_for_like($name) . "%");
      }

      $this->members_info["count"] = $members->reset(false)->count_all();
      $members = $members
        ->limit($this->members_info["num"])
        ->offset($this->members_info["start"])
        ->find_all();

      $key = 0;
      $use_weights = (($item->sort_column == "weight") && ($scope == "direct"));
      foreach ($members as $member) {
        // If the album's sort is "weight", use the weights as the array keys.
        $data[$use_weights ? $member->weight : $key++] = Rest::factory("Items", $member->id);
      }
    }

    return $data;
  }

  /**
   * PUT the item's members.  This reorders the items by their weights.
   */
  public function put_members() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    if (!$item->is_album() || ($item->sort_column != "weight")) {
      throw Rest_Exception::factory(400, array("members" => "cannot_reorder"));
    }

    // Convert our members list into item models.
    $members = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Item", $member_rest->id);
      if (($member_rest->type != "Items") || ($member->parent_id != $item->id)) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $members[$key] = $member;
    }

    // Sort members by their weights (given by their keys).
    ksort($members);

    // We're clear to go - this might be a race condition, so use DB over ORM to be a bit faster.
    // Even if we lose the race, it's relatively harmless and the action is idempotent (i.e. just
    // sending the same request again should fix it).
    foreach ($members as $key => $member) {
      if (DB::select()
          ->from("items")
          ->where("parent_id", "=", $item->id)
          ->where("id", "<>", $member->id)
          ->where("weight", "=", $key)
          ->execute()->count()) {
        // One of its siblings already has this weight - make a hole.
        DB::update("items")
          ->set(array("weight" => DB::expr("`weight` + 1")))
          ->where("parent_id", "=", $item->id)
          ->where("weight", ">=", $key)
          ->execute();
      }
      // Update the member weight.  We check parent_id again to make sure the item hasn't been
      // reparented (i.e. protect against "losing the race").
      DB::update("items")
        ->set(array("weight" => $key))
        ->where("id", "=", $member->id)
        ->where("parent_id", "=", $item->id)
        ->execute();
    }
  }

  /**
   * Override Rest::get_response() to use the "random" parameter, expand members
   * by default for "urls" and "ancestors_for", and default to the root item.
   */
  public function get_response() {
    if (Arr::get($this->params, "random")) {
      // If the "random" parameter is set, get a random item id.
      // This doesn't always work, so keep trying until it does...
      do {
        $this->id = Item::random_query()->offset(0)->limit(1)->find()->id;
      } while (!$this->id);

      // Remove the "random" query parameter so it doesn't appear in URLs downstream.
      unset($this->params["random"]);
    } else if (Arr::get($this->params, "urls") || Arr::get($this->params, "ancestors_for")) {
      // If "urls" or "ancestors_for" parameters are set, expand members by default.
      $this->default_params["expand_members"] = true;
    } else if (!isset($this->id)) {
      // Default to the root item (only if not using "urls" or "ancestors_for").
      $this->id = Item::root()->id;
    }

    return parent::get_response();
  }
}
