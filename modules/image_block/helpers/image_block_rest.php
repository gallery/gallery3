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
class image_block_rest_Core {
  static function get($request) {
    $path = implode("/", $request->arguments);
    switch ($path) {
    case "random":
      $random = ((float)mt_rand()) / (float)mt_getrandmax();

      $items = ORM::factory("item")
        ->viewable()
        ->where("type !=", "album")
        ->where("rand_key < ", $random)
        ->orderby(array("rand_key" => "DESC"))
        ->find_all(1);

      if ($items->count() == 0) {
        // Try once more.  If this fails, just ditch the block altogether
        $items = ORM::factory("item")
          ->viewable()
          ->where("type !=", "album")
          ->where("rand_key >= ", $random)
          ->orderby(array("rand_key" => "DESC"))
          ->find_all(1);
      }
      break;
    default:
      return rest::fail("Unsupported block type: '{$path}'");
    }

    if ($items->count() > 0) {
      $item = $items->current();
      $response_data = array("name" => $item->name,
                             "path" => $item->relative_url(),
                             "title" => $item->title,
                             "thumb_url" => $item->thumb_url(true),
                             "thumb_size" => array("height" => $item->thumb_height,
                                                   "width" => $item->thumb_width));

      return rest::success(array("resource" => $response_data));
    } else {
      return rest::fail("No Image found");
    }
  }
}
