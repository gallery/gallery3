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

class rss_Core {
  static function feed_link($uri) {
    $url = url::site("rss/feed/$uri");
    return "<link rel=\"alternate\" type=\"" . rest::RSS . "\" href=\"$url\" />";
  }

  /**
   * Get all available rss feeds
   */
  static function available_feeds($item, $sidebar_only=true) {
    $feeds = array();
    foreach (module::active() as $module) {
      $class_name = "{$module->name}_rss";
      if (method_exists($class_name, "available_feeds")) {
        foreach (call_user_func(array($class_name, "available_feeds"), $item) as $feed) {
          if ($sidebar_only && !$feed["sidebar"]) {
            continue;
          }
          $feeds[$feed["description"]] = url::site("rss/feed/{$feed['uri']}");
        }
      }
    }

    return $feeds;
  }

  static function feed_data($feed, $offset, $limit, $id) {
    foreach (module::active() as $module) {
      $class_name = "{$module->name}_rss";
      if (method_exists($class_name, $feed)) {
        return call_user_func(array($class_name, $feed), $offset, $limit, $id);
      }
    }
  }
}