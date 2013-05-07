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
class Gallery_Hook_GalleryBlock {
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
      $block->content = new View("admin/block_welcome.html");
      break;

    case "photo_stream":
      $block->css_id = "g-photo-stream";
      $block->title = t("Photo stream");
      $block->content = new View("admin/block_photo_stream.html");
      $block->content->photos = ORM::factory("Item")
        ->where("type", "=", "photo")->order_by("created", "DESC")->limit(10)->find_all();
      break;

    case "log_entries":
      $block->css_id = "g-log-entries";
      $block->title = t("Log entries");
      $block->content = new View("admin/block_log_entries.html");
      $block->content->entries = ORM::factory("Log")
        ->merge_order_by(array("timestamp" => "DESC", "id" => "DESC"))->limit(5)->find_all();
      break;

    case "stats":
      $block->css_id = "g-stats";
      $block->title = t("Gallery stats");
      $block->content = new View("admin/block_stats.html");
      $block->content->album_count =
        ORM::factory("Item")->where("type", "=", "album")->where("id", "<>", 1)->count_all();
      $block->content->photo_count = ORM::factory("Item")->where("type", "=", "photo")->count_all();
      break;

    case "platform_info":
      $block->css_id = "g-platform";
      $block->title = t("Platform information");
      $block->content = new View("admin/block_platform.html");
      break;

    case "project_news":
      $block->css_id = "g-project-news";
      $block->title = t("Gallery project news");
      $block->content = new View("admin/block_news.html");
      $block->content->feed = Feed::parse("http://galleryproject.org/node/feed", 3);
      break;

    case "block_adder":
      $form = Request::factory("admin/dashboard/add_block")->make_ajax()->execute()->body();
      if ($form) {
        $block->css_id = "g-block-adder";
        $block->title = t("Dashboard content");
        $block->content = $form;
      } else {
        $block = "";
      }
      break;

    case "language":
      $locales = Locales::installed();
      if (count($locales) > 1) {
        foreach ($locales as $locale => $display_name) {
          $locales[$locale] = SafeString::of_safe_html($display_name);
        }
        $block = new Block();
        $block->css_id = "g-user-language-block";
        $block->title = t("Language preference");
        $block->content = new View("gallery/user_languages_block.html");
        $block->content->installed_locales = array_merge(array("" => t("« none »")), $locales);
        $block->content->selected = (string) Locales::cookie_locale();
      } else {
        $block = "";
      }
      break;

    case "upgrade_checker":
      $block = new Block();
      $block->css_id = "g-upgrade-available-block";
      $block->title = t("Check for Gallery upgrades");
      $block->content = new View("gallery/upgrade_checker_block.html");
      $block->content->version_info = UpgradeChecker::version_info();
      $block->content->auto_check_enabled = UpgradeChecker::auto_check_enabled();
      $block->content->new_version = UpgradeChecker::get_upgrade_message();
      $block->content->build_number = Gallery::build_number();
    }
    return $block;
  }
}
