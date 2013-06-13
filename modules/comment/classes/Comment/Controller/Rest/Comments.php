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
class Comment_Controller_Rest_Comments extends Controller_Rest {
  /**
   * This resource represents a collection of comment resources.
   *
   * GET displays the collection of comments (no parameters accepted).
   *   @see  Controller_Rest_Comments::get_members()
   *
   * POST *requires* the following post parameters:
   *   entity
   *     Add a comment
   *   @see  Controller_Rest_Comments::post_entity()
   */

  /**
   * GET the members of the comments resource.
   */
  static function get_members($id, $params) {
    $members = ORM::factory("Comment")
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
   * POST a comment's entity.  This generates a new comment model.
   */
  static function post_entity($id, $params) {
    if (!property_exists($params["entity"], "item")) {
      throw Rest_Exception::factory(400, array("item" => "required"));
    }

    list ($i_type, $i_id, $i_params) = Rest::resolve($params["entity"]->item);
    if ($i_type != "item") {
      throw Rest_Exception::factory(400, array("item" => "invalid"));
    }

    $item = ORM::factory("Item", $i_id);
    if (!Comment::can_comment($item)) {
      throw Rest_Exception::factory(403);
    }

    // Build the comment model.
    $comment = ORM::factory("Comment");
    $comment->author_id = Identity::active_user()->id;
    $comment->item_id = $item->id;

    // Add fields from a whitelist.
    foreach (array("text", "state", "guest_name", "guest_email", "guest_url") as $field) {
      if (property_exists($params["entity"], $field)) {
        $comment->$field = $params["entity"]->$field;
      }
    }

    $comment->save();

    // Success!  Return the resource triad.
    return array("comment", $comment->id);
  }
}
