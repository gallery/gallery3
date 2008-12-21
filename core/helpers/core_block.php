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

class core_block_Core {
  public static function page_bottom($theme) {
    if (Session::instance()->get("profiler", false)) {
      $profiler = new Profiler();
      $profiler->render();
    }

    if ($theme->item() && access::can("edit", $theme->item())) {
      return new View("in_place_edit.html");
    }
  }

  public static function admin_dashboard_blocks($theme) {
    $block = new Block();
    $block->id = "gWelcome";
    $block->title = _("Welcome to Gallery3");
    $block->content = new View("admin_block_welcome.html");
    $blocks[] = $block;

    $block = new Block();
    $block->id = "gMessages";
    $block->title = _("Status Messages");
    $block->content = new View("admin_block_messages.html");
    $blocks[] = $block;

    $block = new Block();
    $block->id = "gPhotoStream";
    $block->title = _("Photo Stream");
    $block->content = new View("admin_block_photo_stream.html");
    $block->content->photos =
      ORM::factory("item")->where("type", "photo")->orderby("created", "desc")->find_all(10);
    $blocks[] = $block;

    $block = new Block();
    $block->id = "gLogEntries";
    $block->title = _("Log Entries");
    $block->content = new View("admin_block_log_entries.html");
    $blocks[] = $block;

    return implode("\n", $blocks);
  }

  public static function admin_sidebar_blocks($theme) {
    $block = new Block();
    $block->id = "gStats";
    $block->title = _("Gallery Stats");
    $block->content = new View("admin_block_stats.html");
    $block->content->album_count = ORM::factory("item")->where("type", "album")->count_all();
    $block->content->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
    $blocks[] = $block;

    $block = new Block();
    $block->id = "gPlatform";
    $block->title = _("Platform Information");
    $block->content = new View("admin_block_platform.html");
    $blocks[] = $block;

    $block = new Block();
    $block->id = "gProjectNews";
    $block->title = _("Gallery Project News");
    $block->content = new View("admin_block_news.html");
    $block->content->feed = feed::parse("http://gallery.menalto.com/node/feed", 3);
    $blocks[] = $block;

    return implode("\n", $blocks);
  }
}