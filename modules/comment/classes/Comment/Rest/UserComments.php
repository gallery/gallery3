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
class Comment_Rest_UserComments extends Rest {
  /**
   * This resource represents a collection of comments authored by a specific user.
   *
   * GET displays the collection of comments (id required)
   *   (no parameters)
   *
   * PUT
   *   members
   *     Replace the collection of comments by the user with this list (remove only, no add)
   *
   * DELETE removes all of the user's comments (no parameters accepted).
   *
   * RELATIONSHIPS: "user_comments" is the "comments" relationship of an "users" resource.
   */

  public static $relationships = array("Users" => "Comments");

  /**
   * GET the comment members of the user_comments resource.
   * @see  Rest_Comments::get_members().
   */
  public function get_members() {
    if (empty($this->id)) {
      return null;
    }

    $user = Identity::lookup_user($this->id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Note: we can't simply do "$user->comments" since we have no guarantee
    // that the user is an ORM model with an established relationship.
    $members = ORM::factory("Comment")->viewable()
      ->where("author_id", "=", $user->id);

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
   * PUT the comment members of the user_comments resource.  This replaces the comments list
   * with this one, and removes (but doesn't add) comments as needed.  This is only for admins.
   * @see  Rest_ItemComments::put_members()
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

    // Convert our members list into an array of comment ids.
    $member_ids = array();
    foreach ($this->params["members"] as $key => $member_rest) {
      $member = ORM::factory("Comment", $member_rest->id);
      if (($member_rest->type != "Comments") || ($member->author_id != $user->id)) {
        throw Rest_Exception::factory(400, array("members" => "invalid"));
      }
      $member_ids[$key] = $member->id;
    }

    // Delete any comments that are not in the list.
    foreach (ORM::factory("Comment")->where("author_id", "=", $user->id)->find_all() as $comment) {
      if (!in_array($comment->id, $member_ids)) {
        $comment->delete();
      }
    }
  }

  /**
   * DELETE removes all of the user's comments, and is only for admins.
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

    // Delete all of the user's comments.
    foreach (ORM::factory("Comment")->where("author_id", "=", $user->id)->find_all() as $comment) {
      $comment->delete();
    }
  }
}
