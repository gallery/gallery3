<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
   *   urls=url1,url2,url3
   *     return items that match the specified urls.  Typically used to return the member detail
   *
   *   ancestors_for=url
   *     return the ancestors of the specified item
   *
   *   type=<comma separate list of photo, movie or album>
   *     limit the type to types in this list.  eg, "type=photo,movie"
   */
  static function get($request) {
    $items = array();
    if (isset($request->params->urls)) {
      foreach (json_decode($request->params->urls) as $url) {
        if (isset($request->params->type)) {
          $types = explode(",", $request->params->type);
        }
        $item = rest::resolve($url);
        if (access::can("view", $item)) {
          if (isset($types)) {
            if (in_array($item->type, $types)) {
              $items[] = items_rest::_format_restful_item($item);
            }
          } else {
            $items[] = items_rest::_format_restful_item($item);
          }
        }
      }
    } else if (isset($request->params->ancestors_for)) {
      $item = rest::resolve($request->params->ancestors_for);
      if (!access::can("view", $item)) {
        throw new Kohana_404_Exception();
      }
      $items[] = items_rest::_format_restful_item($item);
      while (($item = $item->parent()) != null) {
        array_unshift($items, items_rest::_format_restful_item($item));
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

  private static function _format_restful_item($item) {
    $item_rest = array("url" => rest::url("item", $item),
                       "entity" => $item->as_restful_array(),
                       "relationships" => rest::relationships("item", $item));
    if ($item->type == "album") {
      $members = array();
      foreach ($item->viewable()->children() as $child) {
        $members[] = rest::url("item", $child);
      }
      $item_rest["members"] = $members;
    }

    return $item_rest;
  }
}
