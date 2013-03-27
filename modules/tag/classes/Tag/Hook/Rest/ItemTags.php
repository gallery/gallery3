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
class Tag_Hook_Rest_ItemTags {
  static function get($request) {
    $item = Rest::resolve($request->url);
    $tags = array();
    foreach (Tag::item_tags($item) as $tag) {
      $tags[] = Rest::url("tag_item", $tag, $item);
    }

    return array(
      "url" => $request->url,
      "members" => $tags);
  }

  static function post($request) {
    $tag = Rest::resolve($request->params->entity->tag);
    $item = Rest::resolve($request->params->entity->item);
    Access::required("view", $item);

    Tag::add($item, $tag->name);
    return array(
      "url" => Rest::url("tag_item", $tag, $item),
      "members" => array(
        Rest::url("tag", $tag),
        Rest::url("item", $item)));
  }

  static function delete($request) {
    $item = Rest::resolve($request->url);
    Access::required("edit", $item);

    // Deleting this collection means removing all tags associated with the item.
    Tag::clear_all($item);
  }

  static function resolve($id) {
    $item = ORM::factory("Item", $id);
    if (!Access::can("view", $item)) {
      throw new HTTP_Exception_404();
    }

    return $item;
  }

  static function url($item) {
    return URL::abs_site("rest/item_tags/{$item->id}");
  }
}
