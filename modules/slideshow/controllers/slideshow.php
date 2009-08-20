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
class Slideshow_Controller extends Controller {
  function album($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    print $this->_build_image_list($item->children());
  }

  function photo($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    print $this->_build_image_list($item->parent()->children());
  }

  function tag($item_id) {
    $item = ORM::factory("item", $item_id);
    $root = $item->id == 1 ? $item : ORM::factory("item", 1);
    access::required("view", $item);

    $v = new View("organize_dialog.html");
    $v->title = $item->title;
    $parents = array();
    foreach ($item->parents() as $parent) {
      $parents[$parent->id] = 1;
    }
    $parents[$item->id] = 1;

    $v->album_tree = self::_tree($root, $parents);
    $v->micro_thumb_grid = self::_get_micro_thumb_grid($item, 0);
    print $v;
  }

  private function _build_image_list($children) {
    $resizes = array();
    foreach ($children as $child) {
      switch($child->type) {
      case "album":
        if (!empty($child->album_cover_item_id)) {
          $cover = ORM::factory("item", $child->album_cover_item_id);
          $resizes[] = array("url" => $cover->resize_url(), "width" => $cover->resize_width,
                             "height" => $cover->resize_height);
        }
        break;
      case "photo":
        $resizes[] = array("url" => $child->resize_url(), "width" => $child->resize_width,
                           "height" => $child->resize_height);
        break;
      }
    }

    return json_encode($resizes);
  }
}
