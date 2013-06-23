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
class Gallery_Rest_Tree extends Rest {
  /**
   * This read-only resource can be considered both an object and a collection of other trees.
   * The tree is rooted in a single item and can have modifiers which adjust what data is shown
   * for items inside the given tree, up to the depth that you want.  The entity for this resource
   * is a series of items, and its members are trees rooted at the maximum depth (if specified).
   *
   * GET displays a tree (id required)
   *   depth=<number>
   *     Only traverse this far down into the tree.  If there are more albums
   *     below this depth, provide RESTful urls to other tree resources in
   *     the members section.  Default is infinite.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit the type to types in this list (e.g. "type=photo,movie").
   *   fields=<comma separated list of field names>
   *     In the entity section only return these fields for each item.
   *     Default is all fields.
   *
   * Notes:
   *   Unlike other collections, "start" and "num" parameters are ignored, and any
   *   "expand_members" parameter is removed (so it will not be "sticky").
   */

  /**
   * GET the tree's entity, which is an array of item urls and entities.
   */
  public function get_entity() {
    $item = ORM::factory("Item", $this->id);
    Access::required("view", $item);
    if (!$item->is_album()) {
      throw Rest_Exception::factory(400, array("tree" => "not_an_album"));
    }

    $members = $item->descendants;

    if (isset($this->params["depth"])) {
      // Only include items up to the maximum depth.
      $members->where("level", "<=", $item->level + $this->params["depth"]);
    }

    if (isset($this->params["type"])) {
      $members->where("type", "IN", $this->params["type"]);
    }

    $members = $members->viewable()->find_all();

    // Build the entity.
    $data = array();
    foreach (array_merge(array($item), iterator_to_array($members)) as $member) {
      $member_rest = Rest::factory("Items", $member->id);

      $url    = $member_rest->url();
      $entity = $member_rest->get_entity();

      if (isset($this->params["fields"])) {
        // Filter by the specified fields.
        $fields = explode(",", trim($this->params["fields"], ","));
        foreach ($entity as $field => $value) {
          if (!in_array($field, $fields)) {
            unset($entity[$field]);
          }
        }
      }

      $data[] = array("url" => $url, "entity" => $entity);
    }

    return $data;
  }

  /**
   * GET the tree's members, which are trees that extend beyond the maximum depth.
   */
  public function get_members() {
    $item = ORM::factory("Item", $this->id);
    Access::required("view", $item);
    if (!$item->is_album()) {
      throw Rest_Exception::factory(400, array("tree" => "not_an_album"));
    }

    if (!isset($this->params["depth"])) {
      // Depth not defined - members list is empty.
      return array();
    }

    // Only include items *at* the maximum depth that are albums.
    $members = $item->descendants
      ->where("level", "=", $item->level + $this->params["depth"])
      ->where("type", "=", "album")
      ->viewable()
      ->find_all();

    // Set the member params - "depth" and "fields" are sticky for trees.
    $m_params = array();
    foreach (array("depth", "fields") as $key) {
      if (isset($this->params[$key])) {
        $m_params[$key] = $this->params[$key];
      }
    }

    // Build the members array.
    $data = array();
    foreach ($members as $member) {
      $data[] = Rest::factory("Tree", $member->id, $m_params);
    }

    return $data;
  }

  /**
   * Override Rest::__construct() to remove the expand_members parameter, if set.
   */
  public function __construct($id, $params) {
    unset($params["expand_members"]);
    parent::__construct($id, $params);
  }
}
