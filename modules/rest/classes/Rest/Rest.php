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
class Rest_Rest {
  const API_VERSION = "3.0";

  static $allowed_methods = array(
    HTTP_Request::GET,
    HTTP_Request::POST,
    HTTP_Request::PUT,
    HTTP_Request::DELETE
  );

  static function init() {
    // Add the REST API version and allowed methods to the header.  Since we're adding it to
    // Response::$default_config, even error responses (e.g. 404) will have these headers.
    Response::$default_config = array_merge_recursive(Response::$default_config,
      array("_header" => array(
        "x-gallery-api-version" => Rest::API_VERSION,
        "allow" => static::$allowed_methods
      )));

    // Set the error view to be the restful view.
    Kohana_Exception::$error_view = "rest/error.json";
    Kohana_Exception::$error_view_content_type = "application/json";

    // We don't need to save REST sessions.
    Session::instance()->abort_save();
  }

  static function set_active_user($access_key) {
    if (empty($access_key)) {
      if (Module::get_var("rest", "allow_guest_access")) {
        Identity::set_active_user(Identity::guest());
        return;
      } else {
        throw Rest_Exception::factory(403);
      }
    }

    $key = ORM::factory("UserAccessKey")
      ->where("access_key", "=", $access_key)
      ->find();

    if (!$key->loaded()) {
      throw Rest_Exception::factory(403);
    }

    $user = Identity::lookup_user($key->user_id);
    if (empty($user)) {
      throw Rest_Exception::factory(403);
    }

    Identity::set_active_user($user);
  }

  static function reset_access_key() {
    $key = ORM::factory("UserAccessKey")
      ->where("user_id", "=", Identity::active_user()->id)
      ->find();
    if ($key->loaded()) {
      $key->delete();
    }
    return Rest::access_key();
  }

  static function access_key($user=null) {
    $user_id = empty($user) ? Identity::active_user()->id : $user->id;

    $key = ORM::factory("UserAccessKey")
      ->where("user_id", "=", $user_id)
      ->find();

    if (!$key->loaded()) {
      $key->user_id = $user_id;
      $key->access_key = md5(Random::hash() . Access::private_key());
      $key->save();
    }

    return $key->access_key;
  }

  /**
   * Convert a REST url into an object.
   * Eg:
   *   http://example.com/gallery3/index.php/rest/item/35          -> Model_Item
   *   http://example.com/gallery3/index.php/rest/tag/16           -> Model_Tag
   *   http://example.com/gallery3/index.php/rest/tagged_item/1,16 -> [Model_Tag, Model_Item]
   *
   * @param string  the fully qualified REST url
   * @return mixed  the corresponding object (usually a model of some kind)
   */
  static function resolve($url) {
    $relative_url = substr($url, strlen(URL::abs_site("rest")));

    $path = parse_url($relative_url, PHP_URL_PATH);
    $components = explode("/", $path, 3);

    if (count($components) != 3) {
      throw HTTP_Exception::factory(404, $url);
    }

    $class = "Hook_Rest_" . Inflector::convert_module_to_class_name($components[1]);
    if (!class_exists($class) || !method_exists($class, "resolve")) {
      throw HTTP_Exception::factory(404, $url);
    }

    return call_user_func(array($class, "resolve"), !empty($components[2]) ? $components[2] : null);
  }

  /**
   * Return an absolute url used for REST resource location.
   * @param  string  resource type (eg, "item", "tag")
   * @param  object  resource
   */
  static function url() {
    $args = func_get_args();
    $resource_type = array_shift($args);

    $class = "Hook_Rest_" . Inflector::convert_module_to_class_name($resource_type);
    if (!class_exists($class) || !method_exists($class, "url")) {
      throw Rest_Exception::factory(400);
    }

    $url = call_user_func_array(array($class, "url"), $args);
    if (Request::current()->query("output") == "html") {
      if (strpos($url, "?") === false) {
        $url .= "?output=html";
      } else {
        $url .= "&output=html";
      }
    }
    return $url;
  }

  static function relationships($resource_type, $resource) {
    $results = array();
    foreach (Module::active() as $module) {
      foreach (glob(MODPATH . "{$module->name}/classes/Hook/Rest/*.php") as $filename) {
        $class = "Hook_Rest_" . str_replace(".php", "", basename($filename));
        if (class_exists($class) && method_exists($class, "relationships")) {
          if ($tmp = call_user_func(array($class, "relationships"), $resource_type, $resource)) {
            $results = array_merge($results, $tmp);
          }
        }
      }
    }

    return $results;
  }

  /**
   * Return an array of all available REST resource types.
   * @param  boolean  flag to return class instead of resource names (e.g. "TagItems" vs. "tag_items")
   */
  static function registry($return_class_names=false) {
    $results = array();
    foreach (Module::active() as $module) {
      foreach (glob(MODPATH . "{$module->name}/classes/Controller/Rest/*.php") as $filename) {
        $class_name = str_replace(".php", "", basename($filename));
        $results[] = $return_class_names ? $class_name :
          Inflector::convert_class_to_module_name($class_name);
      }
    }

    return array_unique($results);
  }
}
