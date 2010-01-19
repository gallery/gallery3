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

// @todo Add logging

// Validation questions
//
// We need to be able to properly validate anything we want to enter here.  But all of our
// validation currently happens at the controller / form level, and we're not using the same
// controllers or forms.
//
// Possible solutions:
// 1) Move validation into the model and use it both here and in the regular controllers.  But
// if we do that, how do we translate validation failures into a user-consumable output which
// we need so that we can return proper error responses to form submissions?
//
// 2) Create some kind of validation helper that can validate every field.  Wait, isn't this
// just like #1 except in a helper instead of in the model?

class gallery_rest_Core {

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
      throw new Exception("Bad Request", 400);
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
      $members[] = url::abs_site("rest/gallery/" . $child->relative_url());
    }

    return array("resource" => $item->as_array(), "members" => $members);
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
      if (array_key_exists($key, $request->params)) {
        $item->$key = $request->params->$key;
      }
    }
    $item->save();

    return array("url" => url::abs_site("/rest/gallery/" . $item->relative_url()));
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

    return array("url" => url::abs_site("/rest/gallery/" . $item->relative_url()));
  }

  static function delete($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $item->delete();
  }

  static function resolve($path) {
    return url::get_item_from_uri($path);
  }
}
