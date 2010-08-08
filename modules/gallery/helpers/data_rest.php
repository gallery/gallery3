<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class data_rest_Core {
  static function get($request) {
    $item = rest::resolve($request->url);
    access::required("view", $item);

    $p = $request->params;
    switch (isset($p->size) ? $p->size : "full") {
    case "thumb":
      $entity = array(
        "width" => $item->thumb_width,
        "height" => $item->thumb_height,
        "path" => $item->thumb_path());
      break;

    case "resize":
      $entity = array(
        "width" => $item->resize_width,
        "height" => $item->resize_height,
        "path" => $item->resize_path());
      break;

    default:
    case "full":
      $entity = array(
        "width" => $item->width,
        "height" => $item->height,
        "path" => $item->file_path());
      break;
    }

    $entity["size"] = filesize($entity["path"]);
    $entity["contents"] = file_get_contents($entity["path"]);
    unset($entity["path"]);

    $result = array(
      "url" => $request->url,
      "entity" => $entity,
      "relationships" => rest::relationships("data", $item));
    return $result;
  }

  static function put($request) {
    $item = rest::resolve($request->url);
    access::required("edit", $item);

    if ($item->is_album()) {
      throw new Rest_Exception("Bad Request", 400, array("errors" => array("type" => "invalid")));
    }

    $item->set_data_file($request->file);
    $item->save();
  }

  static function resolve($id) {
    $item = ORM::factory("item", $id);
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }
    return $item;
  }

  static function url($item) {
    return url::abs_site("rest/data/{$item->id}");
  }
}
