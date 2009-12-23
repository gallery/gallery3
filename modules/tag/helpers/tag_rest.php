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
  static function get($request) {
    if (empty($request->arguments)) {
      $tags = ORM::factory("tag")
        ->select("name", "count")
        ->orderby("count", "DESC");
      if (!empty($request->limit)) {
        $tags->limit($request->limit);
      }
      if (!empty($request->offset)) {
        $tags->offset($request->offset);
      }
      $response = array("tags" => array());
      foreach ($tags->find_all() as $row) {
        $response["tags"][] = array("name" => $row->name, "count" => $row->count);
      }
    } else {
      $item = ORM::factory("item")
        ->where("relative_url_cache", implode("/", $request->arguments))
        ->viewable()
        ->find();
      if ($item->loaded) {
        $response = array("tags" => tag::item_tags($item));
      } else {
        $response = array("resources" => tag_rest::_get_items($request));
      }
    }

    return rest::success($response);
  }

  private static function _get_items($request) {
    $tags = $request->arguments;
    $items = ORM::factory("item")
      ->join("items_tags", "items.id", "items_tags.item_id", "left")
      ->join("tags", "tags.id", "items_tags.tag_id", "left")
      ->where("tags.name", array_shift($tags));
    if (!empty($request->limit)) {
      $items->limit($request->limit);
    }
    if (!empty($request->offset)) {
      $tags->offset($request->offset);
    }
    foreach ($tags as $tag) {
      $items->orWhere("tags.name", $tag);
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
