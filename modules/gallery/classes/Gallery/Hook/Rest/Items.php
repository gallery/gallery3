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
class Gallery_Hook_Rest_Items {
  /**
   * To retrieve a collection of items, you can specify the following query parameters to specify
   * the type of the collection.  If both are specified, then the url parameter is used and the
   * ancestors_for is ignored.  Specifying the "type" parameter with the urls parameter, will
   * filter the results based on the specified type.  Using the type parameter with the
   * ancestors_for parameter makes no sense and will be ignored.
   *
   *   urls=["url1","url2","url3"]
   *     Return items that match the specified urls.  Typically used to return the member detail
   *
   *   ancestors_for=url
   *     Return the ancestors of the specified item
   *
   *   type=<comma separate list of photo, movie or album>
   *     Limit the type to types in this list, eg: "type=photo,movie".
   *     Also limits the types returned in the member collections (same behaviour as Hook_Rest_Item).
   *     Ignored if ancestors_for is set.
   */
  static function get($request) {
    $items = array();
    $types = array();

    if (isset($request->params->urls)) {
      if (isset($request->params->type)) {
        $types = explode(",", $request->params->type);
      }

      foreach (json_decode($request->params->urls) as $url) {
        $item = Rest::resolve($url);
        if (!Access::can("view", $item)) {
          continue;
        }

        if (empty($types) || in_array($item->type, $types)) {
          $items[] = Hook_Rest_Items::_format_restful_item($item, $types);
        }
      }
    } else if (isset($request->params->ancestors_for)) {
      $item = Rest::resolve($request->params->ancestors_for);
      if (!Access::can("view", $item)) {
        throw HTTP_Exception::factory(404);
      }
      $items[] = Hook_Rest_Items::_format_restful_item($item, $types);
      while (($item = $item->parent) != null) {
        array_unshift($items, Hook_Rest_Items::_format_restful_item($item, $types));
      };
    }

    return $items;
  }

  static function resolve($id) {
    $item = ORM::factory("Item", $id);
    if (!Access::can("view", $item)) {
      throw HTTP_Exception::factory(404);
    }
    return $item;
  }

  protected static function _format_restful_item($item, $types) {
    $item_rest = array("url" => Rest::url("item", $item),
                       "entity" => $item->as_restful_array(),
                       "relationships" => Rest::relationships("item", $item));
    if ($item->type == "album") {
      $members = array();
      foreach ($item->children->viewable()->find_all() as $child) {
        if (empty($types) || in_array($child->type, $types)) {
          $members[] = Rest::url("item", $child);
        }
      }
      $item_rest["members"] = $members;
    }

    return $item_rest;
  }
}
