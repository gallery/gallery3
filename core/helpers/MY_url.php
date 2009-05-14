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
class url extends url_Core {
  static function site($uri, $protocol=false) {
    if (($pos = strpos($uri, "?")) !== false) {
      list ($uri, $query) = explode("?", $uri, 2);
      $query = "?$query";
    } else {
      $query = "";
    }

    $parts = explode("/", $uri, 3);
    if ($parts[0] == "albums" || $parts[0] == "photos") {
      $uri = model_cache::get("item", $parts[1])->relative_path();
    }
    return parent::site($uri . $query, $protocol);
  }

  static function parse_url() {
    if (Router::$controller) {
      return;
    }

    $count = count(Router::$segments);
    foreach (ORM::factory("item")
             ->where("name", html_entity_decode(Router::$segments[$count - 1], ENT_QUOTES))
             ->where("level", $count + 1)
             ->find_all() as $match) {
      if ($match->relative_path() == html_entity_decode(Router::$current_uri, ENT_QUOTES)) {
        $item = $match;
      }
    }

    if (!empty($item)) {
      Router::$controller = "{$item->type}s";
      Router::$controller_path = APPPATH . "controllers/{$item->type}s.php";
      Router::$method = $item->id;
    }
  }

  /**
   * Just like url::file() except that it returns an absolute URI
   */
  static function abs_file($path) {
    return url::base(
      false, (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') ? 'http' : 'https') . $path;
  }

  /**
   * Just like url::site() except that it returns an absolute URI and
   * doesn't take a protocol parameter.
   */
  static function abs_site($path) {
    return url::site(
      $path, (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') ? 'http' : 'https');
  }

  /**
   * Just like url::current except that it returns an absolute URI
   */
  static function abs_current($qs=false) {
    return self::abs_site(url::current($qs));
  }
}
