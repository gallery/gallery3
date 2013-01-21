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
class tag_items_rest_Core {
  static function get($request) {
    $tag = rest::resolve($request->url);
    $items = array();
    foreach ($tag->items() as $item) {
      if (access::can("view", $item)) {
        $items[] = rest::url("tag_item", $tag, $item);
      }
    }

    return array(
      "url" => $request->url,
      "members" => $items);
  }

  static function post($request) {
    $tag = rest::resolve($request->params->entity->tag);
    $item = rest::resolve($request->params->entity->item);
    access::required("view", $item);

    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    tag::add($item, $tag->name);
    return array(
      "url" => rest::url("tag_item", $tag, $item),
      "members" => array(
        "tag" => rest::url("tag", $tag),
        "item" => rest::url("item", $item)));
  }

  static function delete($request) {
    $tag = rest::resolve($request->url);
    $tag->remove_items();
  }

  static function resolve($id) {
    return ORM::factory("tag", $id);
  }

  static function url($tag) {
    return url::abs_site("rest/tag_items/{$tag->id}");
  }
}
