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
class image_block_block_Core {
  static function get_site_list() {
    return array("random_image" => t("Random image"));
  }

  static function get($block_id, $theme) {
    $block = "";
    switch ($block_id) {
    case "random_image":
      // The random_query approach is flawed and doesn't always return a
      // result when there actually is one. Retry a *few* times.
      // @todo Consider another fallback if further optimizations are necessary.
      $attempts = 0;
      do {
        $item = item::random_query()->where("type", "!=", "album")->find_all(1)->current();
      } while (!$item && $attempts++ < 3);
      if ($item && $item->loaded()) {
        $block = new Block();
        $block->css_id = "g-image-block";
        $block->title = t("Random image");
        $block->content = new View("image_block_block.html");
        $block->content->item = $item;
      }
      break;
    }

    return $block;
  }
}
