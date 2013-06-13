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
  static function get($request) {
    $comment = Rest::resolve($request->url);
    Access::required("view", $comment->item);

    return array(
      "url" => $request->url,
      "entity" => $comment->as_restful_array(),
      "relationships" => Rest::relationships("comment", $comment));
  }

  static function put($request) {
    // Only admins can edit comments, for now
    if (!Identity::active_user()->admin) {
      throw HTTP_Exception::factory(403);
    }

    $comment = Rest::resolve($request->url);
    $comment = ORM::factory("Comment");
    $comment->text = $request->params->text;
    $comment->save();
  }

  static function delete($request) {
    if (!Identity::active_user()->admin) {
      throw HTTP_Exception::factory(403);
    }

    $comment = Rest::resolve($request->url);
    Access::required("edit", $comment->item);

    $comment->delete();
  }

  static function relationships($resource_type, $resource) {
    switch ($resource_type) {
    case "item":
      return array(
        "comments" => array(
          "url" => Rest::url("item_comments", $resource)));
    }
  }

  static function resolve($id) {
    $comment = ORM::factory("Comment", $id);
    if (!Access::can("view", $comment->item)) {
      throw HTTP_Exception::factory(404);
    }
    return $comment;
  }

  static function url($comment) {
    return URL::abs_site("rest/comment/{$comment->id}");
  }
}
