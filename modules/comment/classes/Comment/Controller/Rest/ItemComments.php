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
class Comment_Controller_Rest_ItemComments extends Controller_Rest {
  /**
   * This resource represents a collection of comment resources on a specified item.
   *
   * GET displays the collection of comments (no parameters accepted).
   *   @see  Controller_Rest_ItemComments::get_members()
   *
   * PUT can accept the following post parameters:
   *   members
   *     Replace the collection of comments on the item with this list (remove only, no add)
   *   @see  Controller_Rest_ItemComments::put_members()
   *
   * DELETE removes all comments from the item (no parameters accepted).
   *   @see  Controller_Rest_ItemComments::delete()
   *
   * RELATIONSHIPS: "item_comments" is the "comments" relationship of an "item" resource.
   */

  /**
   * GET the comment members of the item_comments resource.
   * @see  Controller_Rest_Comments::get_members().
   */
  static function get_members($id, $params) {
    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    $members = $item->comments
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]))
      ->order_by("created", "DESC");

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("comment", $member->id);
    }

    return $data;
  }

  /**
   * PUT the comment members of the item_comments resource.  This replaces the comments list
   * with this one, and removes (but doesn't add) comments as needed.  This is only for admins.
   * @see  Controller_Rest_UserComments::put_members()
   */
  static function put_members($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $item = ORM::factory("Item", $id);
    if (!$item->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of comment ids.
    $member_ids = Rest::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        $comment = ORM::factory("Comment", $id);
        return (($type == "comment") && ($comment->item_id == $data)) ? $id : false;
      }, $item->id);

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
  static function delete($id, $params) {
    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $item = ORM::factory("Item", $id);
    if (!$item->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Delete all comments from the item.
    foreach ($item->comments->find_all() as $comment) {
      $comment->delete();
    }
  }

  /**
   * Return the relationship established by item_comments.  This adds "comments"
   * as a relationship of an "item" resource.
   */
  static function relationships($type, $id, $params) {
    return ($type == "item") ? array("comments" => array("item_comments", $id, $params)) : null;
  }
}
