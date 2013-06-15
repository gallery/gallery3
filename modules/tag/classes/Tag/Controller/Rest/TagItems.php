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
   * This resource represents a collection of item resources that all have a specified tag.
   *
   * GET can accept the following query parameters:
   *   name=<substring>
   *     Only return items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit the type to types in this list (e.g. "type=photo,movie").
   *     Also limits the types returned in the member collections (i.e. sub-albums).
   *   @see  Controller_Rest_TagItems::get_members()
   *
   * PUT can accept the following post parameters:
   *   members
   *     Replace the collection of items on the tag with this list
   *   @see  Controller_Rest_TagItems::put_members()
   *
   * POST can accept the following post parameters:
   *   members
   *     Add the tag to the items in the list.  Unlike PUT, this only *adds* items.
   *     Since there is no entity function, this is only accessible using relationships.
   *   @see  Controller_Rest_TagItems::post_members()
   *
   * DELETE removes all items from the tag, which deletes the tag entirely (no parameters accepted).
   *   @see  Controller_Rest_TagItems::delete()
   *
   * RELATIONSHIPS: "tag_items" is the "items" relationship of a "tag" resource.
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE tag_items.
   */

  /**
   * GET the item members of the tag_items resource.
   * @see  Controller_Rest_Items::get_members().
   */
  static function get_members($id, $params) {
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
      $data[] = array("item", $member->id);
    }

    return $data;
  }

  /**
   * PUT the item members of the tag_items resource.  This replaces the list of items with
   * the specified tag, and is only for admins.
   */
  static function put_members($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item models.
    $items = Rest::resolve_members($params["members"],
      function($type, $id, $params) {
        $item = ORM::factory("Item", $id);
        return (($type == "item") && $item->loaded()) ? $item : false;
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
    $tag = ORM::factory("Tag", $id);
    if (!$tag->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item models.
    $items = Rest::resolve_members($params["members"],
      function($type, $id, $params) {
        $item = ORM::factory("Item", $id);
        return (($type == "item") && $item->loaded()) ? $item : false;
      });

    // Add the tag to the items.
    foreach ($items as $item) {
      Tag::add($item, $tag->name);
    }
  }

  /**
   * DELETE the tag.  This is only for admins.
   * @see  Controller_Rest_Tag::delete()
   */
  static function delete($id, $params) {
    return Rest::delete("tag", $id, $params);
  }

  /**
   * Return the relationship established by tag_items.  This adds "items"
   * as a relationship of a "tag" resource.
   */
  static function relationships($type, $id, $params) {
    return ($type == "tag") ? array("items" => array("tag_items", $id, $params)) : null;
  }
}
