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
class Tag_Hook_Rest_TagItems {
  static function get($request) {
    $tag = Rest::resolve($request->url);
    $items = array();
    foreach ($tag->items->viewable()->order_by("item.id")->find_all() as $item) {
      $items[] = Rest::url("tag_item", $tag, $item);
    }

    return array(
      "url" => $request->url,
      "members" => $items);
  }

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

  static function delete($request) {
    $tag = Rest::resolve($request->url);
    $tag->remove_items();
  }

  static function resolve($id) {
    return ORM::factory("Tag", $id);
  }

  static function url($tag) {
    return URL::abs_site("rest/tag_items/{$tag->id}");
  }
}
