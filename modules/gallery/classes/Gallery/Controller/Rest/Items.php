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
class Gallery_Controller_Rest_Items extends Controller_Rest {
  /**
   * This resource represents a collection of item resources.
   *
   * GET can accept the following query parameters:
   *   ancestors_for=url
   *     Return the ancestors of the specified item.  If specified,
   *     all other query parameters described below will be ignored.
   *     This is typically used to create breadcrumbs for an item.
   *   urls=["url1","url2","url3"]
   *     Return items that match the specified urls.  If specified,
   *     the "start" and "num" parameters will be ignored.
   *     This is typically used to return the member detail.
   *   name=<substring>
   *     Only return items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit the type to types in this list (e.g. "type=photo,movie").
   *     Also limits the types returned in the member collections (i.e. sub-albums).
   *   @see  Controller_Rest_Items::get_members()
   *
   * POST *requires* the following post parameters:
   *   entity
   *     Add an item, using the "parent" field as its parent.
   *   file
   *     Add an item's data file (only for movies and photos)
   *   @see  Controller_Rest_Items::post_entity()
   *
   * Notes:
   *   Unlike other collections, "expand_members" is true by default (backward-compatible with v3.0).
   *   @see  Controller_Rest_Items::action_get()
   */

  /**
   * GET the members of the items resource.
   * @see  Controller_Rest_Item::get_members().
   */
  public static function get_members($id, $params) {
    $types = Arr::get($params, "types");
    $name = Arr::get($params, "name");

    $data = array();
    if ($ancestors_for = Arr::get($params, "ancestors_for")) {
      // Members are the ancestors of the url given.
      list ($i_type, $i_id, $i_params) = Rest::resolve($ancestors_for);
      if ($i_type != "item") {
        throw Rest_Exception::factory(400, array("urls" => "invalid"));
      }

      $item = ORM::factory("Item", $i_id);
      Access::required("view", $item);

      $members = $item->parents->viewable()->find_all();
      foreach ($members as $member) {
        $data[] = array("item", $member->id);
      }
    } else if ($urls = Arr::get($params, "urls")) {
      // Members are taken from a list of urls, filtered by name and type.
      // @todo: json_decode is what was used in 3.0, but should we allow comma-separated lists, too?
      foreach (json_decode($urls, true) as $url) {
        list ($m_type, $m_id, $m_params) = Rest::resolve($url);
        if ($m_type != "item") {
          throw Rest_Exception::factory(400, array("urls" => "invalid"));
        }

        $member = ORM::factory("Item", $m_id);
        Access::required("view", $member);

        if ((empty($types) || in_array($member->type, $types)) &&
            (empty($name) || (strpos($member->name, $name) !== false))) {
          $data[] = array("item", $member->id);
        }
      }
    } else {
      // Members are the standard item collection member list - same as rest/items/1.
      $data = Rest::get_members("item", 1, $params);
    }

    return $data;
  }

  /**
   * POST an item's entity (and possibly file).  This generates a new item model.
   * @see  Controller_Rest_Item::post_entity().
   */
  public static function post_entity($id, $params) {
    if (!property_exists($params["entity"], "parent")) {
      throw Rest_Exception::factory(400, array("parent" => "required"));
    }

    list ($p_type, $p_id, $p_params) = Rest::resolve($params["entity"]->parent);
    if ($p_type != "item") {
      throw Rest_Exception::factory(400, array("parent" => "invalid"));
    }

    return Rest::post_entity("item", $p_id, $params);
  }

  /**
   * Override Controller_Rest::action_get() to expand members by default.
   */
  public function action_get() {
    static::$default_params["expand_members"] = true;
    return parent::action_get();
  }
}
