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

  /**
   * GET the tag members of the item_tags resource.
   * @see  Rest_Tags::get_members().
   */
  static function get_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    $members = $item->tags
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]));

    if (isset($params["name"])) {
      $members->where("name", "LIKE", Database::escape_for_like($params["name"]) . "%");
      $default_order = "name";  // Useful for autocomplete
    } else {
      $default_order = "count"; // Useful for cloud
    }

    switch (Arr::get($params, "order", $default_order)) {
    case "count":
      $members->order_by("count", "DESC");
      break;

    case "name":
      $members->order_by("name", "ASC");
      break;

    default:
      throw Rest_Exception::factory(400, array("order" => "invalid"));
    }

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("tags", $member->id);
    }

    return $data;
  }

  /**
   * PUT the tag members of the item_tags resource.  This replaces the tag list with the one given.
   * @see  TagEvent::item_edit_form_completed(), which does a similar task.
   */
  static function put_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    // Resolve our members list into an array of tag names.
    $tag_names = RestAPI::resolve_members($params["members"],
      function($type, $id, $params) {
        return ($type == "tags") ? ORM::factory("Tag", $id)->name : false;
      });

    // Clear all tags from the item, then add the new set.
    Tag::clear_all($item);
    foreach ($tag_names as $tag_name) {
      Tag::add($item, $tag_name);
    }
    Tag::compact();
  }

  /**
   * POST tag members of the item_tags resource.  Unlike PUT, this only *adds* tags to the item.
   */
  static function post_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    // Resolve our members list into an array of tag names.
    $tag_names = RestAPI::resolve_members($params["members"],
      function($type, $id, $params) {
        return ($type == "tags") ? ORM::factory("Tag", $id)->name : false;
      });

    // Add the tags to the item.
    foreach ($tag_names as $tag_name) {
      Tag::add($item, $tag_name);
    }
    Tag::compact();
  }

  /**
   * DELETE removes all tags from the item.
   */
  static function delete($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("edit", $item);

    Tag::clear_all($item);
  }

  /**
   * Return the relationship established by item_tags.  This adds "tags"
   * as a relationship of an "items" resource.
   */
  static function relationships($type, $id, $params) {
    return (($type == "items") && (!empty($id))) ?
      array("tags" => array("item_tags", $id, $params)) : null;
  }
}
