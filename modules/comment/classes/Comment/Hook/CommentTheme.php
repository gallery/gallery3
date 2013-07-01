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
class Comment_Hook_CommentTheme {
  static function head($theme) {
    return $theme->css("comment.css")
      . $theme->script("comment.js");
  }

  static function admin_head($theme) {
    return $theme->css("comment.css");
  }

  static function photo_bottom($theme) {
    $block = new Block;
    $block->css_id = "g-comments";
    $block->title = t("Comments");
    $block->anchor = "comments";

    $view = new View("comment/block.html");
    $view->comments = $theme->item()->comments
      ->where("state", "=", "published")
      ->order_by("created", "ASC")
      ->order_by("id", "ASC")
      ->find_all();

    $block->content = $view;
    return $block;
  }
}
