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
class Comment_Hook_Rest_ItemComments {
  static function get($request) {
    $item = Rest::resolve($request->url);
    Access::required("view", $item);

    $comments = array();
    foreach (ORM::factory("Comment")
             ->viewable()
             ->where("item_id", "=", $item->id)
             ->order_by("created", "DESC")
             ->find_all() as $comment) {
      $comments[] = Rest::url("comment", $comment);
    }

    return array(
      "url" => $request->url,
      "members" => $comments);
  }

  static function resolve($id) {
    $item = ORM::factory("Item", $id);
    if (!Access::can("view", $item)) {
      throw HTTP_Exception::factory(404);
    }
    return $item;
  }

  static function url($item) {
    return URL::abs_site("rest/item_comments/{$item->id}");
  }
}
