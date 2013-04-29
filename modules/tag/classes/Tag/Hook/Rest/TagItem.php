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
class Tag_Hook_Rest_TagItem {
  static function get($request) {
    list ($tag, $item) = Rest::resolve($request->url);
    return array(
      "url" => $request->url,
      "entity" => array(
        "tag" => Rest::url("tag", $tag),
        "item" => Rest::url("item", $item)));
  }

  static function delete($request) {
    list ($tag, $item) = Rest::resolve($request->url);
    Access::required("edit", $item);
    $tag->remove("items", $item);
    $tag->save();
  }

  static function resolve($tuple) {
    list ($tag_id, $item_id) = explode(",", $tuple);
    $tag = ORM::factory("Tag", $tag_id);
    $item = ORM::factory("Item", $item_id);
    if (!$tag->loaded() || !$item->loaded() ||
        !$tag->has("Item", $item) || !Access::can("view", $item)) {
      throw HTTP_Exception::factory(404);
    }

    return array($tag, $item);
  }

  static function url($tag, $item) {
    return URL::abs_site("rest/tag_item/{$tag->id},{$item->id}");
  }
}
