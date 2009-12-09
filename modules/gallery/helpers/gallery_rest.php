<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class gallery_rest_Core {
  static function get($request) {
    if (empty($request->path)) {
      return rest::invalid_request();
    }

    $item = ORM::factory("item")
      ->where("relative_url_cache", $request->path)
      ->viewable()
      ->find();

    if (!$item->loaded) {
      return rest::not_found("Resource: {$request->path} missing.");
    }

    $response_data = array("path" => $item->relative_url(),
                           "title" => $item->title,
                           "thumb_url" => $item->thumb_url(),
                           "url" => $item->abs_url(),
                           "description" => $item->description,
                           "internet_address" => $item->slug);

    $children = self::_get_children($item, $request);
    if (!empty($children)) {
      $response_data["children"] = $children;
    }
    return rest::success(array($item->type => $response_data));
  }

  private static function _get_children($item, $request) {
    $children = array();
    $limit = empty($request->limit) ? null : $request->limit;
    $offset = empty($request->offset) ? null : $request->offset;
    $where = empty($request->filter) ? array() : array("type" => $request->filter);
    foreach ($item->viewable()->children($limit, $offset, $where) as $child) {
      $children[] = array("type" => $child->type,
                          "has_children" => $child->children_count() > 0,
                          "path" => $child->relative_url(),
                          "title" => $child->title);
    }

    return $children;
  }
}
