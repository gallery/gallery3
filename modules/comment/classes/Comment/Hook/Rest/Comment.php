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
class comment_rest_Core {
  static function get($request) {
    $comment = rest::resolve($request->url);
    access::required("view", $comment->item());

    return array(
      "url" => $request->url,
      "entity" => $comment->as_restful_array(),
      "relationships" => rest::relationships("comment", $comment));
  }

  static function put($request) {
    // Only admins can edit comments, for now
    if (!identity::active_user()->admin) {
      access::forbidden();
    }

    $comment = rest::resolve($request->url);
    $comment = ORM::factory("comment");
    $comment->text = $request->params->text;
    $comment->save();
  }

  static function delete($request) {
    if (!identity::active_user()->admin) {
      access::forbidden();
    }

    $comment = rest::resolve($request->url);
    access::required("edit", $comment->item());

    $comment->delete();
  }

  static function relationships($resource_type, $resource) {
    switch ($resource_type) {
    case "item":
      return array(
        "comments" => array(
          "url" => rest::url("item_comments", $resource)));
    }
  }

  static function resolve($id) {
    $comment = ORM::factory("comment", $id);
    if (!access::can("view", $comment->item())) {
      throw new Kohana_404_Exception();
    }
    return $comment;
  }

  static function url($comment) {
    return url::abs_site("rest/comment/{$comment->id}");
  }
}
