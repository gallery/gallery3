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
class tag_theme_Core {
  static function head($theme) {
    $url = url::file("modules/tag/js/tag.js");
    return "<script src=\"$url\" type=\"text/javascript\"></script>";
  }

  static function sidebar_blocks($theme) {
    // @todo this needs to be data driven
    if (!$theme->item()) {
      return;
    }

    $block = new Block();
    $block->css_id = "gTag";
    $block->title = t("Popular Tags");
    $block->content = new View("tag_block.html");
    $block->content->cloud = tag::cloud(30);

    if ($theme->page_type() != "tag" && access::can("edit", $theme->item())) {
      $controller = new Tags_Controller();
      $block->content->form = tag::get_add_form($theme->item());
    } else {
      $block->content->form = "";
    }

    return $block;
  }

  static function sort_by_name($tag1, $tag2) {
    return strcasecmp($tag1->name, $tag2->name);
  }
}