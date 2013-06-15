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
class Gallery_Controller_Rest_UserComments extends Controller_Rest {
  /**
   * This resource represents a collection of comment resources authored by a specific user.
   *
   * GET displays the collection of comments (no parameters accepted).
   *   @see  Controller_Rest_UserComments::get_members()
   *
   * PUT can accept the following post parameters:
   *   members
   *     Replace the collection of comments by the user with this list (remove only, no add)
   *   @see  Controller_Rest_UserComments::put_members()
   *
   * DELETE removes all of the user's comments (no parameters accepted).
   *   @see  Controller_Rest_UserComments::delete()
   *
   * RELATIONSHIPS: "user_comments" is the "comments" relationship of an "user" resource.
   */

  /**
   * GET the comment members of the user_comments resource.
   * @see  Controller_Rest_Comments::get_members().
   */
  static function get_members($id, $params) {
    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Note: we can't simply do "$user->comments" since we have no guarantee
    // that the user is an ORM model with an established relationship.
    $members = ORM::factory("Comment")
      ->where("author_id", "=", $user->id)
      ->order_by("created", "DESC")
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]));

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("comment", $member->id);
    }

    return $data;
  }

  /**
   * PUT the comment members of the user_comments resource.  This replaces the comments list
   * with this one, and removes (but doesn't add) comments as needed.  This is only for admins.
   * @see  Controller_Rest_ItemComments::put_members()
   */
  static function put_members($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of comment ids.
    $member_ids = Rest::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        $comment = ORM::factory("Comment", $id);
        return (($type == "comment") && ($comment->author_id == $data)) ? $id : false;
      }, $user->id);

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
  static function delete($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($id);
    if (!Identity::can_view_profile($user)) {
      throw Rest_Exception::factory(404);
    }

    // Delete all of the user's comments.
    foreach (ORM::factory("Comment")->where("author_id", "=", $user->id)->find_all() as $comment) {
      $comment->delete();
    }
  }

  /**
   * Return the relationship established by user_comments.  This adds "comments"
   * as a relationship of an "user" resource.
   */
  static function relationships($type, $id, $params) {
    return ($type == "user") ? array("comments" => array("user_comments", $id, $params)) : null;
  }
}
