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
class items_rest_Core {
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
   *     Also limits the types returned in the member collections (same behaviour as item_rest).
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
        $item = rest::resolve($url);
        if (!access::can("view", $item)) {
          continue;
        }

        if (empty($types) || in_array($item->type, $types)) {
          $items[] = items_rest::_format_restful_item($item, $types);
        }
      }
    } else if (isset($request->params->ancestors_for)) {
      $item = rest::resolve($request->params->ancestors_for);
      if (!access::can("view", $item)) {
        throw new Kohana_404_Exception();
      }
      $items[] = items_rest::_format_restful_item($item, $types);
      while (($item = $item->parent()) != null) {
        array_unshift($items, items_rest::_format_restful_item($item, $types));
      };
    }

    return $items;
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  private static function _format_restful_item($item, $types) {
    $item_rest = array("url" => rest::url("item", $item),
                       "entity" => $item->as_restful_array(),
                       "relationships" => rest::relationships("item", $item));
    if ($item->type == "album") {
      $members = array();
      foreach ($item->viewable()->children() as $child) {
        if (empty($types) || in_array($child->type, $types)) {
          $members[] = rest::url("item", $child);
        }
      }
      $item_rest["members"] = $members;
    }

    return $item_rest;
  }
}
