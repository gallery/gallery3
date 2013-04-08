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
class Gallery_URL extends Kohana_URL {
  /**
   * Just like URL::file() except that it returns an absolute URI
   */
  static function abs_file($path) {
    return URL::base(true, false) . $path;
  }

  /**
   * Just like URL::site() except that it returns an absolute URI and
   * doesn't take a protocol parameter.
   */
  static function abs_site($path) {
    return URL::site($path, true);
  }

  /**
   * Just like URL::current except that it returns an absolute URI
   */
  static function abs_current($qs=false) {
    return self::abs_site(URL::current($qs));
  }

  /**
   * Just like URL::merge except that it escapes any XSS in the path.
   */
  static function merge(array $arguments) {
    return htmlspecialchars(parent::merge($arguments));
  }

  /**
   * Just like URL::current except that it escapes any XSS in the path.
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
