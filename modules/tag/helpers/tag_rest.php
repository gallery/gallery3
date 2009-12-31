<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class tag_rest_Core {
  // If no arguments just return all the tags.  If 2 or more then it is a path then
  // return the tags for that item.  But if its only 1, then is it a path or a tag?
  // Assume a tag first, if nothing is found then try finding the item.
  static function get($request) {
    $resources = array();
    switch (count($request->arguments)) {
    case 0:
      $tags = ORM::factory("tag")
        ->select("name", "count")
        ->order_by("count", "DESC");
      if (!empty($request->limit)) {
        $tags->limit($request->limit);
      }
      if (!empty($request->offset)) {
        $tags->offset($request->offset);
      }
      $resources = array("tags" => array());
      foreach ($tags->find_all() as $row) {
        $resources["tags"][] = array("name" => $row->name, "count" => $row->count);
      }
      break;
    case 1:
      $resources = tag_rest::_get_items($request);
      if (!empty($resources)) {
        $resources = array("resources" => $resources);
        break;
      }
    default:
      $item = ORM::factory("item")
        ->where("relative_url_cache", "=", implode("/", $request->arguments))
        ->viewable()
        ->find();
      if ($item->loaded()) {
        $resources = array("tags" => tag::item_tags($item));
      }
    }

    return rest::success($resources);
  }

  static function post($request) {
    if (empty($request->arguments) || count($request->arguments) != 1 || empty($request->path)) {
      return rest::invalid_request();
    }
    $path = $request->path;
    $tags = explode(",", $request->arguments[0]);

    $item = ORM::factory("item")
      ->where("relative_url_cache", "=", $path)
      ->viewable()
      ->find();
    if (!$item->loaded()) {
      throw new Kohana_404_Exception();
    }

    if (!access::can("edit", $item)) {
      throw new Kohana_404_Exception();
    }

    foreach ($tags as $tag) {
      tag::add($item, $tag);
    }
    return rest::success();
  }

  static function put($request) {
    if (empty($request->arguments[0]) || empty($request->new_name)) {
      return rest::invalid_request();
    }

    $name = $request->arguments[0];

    $tag = ORM::factory("tag")
      ->where("name", "=", $name)
      ->find();
    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    $tag->name = $request->new_name;
    $tag->save();

    return rest::success();
  }

  static function delete($request) {
    if (empty($request->arguments[0])) {
      return rest::invalid_request();
    }
    $tags = explode(",", $request->arguments[0]);
    if (!empty($request->path)) {
      $tag_list = ORM::factory("tag")
        ->join("items_tags", "tags.id", "items_tags.tag_id")
        ->join("items", "items.id", "items_tags.item_id")
        ->where("tags.name", "IN",  $tags)
        ->where("relative_url_cache", "=", $request->path)
        ->viewable()
        ->find_all();
    } else {
      $tag_list = ORM::factory("tag")
        ->where("name", "IN", $tags)
        ->find_all();
    }

    foreach ($tag_list as $row) {
      $row->delete();
    };

    tag::compact();
    return rest::success();
  }

  private static function _get_items($request) {
    $tags = explode(",", $request->arguments[0]);
    $items = ORM::factory("item")
      ->select_distinct("*")
      ->join("items_tags", "items.id", "items_tags.item_id")
      ->join("tags", "tags.id", "items_tags.tag_id")
      ->where("tags.name", "IN",  $tags);
    if (!empty($request->limit)) {
      $items->limit($request->limit);
    }
    if (!empty($request->offset)) {
      $items->offset($request->offset);
    }
    $resources = array();
    foreach ($items->find_all() as $item) {
      $resources[] = array("type" => $item->type,
                           "has_children" => $item->children_count() > 0,
                           "path" => $item->relative_url(),
                           "thumb_url" => $item->thumb_url(true),
                           "thumb_dimensions" => array("width" => $item->thumb_width,
                                                       "height" => $item->thumb_height),
                           "has_thumb" => $item->has_thumb(),
                           "title" => $item->title);
    }

    return $resources;
  }
}
