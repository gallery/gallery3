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

    print json_encode(array_values($this->_build_image_list($item->children())));
  }

  function photo($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);

    $images = $this->_build_image_list($item->parent()->children());
    $this_photo = array_search($item_id, array_keys($images));
    $images = array_merge(array_slice($images, $this_photo), array_slice($images, 0, $this_photo));

    print json_encode($images);
  }

  function tag($tag_id) {
    $tag = ORM::factory("tag", $tag_id);
    print json_encode(array_values($this->_build_image_list($tag->items())));
  }

  private function _build_image_list($children) {
    $resizes = array();
    foreach ($children as $child) {
      switch($child->type) {
      case "album":
        if (!empty($child->album_cover_item_id)) {
          $cover = ORM::factory("item", $child->album_cover_item_id);
          $resizes[$child->id] = array("url" => $cover->resize_url(),
            "width" => $cover->resize_width, "height" => $cover->resize_height);
        }
        break;
      case "photo":
        $resizes[$child->id] = array("url" => $child->resize_url(),
          "width" => $child->resize_width, "height" => $child->resize_height);
        break;
      }
    }

    return $resizes;
  }
}
