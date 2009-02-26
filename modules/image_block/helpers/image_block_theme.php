<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class image_block_theme_Core {
  static function sidebar_blocks($theme) {
    $result = ORM::factory("item")
      ->viewable()
      ->select("COUNT(*) AS C")
      ->find();
    if (empty($result->C)) {
      return "";
    }

    $block = new Block();
    $block->css_id = "gImageBlock";
    $block->title = t("Random Image");
    $block->content = new View("image_block_block.html");

    $result = ORM::factory("item")
      ->viewable()
      ->select("MAX(rand_key) AS max_random")
      ->find();

    $max_rand = $result->max_random;
    $random = ((float)mt_rand()) / (float)mt_getrandmax();

    $items = ORM::factory("item")
      ->viewable()
      ->where("type !=", "album")
      ->where("rand_key < ", $max_rand * $random)
      ->orderby(array("rand_key" => "DESC"))
      ->find_all(1);

    if ($items->count() == 0) {
      $items = ORM::factory("item")
        ->viewable()
        ->where("type !=", "album")
        ->where("rand_key > ", $max_rand * $random)
        ->orderby(array("rand_key" => "DESC"))
        ->find_all(1);
     
    }
 
    $block->content->item = $items->current();

    return $items->count() == 0 ? "" : $block;
  }
}
