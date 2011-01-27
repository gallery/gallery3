<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class comment_block_Core {
  static function get_admin_list() {
    return array("recent_comments" => t("Recent comments"));
  }

  static function get($block_id) {
    $block = new Block();
    switch ($block_id) {
    case "recent_comments":
      $block->css_id = "g-recent-comments";
      $block->title = t("Recent comments");
      $block->content = new View("admin_block_recent_comments.html");
      $block->content->comments =
        ORM::factory("comment")->order_by("created", "DESC")->limit(5)->find_all();
      break;
    }

    return $block;
  }
}