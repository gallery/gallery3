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
class comments_rest_Core {
  /**
   * Possible request parameters:
   *   start=#
   *     start at the Nth comment (zero based)
   *
   *   num=#
   *     return up to N comments (max 100)
   */
  static function get($request) {
    $comments = array();

    $p = $request->params;
    $num = isset($p->num) ? min((int)$p->num, 100) : 10;
    $start = isset($p->start) ? (int)$p->start : 0;

    foreach (ORM::factory("comment")->viewable()->find_all($num, $start) as $comment) {
      $comments[] = rest::url("comment", $comment);
    }
    return array("url" => rest::url("comments"),
                 "members" => $comments);
  }


  static function post($request) {
    $entity = $request->params->entity;

    $item = rest::resolve($entity->item);
    access::required("edit", $item);

    $comment = ORM::factory("comment");
    $comment->author_id = identity::active_user()->id;
    $comment->item_id = $item->id;
    $comment->text = $entity->text;
    $comment->save();

    return array("url" => rest::url("comment", $comment));
  }

  static function url() {
    return url::abs_site("rest/comments");
  }
}
