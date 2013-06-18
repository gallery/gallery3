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
class Gallery_Controller_Rest_UserItems extends Controller_Rest {
  /**
   * This resource represents a collection of items owned by a specific user.
   *
   * GET displays the collection of items (id required)
   *   name=<substring>
   *     Only return member items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit member items to the types in this list (e.g. "type=photo,movie").
   *
   * PUT
   *   members
   *     Replace the collection of items by the user with this list (remove only, no add)
   *
   * POST
   *   members
   *     Add the tag to the items in the list.  Unlike PUT, this only *adds* items.
   *     Since there is no entity function, this is only accessible using relationships.
   *
   * DELETE removes all of the user's items (no parameters accepted).
   *
   * RELATIONSHIPS: "user_items" is the "items" relationship of a "users" resource.
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE user_items.
   */

  /**
   * GET the item members of the user_items resource.
   * @see  Controller_Rest_Items::get_members().
   */
  static function get_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Note: we can't simply do "$user->items" since we have no guarantee
    // that the user is an ORM model with an established relationship.
    $members = ORM::factory("Item")->viewable()
      ->where("owner_id", "=", $user->id)
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]));

    if (isset($params["type"])) {
      $members->where("type", "IN", $params["type"]);
    }

    if (isset($params["name"])) {
      $members->where("name", "LIKE", "%" . Database::escape_for_like($params["name"]) . "%");
    }

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("items", $member->id);
    }

    return $data;
  }

  /**
   * PUT the item members of the user_items resource.  This replaces the items list
   * with this one, and removes (but doesn't add) items as needed.  This is only for admins.
   */
  static function put_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item ids.
    $member_ids = RestAPI::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        $item = ORM::factory("Item", $id);
        return (($type == "items") && ($item->owner_id == $data)) ? $id : false;
      }, $user->id);

    // Delete any items that are not in the list.
    foreach (ORM::factory("Item")
             ->where("owner_id", "=", $user->id)
             ->where("id", "<>", Item::root()->id) // If root included, Model_Item will throw a 500.
             ->order_by("left_ptr", "DESC")        // Delete children before parents.
             ->find_all() as $item) {
      if (!in_array($item->id, $member_ids)) {
        $item->delete();
      }
    }
  }

  /**
   * DELETE removes all of the user's items, and is only for admins.
   */
  static function delete($id, $params) {
    if (empty($id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Delete all of the user's items.
    foreach (ORM::factory("Item")
             ->where("owner_id", "=", $user->id)
             ->where("id", "<>", Item::root()->id) // If root included, Model_Item will throw a 500.
             ->order_by("left_ptr", "DESC")        // Delete children before parents.
             ->find_all() as $item) {
      $item->delete();
    }
  }

  /**
   * Return the relationship established by user_items.  This adds "items"
   * as a relationship of a "users" resource.
   */
  static function relationships($type, $id, $params) {
    return (($type == "users") && (!empty($id))) ?
      array("items" => array("user_items", $id, $params)) : null;
  }
}
