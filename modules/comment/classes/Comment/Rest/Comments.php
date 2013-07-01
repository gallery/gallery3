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
class Comment_Rest_Comments extends Rest {
  /**
   * This resource represents a Model_Comment.
   *
   * GET displays a comment (if id given)
   *   (no parameters)
   *
   * GET displays a collection of comments, ordered by post date with newest first (no id given)
   *   (no parameters)
   *
   * PUT
   *   entity
   *     Edit the comment
   *
   * POST
   *   entity
   *     Add a comment
   *
   * DELETE removes the comment entirely (no parameters accepted).
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE a comment.
   */

  /**
   * GET the comment's entity.
   */
  public function get_entity() {
    if (empty($this->id)) {
      return null;
    }

    $comment = ORM::factory("Comment", $this->id);
    Access::required("view", $comment->item);

    $data = $comment->as_array();

    // Remove "server_" fields and "guest_" fields if the author isn't a guest.
    foreach ($data as $key => $value) {
      if ((substr($key, 0, 7) == "server_") ||
          ((substr($key, 0, 6) == "guest_") && ($comment->author_id != Identity::guest()->id))) {
        unset($data[$key]);
      }
    }

    // Convert "item_id" to "item" REST URL.
    if ($comment->item->loaded()) {
      $data["item"] = Rest::factory("Items", $comment->item->id)->url();
    }
    unset($data["item_id"]);

    // Convert "author_id" to "author" REST URL.
    $author = Identity::lookup_user($comment->author_id);
    if (Identity::can_view_profile($author)) {
      $data["author"] = Rest::factory("Users", $author->id)->url();
    }
    unset($data["author_id"]);

    return $data;
  }

  /**
   * PUT the comment's entity.  This edits the comment model, and is only for admins.
   */
  public function put_entity() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $comment = ORM::factory("Comment", $this->id);
    if (!$comment->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Add fields from a whitelist.
    foreach (array("text", "state", "guest_name", "guest_email", "guest_url") as $field) {
      if (property_exists($this->params["entity"], $field)) {
        $comment->$field = $this->params["entity"]->$field;
      }
    }

    $comment->save();
  }

  /**
   * DELETE the comment.  This is only for admins.
   */
  public function delete() {
    if (empty($this->id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $comment = ORM::factory("Comment", $this->id);
    if (!$comment->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $comment->delete();
  }

  /**
   * GET the members of the comments collection.
   */
  public function get_members() {
    if (!empty($this->id)) {
      return null;
    }

    $members = ORM::factory("Comment")->viewable();

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
   * POST a comment's entity.  This generates a new comment model.
   */
  public function post_entity() {
    if (!empty($this->id)) {
      return null;
    }

    if (!property_exists($this->params["entity"], "item")) {
      throw Rest_Exception::factory(400, array("item" => "required"));
    }

    $item_rest = RestAPI::resolve($this->params["entity"]->item);
    if (!$item_rest || ($item_rest->type != "Items")) {
      throw Rest_Exception::factory(400, array("item" => "invalid"));
    }

    $item = ORM::factory("Item", $item_rest->id);
    if (!Comment::can_comment($item)) {
      throw Rest_Exception::factory(403);
    }

    // Build the comment model.
    $comment = ORM::factory("Comment");
    $comment->author_id = Identity::active_user()->id;
    $comment->item_id = $item->id;

    // Add fields from a whitelist.
    foreach (array("text", "state", "guest_name", "guest_email", "guest_url") as $field) {
      if (property_exists($this->params["entity"], $field)) {
        $comment->$field = $this->params["entity"]->$field;
      }
    }

    $comment->save();

    // Success!
    $this->id = $comment->id;
  }
}
