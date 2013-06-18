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
class Tag_Controller_Rest_TagItems extends Controller_Rest {
  /**
   * This resource represents a collection of items that all have a specified tag.
   *
   * GET displays the collection of items (id required)
   *   name=<substring>
   *     Only return member items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit member items to the types in this list (e.g. "type=photo,movie").
   *
   * PUT
   *   members
   *     Replace the collection of items on the tag with this list
   *
   * POST
   *   members
   *     Add the tag to the items in the list.  Unlike PUT, this only *adds* items.
   *     Since there is no entity function, this is only accessible using relationships.
   *
   * DELETE removes all items from the tag, which deletes the tag entirely (no parameters accepted).
   *
   * RELATIONSHIPS: "tag_items" is the "items" relationship of a "tags" resource.
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE tag_items.
   */

  /**
   * GET the item members of the tag_items resource.
   * @see  Controller_Rest_Items::get_members().
   */
  static function get_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $members = $tag->items->viewable()
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]));

    if (isset($params["type"])) {
      $members->where("type", "IN", $params["type"]);
    }

    if (isset($params["name"])) {
      $members->where("name", "LIKE", "%" . Database::escape_for_like($params["name"]) . "%");
    }

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("items", $member->id);
    }

    return $data;
  }

  /**
   * PUT the item members of the tag_items resource.  This replaces the list of items with
   * the specified tag, and is only for admins.
   */
  static function put_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item models.
    $items = RestAPI::resolve_members($params["members"],
      function($type, $id, $params) {
        $item = ORM::factory("Item", $id);
        return (($type == "items") && $item->loaded()) ? $item : false;
      });

    // Clear all items from the tag, then add the new set.
    Tag::remove_items($tag);
    foreach ($items as $item) {
      Tag::add($item, $tag->name);
    }
    Tag::compact();
  }

  /**
   * POST item members of the tag_items resource.  Unlike PUT, this only *adds* the tag
   * to the items, and is not admin-only.
   */
  static function post_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item models.
    $items = RestAPI::resolve_members($params["members"],
      function($type, $id, $params) {
        $item = ORM::factory("Item", $id);
        return (($type == "items") && $item->loaded()) ? $item : false;
      });

    // Add the tag to the items.
    foreach ($items as $item) {
      Tag::add($item, $tag->name);
    }
  }

  /**
   * DELETE the tag.  This is only for admins.
   * @see  Controller_Rest_Tags::delete()
   */
  static function delete($id, $params) {
    return RestAPI::resource_func("delete", "tags", $id, $params);
  }

  /**
   * Return the relationship established by tag_items.  This adds "items"
   * as a relationship of a "tags" resource.
   */
  static function relationships($type, $id, $params) {
    return (($type == "tags") && (!empty($id))) ?
      array("items" => array("tag_items", $id, $params)) : null;
  }
}
