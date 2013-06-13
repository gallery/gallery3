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
class Comment_Controller_Rest_Comment extends Controller_Rest {
  /**
   * This resource represents a Model_Comment object.
   *
   * GET displays the comment (no parameters accepted).
   *   @see  Controller_Rest_Comment::get_entity()
   *
   * PUT can accept the following post parameters:
   *   entity
   *     Edit the comment
   *   @see  Controller_Rest_Comment::put_entity()
   *
   * DELETE removes the comment entirely (no parameters accepted).
   *   @see  Controller_Rest_Comment::delete()
   *
   * Note: similar to the standard UI, only admins can PUT or DELETE a comment.
   */

  /**
   * GET the comment's entity.
   */
  static function get_entity($id, $params) {
    $comment = ORM::factory("Comment", $id);
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
      $data["item"] = Rest::url("item", $comment->item->id);
    }
    unset($data["item_id"]);

    return $data;
  }

  /**
   * PUT the comment's entity.  This edits the comment model, and is only for admins.
   */
  static function put_entity($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $comment = ORM::factory("Comment", $id);
    if (!$comment->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Add fields from a whitelist.
    foreach (array("text", "state", "guest_name", "guest_email", "guest_url") as $field) {
      if (property_exists($params["entity"], $field)) {
        $comment->$field = $params["entity"]->$field;
      }
    }

    $comment->save();
  }

  /**
   * DELETE the comment.  This is only for admins.
   */
  static function delete($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $comment = ORM::factory("Comment", $id);
    if (!$comment->loaded()) {
      throw Rest_Exception::factory(404);
    }

    $comment->delete();
  }
}
