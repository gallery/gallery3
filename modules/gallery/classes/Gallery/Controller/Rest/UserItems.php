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
   * This resource represents a collection of item resources owned by a specific user.
   *
   * GET can accept the following query parameters:
   *   name=<substring>
   *     Only return items where the name contains this substring.
   *   type=<comma-separated list of photo, movie or album>
   *     Limit the type to types in this list (e.g. "type=photo,movie").
   *     Also limits the types returned in the member collections (i.e. sub-albums).
   *   @see  Controller_Rest_UserItems::get_members()
   *
   * PUT can accept the following post parameters:
   *   members
   *     Replace the collection of items by the user with this list (remove only, no add)
   *   @see  Controller_Rest_UserItems::put_members()
   *
   * DELETE removes all of the user's items (no parameters accepted).
   *   @see  Controller_Rest_UserItems::delete()
   *
   * RELATIONSHIPS: "user_items" is the "items" relationship of a "user" resource.
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE user_items.
   */

  /**
   * GET the item members of the user_items resource.
   * @see  Controller_Rest_Items::get_members().
   */
  static function get_members($id, $params) {
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
      $data[] = array("item", $member->id);
    }

    return $data;
  }

  /**
   * PUT the item members of the user_items resource.  This replaces the items list
   * with this one, and removes (but doesn't add) items as needed.  This is only for admins.
   */
  static function put_members($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of item ids.
    $member_ids = Rest::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        $item = ORM::factory("Item", $id);
        return (($type == "item") && ($item->owner_id == $data)) ? $id : false;
      }, $user->id);

    // Delete any items that are not in the list.
    foreach (ORM::factory("Item")
             ->where("owner_id", "=", $user->id)
             ->where("id", "<>", Item::root()->id) // If root included, Model_Item will throw a 500.
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
             ->find_all() as $item) {
      $item->delete();
    }
  }

  /**
   * Return the relationship established by user_items.  This adds "items"
   * as a relationship of a "user" resource.
   */
  static function relationships($type, $id, $params) {
    return ($type == "user") ? array("items" => array("user_items", $id)) : null;
  }
}
