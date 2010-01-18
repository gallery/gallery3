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
    $tag = rest::resolve($request->url);
    $items = array();
    foreach ($tag->items() as $item) {
      $items[] = url::abs_site("rest/gallery/" . $item->relative_url());
    }

    return rest::reply(array("resource" => $tag->as_array(), "members" => $items));
  }

  static function post($request) {
    if (empty($request->params->url)) {
      throw new Rest_Exception("Bad request", 400);
    }

    $tag = rest::resolve($request->url);
    $item = rest::resolve($request->params->url);
    access::required("edit", $item);

    tag::add($item, $tag->name);
    return rest::reply(array("url" => url::abs_site("rest/tag/" . rawurlencode($tag->name))));
  }

  static function put($request) {
    $tag = rest::resolve($request->url);

    if (isset($request->params->remove)) {
      if (!is_array($request->params->remove)) {
        throw new Exception("Bad request", 400);
      }

      foreach ($request->params->remove as $item_url) {
        $item = rest::resolve($item_url);
        access::required("edit", $item);
        $tag->remove($item);
      }
    }

    if (isset($request->params->name)) {
      $tag->name = $request->params->name;
    }

    $tag->save();
    return rest::reply(array("url" => url::abs_site("rest/tag/" . rawurlencode($tag->name))));
  }

  static function delete($request) {
    $tag = rest::resolve($request->url);

    if (empty($request->params->url)) {
      // Delete the tag
      $tag->delete();
      return rest::reply();
    } else {
      // Remove an item from the tag
      $item = rest::resolve($request->params->url);
      $tag->remove($item);
      $tag->save();

      tag::compact();
      return rest::reply(array("url" => url::abs_site("rest/tag/" . rawurlencode($tag->name))));
    }
  }

  static function resolve($tag_name) {
    $tag = ORM::factory("tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    return $tag;
  }
}
