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
class url extends url_Core {
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

    $item = item::find_by_relative_url(html_entity_decode(Router::$current_uri, ENT_QUOTES));
    if ($item && $item->loaded()) {
      Router::$controller = "{$item->type}s";
      Router::$controller_path = MODPATH . "gallery/controllers/{$item->type}s.php";
      Router::$method = "show";
      Router::$arguments = array($item);
    }
  }

  /**
   * Just like url::file() except that it returns an absolute URI
   */
  static function abs_file($path) {
    return url::base(false, request::protocol()) . $path;
  }

  /**
   * Just like url::site() except that it returns an absolute URI and
   * doesn't take a protocol parameter.
   */
  static function abs_site($path) {
    return url::site($path, request::protocol());
  }

  /**
   * Just like url::current except that it returns an absolute URI
   */
  static function abs_current($qs=false) {
    return self::abs_site(url::current($qs));
  }

  /**
   * Just like url::merge except that it escapes any XSS in the path.
   */
  static function merge(array $arguments) {
    return htmlspecialchars(parent::merge($arguments));
  }

  /**
   * Just like url::current except that it escapes any XSS in the path.
   */
  static function current($qs=false, $suffix=false) {
    return htmlspecialchars(parent::current($qs, $suffix));
  }

  /**
   * Merge extra an query string onto a given url safely.
   * @param string the original url
   * @param array the query string data in key=value form
   */
  static function merge_querystring($url, $query_params) {
    $qs = implode("&", $query_params);
    if (strpos($url, "?") === false) {
      return $url . "?$qs";
    } else {
      return $url . "&$qs";
    }
  }
}
