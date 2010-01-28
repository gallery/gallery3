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
class item_rest_Core {
  /**
   * For items that are collections, you can specify the following additional query parameters to
   * query the collection.  You can specify them in any combination.
   *
   *   scope=direct
   *     only return items that are immediately under this one
   *   scope=all
   *     return items anywhere under this one
   *
   *   name=<substring>
   *     only return items where the name contains this substring
   *
   *   random=true
   *     return a single random item
   *
   *   type=<comma separate list of photo, movie or album>
   *     limit the type to types in this list.  eg, "type=photo,movie"
   */
  static function get($request) {
    $item = rest::resolve($request->url);
    access::required("view", $item);

    $p = $request->params;
    if (isset($p->random)) {
      $orm = item::random_query()->offset(0)->limit(1);
    } else {
      $orm = ORM::factory("item")->viewable();
    }

    if (empty($p->scope)) {
      $p->scope = "direct";
    }

    if (!in_array($p->scope, array("direct", "all"))) {
      throw new Rest_Exception("Bad Request", 400);
    }

    if ($p->scope == "direct") {
      $orm->where("parent_id", "=", $item->id);
    } else {
      $orm->where("left_ptr", ">", $item->left_ptr);
      $orm->where("right_ptr", "<", $item->right_ptr);
    }

    if (isset($p->name)) {
      $orm->where("name", "LIKE", "%{$p->name}%");
    }

    if (isset($p->type)) {
      $orm->where("type", "IN", explode(",", $p->type));
    }

    $members = array();
    foreach ($orm->find_all() as $child) {
      $members[] = rest::url("item", $child);
    }

    return array(
      "url" => $request->url,
      "entity" => $item->as_array(),
      "members" => $members,
      "relationships" => rest::relationships("item", $item));
  }

  static function put($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $params = $request->params;

    // Only change fields from a whitelist.
    foreach (array("album_cover_item_id", "captured", "description",
                   "height", "mime_type", "name", "parent_id", "rand_key", "resize_dirty",
                   "resize_height", "resize_width", "slug", "sort_column", "sort_order",
                   "thumb_dirty", "thumb_height", "thumb_width", "title", "view_count",
                   "weight", "width") as $key) {
      if (property_exists($request->params, $key)) {
        $item->$key = $request->params->$key;
      }
    }
    $item->save();
  }

  static function post($request) {
    $parent = rest::resolve($request->url);
    access::required("edit", $parent);

    $params = $request->params;
    $item = ORM::factory("item");
    switch ($params->type) {
    case "album":
      $item->type = "album";
      $item->parent_id = $parent->id;
      $item->name = $params->name;
      $item->title = isset($params->title) ? $params->title : $name;
      $item->description = isset($params->description) ? $params->description : null;
      $item->slug = isset($params->slug) ? $params->slug : null;
      $item->save();
      break;

    case "photo":
    case "movie":
      $item->type = $params->type;
      $item->parent_id = $parent->id;
      $item->set_data_file($request->file);
      $item->name = $params->name;
      $item->title = isset($params->title) ? $params->title : $params->name;
      $item->description = isset($params->description) ? $params->description : null;
      $item->slug = isset($params->slug) ? $params->slug : null;
      $item->save();
      break;

    default:
      throw new Rest_Exception("Invalid type: $params->type", 400);
    }

    return array("url" => rest::url("item", $item));
  }

  static function delete($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $item->delete();
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item) {
    return url::abs_site("rest/item/{$item->id}");
  }
}
