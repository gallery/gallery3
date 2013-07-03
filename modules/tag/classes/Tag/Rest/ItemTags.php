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
class Tag_Rest_ItemTags extends Rest {
  /**
   * This resource represents a collection of tags on a specified item.
   *
   * GET displays the collection of tags (id required)
   *   name=<substring>
   *     Only return tags that start with this substring.
   *     This is typically used for tag autocomplete.
   *   order=count, order=name
   *     Return the tags in decreasing order by count ("count", typically used for tag
   *     clouds) or increasing order by name ("name", typically used for autocomplete
   *     or other alphabetic lists).  If the "name" parameter is also set, the default
   *     is "name"; otherwise, the default is "count".
   *
   * PUT
   *   members
   *     Replace the collection of tags on the item with this list
   *
   * POST
   *   members
   *     Add the list of tags to the item.  Unlike PUT, this only *adds* tags.
   *     Since there is no entity function, this is only accessible using relationships.
   *
   * DELETE removes all tags from the item (no parameters accepted).
   *
   * RELATIONSHIPS: "item_tags" is the "tags" relationship of an "items" resource.
   */

  public static $relationships = array("Items" => "Tags");

  /**
   * GET the tag members of the item_tags resource.
   * @see  Rest_Tags::get_members().
   */
  public function get_members() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("view", $item);

    $members = $item->tags;

    if (isset($this->params["name"])) {
      $members->where("name", "LIKE", Database::escape_for_like($this->params["name"]) . "%");
      $default_order = "name";  // Useful for autocomplete
    } else {
      $default_order = "count"; // Useful for cloud
    }

    switch (Arr::get($this->params, "order", $default_order)) {
    case "name":
      $members->order_by("name", "ASC");

    case "count":  // default as set by Model_Tag::$_sorting - do nothing.
      break;

    default:
      throw Rest_Exception::factory(400, array("order" => "invalid"));
    }

    $this->members_info["count"] = $members->reset(false)->count_all();
    $members = $members
      ->limit($this->members_info["num"])
      ->offset($this->members_info["start"])
      ->find_all();

    $data = array();
    foreach ($members as $member) {
      $data[] = Rest::factory("Tags", $member->id);
    }

    return $data;
  }

  /**
   * PUT the entity of the item_tags resource.  This replaces the tag list with the one given,
   * which is a comma-separated list in the "names" field of the entity.
   * @see  TagEvent::item_edit_form_completed(), which does a similar task.
   */
  public function put_entity() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    if (!empty($this->params["members"])) {
      throw Rest_Exception::factory(400, array("entity" => "entity_and_members_sent"));
    }

    if (!property_exists($this->params["entity"], "names") ||
        !($names = array_filter(array_map("trim", explode(",", $this->params["entity"]->names))))) {
      throw Rest_Exception::factory(400, array("entity" => "invalid"));
    }

    // Clear all tags from the item, then add the new set.
    Tag::clear_all($item);
    foreach ($names as $name) {
      Tag::add($item, $name);
    }
    Tag::compact();
  }

  /**
   * PUT the tag members of the item_tags resource.  This replaces the tag list with the one given.
   * @see  TagEvent::item_edit_form_completed(), which does a similar task.
   */
  public function put_members() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    // Convert our members list into an array of item ids.
    $names = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Tag", $member_rest->id);
      if (($member_rest->type != "Tags") || !$member->loaded()) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $names[$key] = $member->name;
    }

    // Clear all tags from the item, then add the new set.
    Tag::clear_all($item);
    foreach ($names as $name) {
      Tag::add($item, $name);
    }
    Tag::compact();
  }

  /**
   * POST the entity of the item_tags resource.  Unlike PUT, this only *adds* tags to the item.
   */
  public function post_entity() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    if (!empty($this->params["members"])) {
      throw Rest_Exception::factory(400, array("entity" => "entity_and_members_sent"));
    }

    if (!property_exists($this->params["entity"], "names") ||
        !($names = array_filter(array_map("trim", explode(",", $this->params["entity"]->names))))) {
      throw Rest_Exception::factory(400, array("entity" => "invalid"));
    }

    // Add the tags to the item.
    foreach ($names as $name) {
      Tag::add($item, $name);
    }
    Tag::compact();
  }

  /**
   * POST tag members of the item_tags resource.  Unlike PUT, this only *adds* tags to the item.
   */
  public function post_members() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    // Convert our members list into an array of item ids.
    $names = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Tag", $member_rest->id);
      if (($member_rest->type != "Tags") || !$member->loaded()) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $names[$key] = $member->name;
    }

    // Add the tags to the item.
    foreach ($names as $name) {
      Tag::add($item, $name);
    }
    Tag::compact();
  }

  /**
   * DELETE removes all tags from the item.
   */
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("edit", $item);

    Tag::clear_all($item);
  }

  /**
   * Overload Rest::get_response() to add an entity of member names if this is a collection.
   */
  public function get_response() {
    $result = parent::get_response();

    // If this is a collection of tags, generate the entity with the comma-separated names.
    if (isset($result["members"])) {
      // Get the tag member names.
      $names = array();
      foreach ($result["members"] as $member_url) {
        $tag_rest = RestAPI::resolve($member_url)->get_entity();
        $names[] = $tag_rest["name"];
      }

      // Add the entity to $result.  Since we want it to go right after url, and since
      // array_splice() doesn't preserve keys, we resort to using array_merge().
      $result = array_merge(array(
          "url"    => $result["url"],
          "entity" => array("names" => implode(",", $names))),
        $result);
    }

    return $result;
  }
}
