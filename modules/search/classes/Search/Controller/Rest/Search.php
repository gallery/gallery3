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
class Search_Controller_Rest_Search extends Controller_Rest {
  /**
   * This read-only resource is a collection of items resulting from a Gallery search.
   *
   * GET displays a collection of items
   *   q=<string>
   *     Search string (required).  This is the same as the search box in the standard UI.
   *   album=<album rest url>
   *     Limit the search to items found in a given album, given as the album's REST URL.
   *     This is the same as searching from a non-root album in the standard UI.
   *     If not given, this will default to the root album (i.e. search all items).
   *   type=<comma-separated list of photo, movie or album>
   *     Limit the type to types in this list (e.g. "type=photo,movie").
   */

  /**
   * GET the search's members, which are items.
   */
  static function get_members($id, $params) {
    if (!($q = Arr::get($params, "q"))) {
      throw Rest_Exception::factory(400, array("q" => "required"));
    }

    // Get the search album
    $album_url = Arr::get($params, "album");
    if ($album_url) {
      list ($a_type, $a_id, $a_params) = RestAPI::resolve($album_url);
      if ($a_type != "items") {
        throw Rest_Exception::factory(400, array("album" => "invalid"));
      }

      $album = ORM::factory("Item", $a_id);
      if (!$album->is_album()) {
        throw Rest_Exception::factory(400, array("album" => "invalid"));
      }
    } else {
      $album = Item::root();
    }
    Access::required("view", $album);

    // Do the search.
    $q_with_more_terms = Search::add_query_terms($q);
    $result = Search::search_within_album($q_with_more_terms, $album,
      Arr::get($params, "num",   static::$default_params["num"]),
      Arr::get($params, "start", static::$default_params["start"]));

    // Build the members array.
    $data = array();
    $types = Arr::get($params, "type");
    foreach ($result[1] as $item) {
      if (!$types || in_array($item->type, $types)) {
        $data[] = array("items", $item->id);
      }
    }

    return $data;
  }
}
