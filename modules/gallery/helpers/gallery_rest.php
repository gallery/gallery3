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
// @todo VALIDATION

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

    if (!empty($p->scope) && !in_array($p->scope, array("direct", "all"))) {
      throw new Exception("Bad Request", 400);
    }
    if (!empty($p->scope)) {
      if ($p->scope == "direct") {
        $orm->where("parent_id", "=", $item->id);
      } else {
        $orm->where("left_ptr", ">=", $item->left_ptr);
        $orm->where("right_ptr", "<=", $item->left_ptr);
        $orm->where("id", "<>", $item->id);
      }
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

    return rest::reply(array("resource" => $item->as_array(), "members" => $members));
  }

  static function put($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $params = $request->params;
    foreach (array("captured", "description", "slug", "sort_column", "sort_order",
                   "title", "view_count", "weight") as $key) {
      if (isset($params->$key)) {
        $item->$key = $params->$key;
      }
    }
    $item->save();

    return rest::reply(array("url" => url::abs_site("/rest/gallery/" . $item->relative_url())));
  }

  static function post($request) {
    $parent = rest::resolve($request->url);
    access::required("edit", $parent);

    $params = $request->params;
    switch ($params->type) {
    case "album":
      $item = album::create(
        $parent,
        $params->name,
        isset($params->title) ? $params->title : $name,
        isset($params->description) ? $params->description : null);
      break;

    case "photo":
      $item = photo::create(
        $parent,
        $request->file,
        $params->name,
        isset($params->title) ? $params->title : $name,
        isset($params->description) ? $params->description : null);
      break;

    default:
      throw new Rest_Exception("Invalid type: $args->type", 400);
    }

    return rest::reply(array("url" => url::abs_site("/rest/gallery/" . $item->relative_url())));
  }

  static function delete($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    $item->delete();
    return rest::reply();
  }

  static function resolve($path) {
    return url::get_item_from_uri($path);
  }
}
