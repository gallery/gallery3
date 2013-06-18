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

  /**
   * GET the comment members of the item_comments resource.
   * @see  Controller_Rest_Comments::get_members().
   */
  static function get_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    $item = ORM::factory("Item", $id);
    Access::required("view", $item);

    $members = $item->comments
      ->limit(Arr::get($params, "num", static::$default_params["num"]))
      ->offset(Arr::get($params, "start", static::$default_params["start"]))
      ->order_by("created", "DESC");

    $data = array();
    foreach ($members->find_all() as $member) {
      $data[] = array("comments", $member->id);
    }

    return $data;
  }

  /**
   * PUT the comment members of the item_comments resource.  This replaces the comments list
   * with this one, and removes (but doesn't add) comments as needed.  This is only for admins.
   * @see  Controller_Rest_UserComments::put_members()
   */
  static function put_members($id, $params) {
    if (empty($id)) {
      return null;
    }

    if (!Identity::active_user()->admin) {
      throw Rest_Exception::factory(403);
    }

    $item = ORM::factory("Item", $id);
    if (!$item->loaded()) {
      throw Rest_Exception::factory(404);
    }

    // Resolve our members list into an array of comment ids.
    $member_ids = RestAPI::resolve_members($params["members"],
      function($type, $id, $params, $data) {
        $comment = ORM::factory("Comment", $id);
        return (($type == "comments") && ($comment->item_id == $data)) ? $id : false;
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
    if (empty($id)) {
      return null;
    }

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
   * as a relationship of an "items" resource.
   */
  static function relationships($type, $id, $params) {
    return (($type == "items") && (!empty($id))) ?
      array("comments" => array("item_comments", $id, $params)) : null;
  }
}
