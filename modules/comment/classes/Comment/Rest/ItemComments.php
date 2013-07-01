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
class Comment_Rest_ItemComments extends Rest {
  /**
   * This resource represents a collection of comments on a specified item.
   *
   * GET displays the collection of comments (id required)
   *   (no parameters)
   *
   * PUT
   *   members
   *     Replace the collection of comments on the item with this list (remove only, no add)
   *
   * DELETE removes all comments from the item (no parameters accepted).
   *
   * RELATIONSHIPS: "item_comments" is the "comments" relationship of an "items" resource.
   */

  public static $relationships = array("Items" => "Comments");

  /**
   * GET the comment members of the item_comments resource.
   * @see  Rest_Comments::get_members().
   */
  public function get_members() {
    if (empty($this->id)) {
      return null;
    }

    $item = ORM::factory("Item", $this->id);
    Access::required("view", $item);

    $members = $item->comments;

    $this->members_info["count"] = $members->reset(false)->count_all();
    $members = $members
      ->limit($this->members_info["num"])
      ->offset($this->members_info["start"])
      ->find_all();

    $data = array();
    foreach ($members as $member) {
      $data[] = Rest::factory("Comments", $member->id);
    }

    return $data;
  }

  /**
   * PUT the comment members of the item_comments resource.  This replaces the comments list
   * with this one, and removes (but doesn't add) comments as needed.  This is only for admins.
   * @see  Rest_UserComments::put_members()
   */
  public function put_members() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $item = ORM::factory("Item", $this->id);
    if (!$item->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Convert our members list into an array of comment ids.
    $member_ids = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Comment", $member_rest->id);
      if (($member_rest->type != "Comments") || ($member->item_id != $item->id)) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $member_ids[$key] = $member->id;
    }

    // Delete any comments that are not in the list.
    foreach ($item->comments->find_all() as $comment) {
      if (!in_array($comment->id, $member_ids)) {
        $comment->delete();
      }
    }
  }

  /**
   * DELETE removes all comments from the item, and is only for admins.
   */
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $item = ORM::factory("Item", $this->id);
    if (!$item->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Delete all comments from the item.
    foreach ($item->comments->find_all() as $comment) {
      $comment->delete();
    }
  }
}
