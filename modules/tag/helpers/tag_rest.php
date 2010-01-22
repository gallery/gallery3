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
    $tag_items = array();
    foreach ($tag->items() as $item) {
      if (access::can("view", $item)) {
        $tag_items[] = rest::url("tag_item", $tag, $item);
      }
    }

    return array(
      "url" => $request->url,
      "resource" => $tag->as_array(),
      "relationships" => array(
        "items" => $tag_items));
  }

  static function post($request) {
    if (empty($request->params->url)) {
      throw new Rest_Exception("Bad request", 400);
    }

    $tag = rest::resolve($request->url);
    $item = rest::resolve($request->params->url);
    access::required("edit", $item);

    tag::add($item, $tag->name);
  }

  static function put($request) {
    $tag = rest::resolve($request->url);
    if (isset($request->params->name)) {
      $tag->name = $request->params->name;
      $tag->save();
    }
  }

  static function delete($request) {
    $tag = rest::resolve($request->url);
    $tag->delete();
  }

  static function relationships($resource_type, $resource) {
    switch ($resource_type) {
    case "item":
      $tags = array();
      foreach (tag::item_tags($resource) as $tag) {
        $tags[] = rest::url("tag_item", $tag, $resource);
      }
      return array("tags" => $tags);
    }
  }

  static function resolve($id) {
    $tag = ORM::factory("tag")->where("id", "=", $id)->find();
    if (!$tag->loaded()) {
      throw new Kohana_404_Exception();
    }

    return $tag;
  }

  static function url($tag) {
    return url::abs_site("rest/tag/{$tag->id}");
  }
}
