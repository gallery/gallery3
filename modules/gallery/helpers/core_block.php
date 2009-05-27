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
class core_block_Core {
  static function get_list() {
    return array(
      "welcome" => t("Welcome to Gallery 3!"),
      "photo_stream" => t("Photo Stream"),
      "log_entries" => t("Log Entries"),
      "stats" => t("Gallery Stats"),
      "platform_info" => t("Platform Information"),
      "project_news" => t("Gallery Project News"));
  }

  static function get($block_id) {
    $block = new Block();
    switch($block_id) {
    case "welcome":
      $block->css_id = "gWelcome";
      $block->title = t("Welcome to Gallery3");
      $block->content = new View("admin_block_welcome.html");
      break;

    case "photo_stream":
      $block->css_id = "gPhotoStream";
      $block->title = t("Photo Stream");
      $block->content = new View("admin_block_photo_stream.html");
      $block->content->photos =
        ORM::factory("item")->where("type", "photo")->orderby("created", "DESC")->find_all(10);
      break;

    case "log_entries":
      $block->css_id = "gLogEntries";
      $block->title = t("Log Entries");
      $block->content = new View("admin_block_log_entries.html");
      $block->content->entries = ORM::factory("log")->orderby("timestamp", "DESC")->find_all(5);
        break;

    case "stats":
      $block->css_id = "gStats";
      $block->title = t("Gallery Stats");
      $block->content = new View("admin_block_stats.html");
      $block->content->album_count = ORM::factory("item")->where("type", "album")->count_all();
      $block->content->photo_count = ORM::factory("item")->where("type", "photo")->count_all();
      break;

    case "platform_info":
      $block->css_id = "gPlatform";
      $block->title = t("Platform Information");
      $block->content = new View("admin_block_platform.html");
      if (is_readable("/proc/loadavg")) {
        $block->content->load_average =
          join(" ", array_slice(split(" ", array_shift(file("/proc/loadavg"))), 0, 3));
      } else {
        $block->content->load_average = t("Unavailable");
      }
      break;

    case "project_news":
      $block->css_id = "gProjectNews";
      $block->title = t("Gallery Project News");
      $block->content = new View("admin_block_news.html");
      $block->content->feed = feed::parse("http://gallery.menalto.com/node/feed", 3);
      break;

    case "block_adder":
      $block->css_id = "gBlockAdder";
      $block->title = t("Dashboard Content");
      $block->content = self::get_add_block_form();
    }

    return $block;
  }

  static function get_add_block_form() {
    $form = new Forge("admin/dashboard/add_block", "", "post",
                      array("id" => "gAddDashboardBlockForm"));
    $group = $form->group("add_block")->label(t("Add Block"));
    $group->dropdown("id")->label("Available Blocks")->options(block_manager::get_available());
    $group->submit("center")->value(t("Add to center"));
    $group->submit("sidebar")->value(t("Add to sidebar"));
    return $form;
  }
}