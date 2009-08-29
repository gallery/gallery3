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
    $url = parent::site($uri . $query, $protocol);
    return SafeString::of_safe_html($url);
  }

  static function parse_url() {
    if (Router::$controller) {
      return;
    }

    // Work around problems with the CGI sapi by enforcing our default path
    if ($_SERVER["SCRIPT_NAME"] && "/" . Router::$current_uri == $_SERVER["SCRIPT_NAME"]) {
      Router::$controller_path = MODPATH . "gallery/controllers/albums.php";
      Router::$controller = "albums";
      Router::$method = 1;
      return;
    }

    $item = self::get_item_from_uri(Router::$current_uri);
    if ($item && $item->loaded) {
      Router::$controller = "{$item->type}s";
      Router::$controller_path = MODPATH . "gallery/controllers/{$item->type}s.php";
      Router::$method = $item->id;
    }
  }

  /**
   * Return the item that the uri is referencing
   */
  static function get_item_from_uri($uri) {
    $current_uri = html_entity_decode($uri, ENT_QUOTES);
    $item = ORM::factory("item")->where("relative_path_cache", $current_uri)->find();
    if (!$item->loaded) {
      // It's possible that the relative path cache for the item we're looking for is out of date,
      // so find it the hard way.
      $count = count(Router::$segments);
      foreach (ORM::factory("item")
               ->where("name", html_entity_decode(Router::$segments[$count - 1], ENT_QUOTES))
               ->where("level", $count + 1)
               ->find_all() as $match) {
        if ($match->relative_path() == $current_uri) {
          $item = $match;
        }
      }
    }
    return $item;
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

  public static function base($index=false, $protocol=false) {
    $url = parent::base($index, $protocol);
    return SafeString::of_safe_html($url);
  }

  public static function current($qs=false) {
    $url = parent::current($qs);
    return SafeString::of_safe_html($url);
  }

  public static function file($file, $index=false) {
    $url = parent::file($file, $index);
    return SafeString::of_safe_html($url);
  }

  public static function merge(array $arguments) {
    $url = parent::merge($arguments);
    return SafeString::of_safe_html($url);
  }

}
