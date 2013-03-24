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
class gallery_block_Core {
  static function get_admin_list() {
    return array(
      "welcome" => t("Welcome to Gallery 3!"),
      "photo_stream" => t("Photo stream"),
      "log_entries" => t("Log entries"),
      "stats" => t("Gallery stats"),
      "platform_info" => t("Platform information"),
      "project_news" => t("Gallery project news"),
      "upgrade_checker" => t("Check for Gallery upgrades")
    );
  }

  static function get_site_list() {
    return array("language" => t("Language preference"));
  }

  static function get($block_id) {
    $block = new Block();
    switch ($block_id) {
    case "welcome":
      $block->css_id = "g-welcome";
      $block->title = t("Welcome to Gallery 3");
      $block->content = new View("admin_block_welcome.html");
      break;

    case "photo_stream":
      $block->css_id = "g-photo-stream";
      $block->title = t("Photo stream");
      $block->content = new View("admin_block_photo_stream.html");
      $block->content->photos = ORM::factory("item")
        ->where("type", "=", "photo")->order_by("created", "DESC")->find_all(10);
      break;

    case "log_entries":
      $block->css_id = "g-log-entries";
      $block->title = t("Log entries");
      $block->content = new View("admin_block_log_entries.html");
      $block->content->entries = ORM::factory("log")
        ->order_by(array("timestamp" => "DESC", "id" => "DESC"))->find_all(5);
      break;

    case "stats":
      $block->css_id = "g-stats";
      $block->title = t("Gallery stats");
      $block->content = new View("admin_block_stats.html");
      $block->content->album_count =
        ORM::factory("item")->where("type", "=", "album")->where("id", "<>", 1)->count_all();
      $block->content->photo_count = ORM::factory("item")->where("type", "=", "photo")->count_all();
      break;

    case "platform_info":
      $block->css_id = "g-platform";
      $block->title = t("Platform information");
      $block->content = new View("admin_block_platform.html");
      break;

    case "project_news":
      $block->css_id = "g-project-news";
      $block->title = t("Gallery project news");
      $block->content = new View("admin_block_news.html");
      $block->content->feed = feed::parse("http://galleryproject.org/node/feed", 3);
      break;

    case "block_adder":
      if ($form = gallery_block::get_add_block_form()) {
        $block->css_id = "g-block-adder";
        $block->title = t("Dashboard content");
        $block->content = $form;
      } else {
        $block = "";
      }
      break;

    case "language":
      $locales = locales::installed();
      if (count($locales) > 1) {
        foreach ($locales as $locale => $display_name) {
          $locales[$locale] = SafeString::of_safe_html($display_name);
        }
        $block = new Block();
        $block->css_id = "g-user-language-block";
        $block->title = t("Language preference");
        $block->content = new View("user_languages_block.html");
        $block->content->installed_locales = array_merge(array("" => t("« none »")), $locales);
        $block->content->selected = (string) locales::cookie_locale();
      } else {
        $block = "";
      }
      break;

    case "upgrade_checker":
      $block = new Block();
      $block->css_id = "g-upgrade-available-block";
      $block->title = t("Check for Gallery upgrades");
      $block->content = new View("upgrade_checker_block.html");
      $block->content->version_info = upgrade_checker::version_info();
      $block->content->auto_check_enabled = upgrade_checker::auto_check_enabled();
      $block->content->new_version = upgrade_checker::get_upgrade_message();
      $block->content->build_number = gallery::build_number();
    }
    return $block;
  }

  static function get_add_block_form() {
    $available_blocks = block_manager::get_available_admin_blocks();

    $active = array();
    foreach (array_merge(block_manager::get_active("dashboard_sidebar"),
                         block_manager::get_active("dashboard_center")) as $b) {
      unset($available_blocks[implode(":", $b)]);
    }

    if (!$available_blocks) {
      return;
    }

    $form = new Forge("admin/dashboard/add_block", "", "post",
                      array("id" => "g-add-dashboard-block-form"));
    $group = $form->group("add_block")->label(t("Add Block"));
    $group->dropdown("id")->label(t("Available blocks"))->options($available_blocks);
    $group->submit("center")->value(t("Add to center"));
    $group->submit("sidebar")->value(t("Add to sidebar"));
    return $form;
  }
}