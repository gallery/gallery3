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

class comment_block_Core {
  public static function head($theme) {
    $url = url::file("modules/comment/js/comment.js");
    return "<script src=\"$url\" type=\"text/javascript\"></script>\n";
  }

  public static function photo_bottom($theme) {
    $block = new Block;
    $block->id = "gComments";
    $block->title = _("Comments");

    $view = new View("comments.html");
    $view->comments = ORM::factory("comment")
      ->where("item_id", $theme->item()->id)
      ->where("published", 1)
      ->orderby("created", "ASC")
      ->find_all();

    $block->content = $view;
    $block->content .= comment::get_add_form($theme->item())->render("form.html");
    return $block;
  }

  public static function admin_dashboard_blocks($theme) {
    $block = new Block();
    $block->id = "gRecentComments";
    $block->title = _("Recent Comments");
    $block->content = new View("admin_block_recent_comments.html");
    $block->content->comments =
      ORM::factory("comment")->orderby("created", "DESC")->limit(5)->find_all();
    return $block;
  }
}