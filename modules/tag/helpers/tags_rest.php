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
class tags_rest_Core {
  /**
   * Possible request parameters:
   *   start=#
   *     start at the Nth comment (zero based)
   *
   *   num=#
   *     return up to N comments (max 100)
   */
  static function get($request) {
    $tags = array();

    $num = 10;
    $start = 0;
    if (isset($request->params)) {
      $p = $request->params;
      $num = isset($p->num) ? min((int)$p->num, 100) : 10;
      $start = isset($p->start) ? (int)$p->start : 0;
    }

    foreach (ORM::factory("tag")->find_all($num, $start) as $tag) {
      $tags[] = rest::url("tag", $tag);
    }
    return array("url" => rest::url("tags"),
                 "members" => $tags);
  }

  static function post($request) {
    // The user must have some edit permission somewhere to create a tag.
    if (!identity::active_user()->admin) {
      $query = db::build()->from("access_caches")->and_open();
      foreach (identity::active_user()->groups() as $group) {
        $query->or_where("edit_{$group->id}", "=", access::ALLOW);
      }
      $has_any_edit_perm = $query->close()->count_records();
      if (!$has_any_edit_perm) {
        access::forbidden();
      }
    }

    if (empty($request->params->entity->name)) {
      throw new Rest_Exception("Bad Request", 400);
    }

    $tag = ORM::factory("tag")->where("name", "=", $request->params->entity->name)->find();
    if (!$tag->loaded()) {
      $tag->name = $request->params->entity->name;
      $tag->count = 0;
      $tag->save();
    }

    return array("url" => rest::url("tag", $tag));
  }

  static function url() {
    return url::abs_site("rest/tags");
  }
}
