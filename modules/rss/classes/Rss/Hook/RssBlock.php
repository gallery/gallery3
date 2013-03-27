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
class rss_block_Core {
  static function get_site_list() {
    return array("rss_feeds" => t("Available RSS feeds"));
  }

  static function get($block_id, $theme) {
    $block = "";
    switch ($block_id) {
    case "rss_feeds":
      $feeds = array();
      foreach (module::active() as $module) {
        $class_name = "{$module->name}_rss";
        if (class_exists($class_name) && method_exists($class_name, "available_feeds")) {
          $feeds = array_merge($feeds,
            call_user_func(array($class_name, "available_feeds"), $theme->item(), $theme->tag()));
        }
      }
      if (!empty($feeds)) {
        $block = new Block();
        $block->css_id = "g-rss";
        $block->title = t("Available RSS feeds");
        $block->content = new View("rss_block.html");
        $block->content->feeds = $feeds;
      }
      break;
    }

    return $block;
  }
}
