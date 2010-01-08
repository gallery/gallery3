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
class rest_Core {
  static function reply($data=array()) {
    Session::abort_save();

    if ($data) {
      print json_encode($data);
    }
  }

  static function set_active_user($access_token) {
    if (empty($access_token)) {
      identity::set_active_user(identity::guest());
      return;
    }

    $key = ORM::factory("user_access_token")
      ->where("access_key", "=", $access_token)
      ->find();

    if (!$key->loaded()) {
      throw new Rest_Exception("Forbidden", 403);
    }

    $user = identity::lookup_user($key->user_id);
    if (empty($user)) {
      throw new Rest_Exception("Forbidden", 403);
    }

    identity::set_active_user($user);
  }

  static function send_headers($exception) {
    header("HTTP/1.1 " . $exception->getCode() . " " . $exception->getMessage());
  }

  /**
   * Convert a REST url into an object.
   * Eg: "http://example.com/gallery3/index.php/rest/gallery/Family/Wedding" -> Item_Model
   *
   * @param string  the fully qualified REST url
   * @return mixed  the corresponding object (usually a model of some kind)
   */
  static function resolve($url) {
    $relative_url = substr($url, strlen(url::abs_site("rest")));
    $path = parse_url($relative_url, PHP_URL_PATH);
    $components = explode("/", $path, 3);

    $class = "$components[1]_rest";
    if (!method_exists($class, "resolve")) {
      throw new Kohana_404_Exception($url);
    }

    return call_user_func(array($class, "resolve"), !empty($components[2]) ? $components[2] : null);
  }
}
