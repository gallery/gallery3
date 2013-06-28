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
class Gallery_Rest_UserItems extends Rest {
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

  public static $relationships = array("Users" => "Items");

  /**
   * GET the item members of the user_items resource.
   * @see  Rest_Items::get_members().
   */
  public function get_members() {
    if (empty($this->id)) {
      return null;
    }

    $user = Identity::lookup_user($this->id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Note: we can't simply do "$user->items" since we have no guarantee
    // that the user is an ORM model with an established relationship.
    $members = ORM::factory("Item")
      ->viewable()
      ->where("owner_id", "=", $user->id);

    if (isset($this->params["type"])) {
      $members->where("type", "IN", $this->params["type"]);
    }

    if (isset($this->params["name"])) {
      $members->where("name", "LIKE", "%" . Database::escape_for_like($this->params["name"]) . "%");
    }

    $this->members_info["count"] = $members->reset(false)->count_all();
    $members = $members
      ->limit($this->members_info["num"])
      ->offset($this->members_info["start"])
      ->find_all();

    $data = array();
    foreach ($members as $member) {
      $data[] = Rest::factory("Items", $member->id);
    }

    return $data;
  }

  /**
   * PUT the item members of the user_items resource.  This replaces the items list
   * with this one, and removes (but doesn't add) items as needed.  This is only for admins.
   */
  public function put_members() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($this->id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Convert our members list into an array of item ids.
    $member_ids = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Item", $member_rest->id);
      if (($member_rest->type != "Items") || ($member->owner_id != $user->id)) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $member_ids[$key] = $member->id;
    }

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
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($this->id);
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
}
