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
class Tag_Hook_TagBlock {
  static function get_site_list() {
    return array("tag" => t("Popular tags"));
  }

  static function get($block_id, $theme) {
    $block = "";
    switch ($block_id) {
    case "tag":
      $block = new Block();
      $block->css_id = "g-tag";
      $block->title = t("Popular tags");
      $block->content = new View("tag/block.html");
      $block->content->cloud = Tag::cloud(Module::get_var("tag", "tag_cloud_size", 30));

      if ($theme->item() && $theme->page_subtype() != "tag" && Access::can("edit", $theme->item())) {
        $block->content->form = Request::factory("tags/add/{$theme->item()->id}")->execute()->body();
      } else {
        $block->content->form = "";
      }
      break;
    }
    return $block;
  }
}