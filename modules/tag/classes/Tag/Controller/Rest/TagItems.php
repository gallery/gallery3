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
   * DELETE removes all items from the tag, which deletes the tag entirely (no parameters accepted).
   *   @see  Controller_Rest_TagItems::delete()
   *
   * RELATIONSHIPS: "tag_items" is the "items" relationship of a "tag" resource.
   *
   * Note: similar to the standard UI, only admins can DELETE a tag.
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

    if (isset($params["types"])) {
      $members->where("type", "IN", $params["types"]);
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

  /* @todo: add back in deprecated tag_item post.
  static function post($request) {
    $tag = Rest::resolve($request->params->entity->tag);
    $item = Rest::resolve($request->params->entity->item);
    Access::required("view", $item);

    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    Tag::add($item, $tag->name);
    return array(
      "url" => Rest::url("tag_item", $tag, $item),
      "members" => array(
        "tag" => Rest::url("tag", $tag),
        "item" => Rest::url("item", $item)));
  }
  */

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
    return ($type == "tag") ? array("items" => array("tag_items", $id)) : null;
  }
}
