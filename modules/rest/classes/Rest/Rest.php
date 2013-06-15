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
  const API_VERSION = "3.1";

  static $allowed_methods = array(
    HTTP_Request::GET,
    HTTP_Request::POST,
    HTTP_Request::PUT,
    HTTP_Request::DELETE,
    HTTP_Request::OPTIONS
  );

  static $allowed_headers = array(
    "X-Gallery-Request-Key",
    "X-Gallery-Request-Method",
    "X-Requested-With"
  );

  static $exposed_headers = array(
    "X-Gallery-Api-Version",
    "Allow"
  );

  static $preflight_max_age = 604800;  // one week

  static function init() {
    // Add the REST API version and allowed methods to the header.  Since we're adding it to
    // Response::$default_config, even error responses (e.g. 404) will have these headers.
    Response::$default_config = array_merge_recursive(Response::$default_config,
      array("_header" => array(
        "X-Gallery-Api-Version" => Rest::API_VERSION,
        "Allow" => static::$allowed_methods
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
   * Convert a REST url into a type/id/params triad.
   * Eg:
   *   http://example.com/gallery3/index.php/rest/item/35          -> "item", 35, array()
   *   http://example.com/gallery3/index.php/rest/item_comments    -> "item_comments", null, array()
   *   http://example.com/gallery3/index.php/rest/data/1?size=full -> "data", 1, array("size" => "full")
   *
   * @param string  the fully qualified REST url
   * @return array  the type/id/params triad
   */
  static function resolve($url) {
    $relative_url = substr($url, strlen(URL::abs_site("rest")));  // e.g. "/data/1?size=full"

    $path =  parse_url($relative_url, PHP_URL_PATH);
    $query = parse_url($relative_url, PHP_URL_QUERY);
    $components = explode("/", $path, 3);

    if (empty($components[1])) {
      throw Rest_Exception::factory(404);
    }
    $type = $components[1];

    $id = empty($components[2]) ? null : $components[2];

    $params = array();
    if (!empty($query)) {
      // @todo: we really shouldn't do raw query parsing at this level - move this elsewhere.
      $pairs = explode("&", $query);
      foreach ($pairs as $pair) {
        list ($key, $value) = (strpos($pair, "=") === false) ?
          array($pair, "") : explode("=", $pair, 2);
        $params[urldecode($key)] = urldecode($value);
      }
    }

    return array($type, $id, $params);
  }

  /**
   * Convert a list of member REST urls into an array.  If no callback is given, then each
   * array element is a type/id/params triad.  If a callback (and optionally callback_data)
   * is given, then each array element is the return value of the callback.  If the callback
   * returns false, then this fires a 400 Bad Request.
   *
   * This function maintains the original array keys of the members list.
   *
   * @param  array  REST urls
   * @param  mixed  callback function (optional, can be anonymous or named)
   * @param  mixed  callback data (optional)
   * @return array
   */
  static function resolve_members($members, $callback=null, $callback_data=null) {
    $data = array();
    foreach ($members as $key => $member) {
      list ($type, $id, $params) = Rest::resolve($member);

      if ($callback) {
        $result = call_user_func($callback, $type, $id, $params, $callback_data);
        if (empty($result)) {
          throw Rest_Exception::factory(400, array("members" => "invalid"));
        }
        $data[$key] = $result;
      } else {
        $data[$key] = array($type, $id, $params);
      }
    }

    return $data;
  }

  /**
   * Return an absolute url used for REST resource location.
   * @param  string  resource type (e.g. "item", "tag")
   * @param  mixed   resource id (typically an integer, but can be more complex (e.g. "3,5")
   * @param  array   resource query params (e.g. "data" requires a "size" param)
   * @return string  REST resource url with "sticky" query params carried over as needed
   */
  static function url($type, $id=null, $params=array()) {
    if (is_array($type)) {
      list ($type, $id, $params) = static::_split_triad($type);
    }

    // Carry over the "sticky" params.
    foreach (array("access_key", "num", "type") as $key) {
      $value = Request::current()->query($key);
      if (isset($value)) {
        $params[$key] = $value;
      }
    }

    // Output is only "sticky" if set to html.
    if (Request::current()->query("output") == "html") {
      $params["output"] = "html";
    }

    $url = URL::abs_site("rest/$type");
    $url .= empty($id)     ? "" : "/$id";
    $url .= empty($params) ? "" : URL::query($params, false);
    return $url;
  }

  /**
   * GET a resource's entity.
   * @return  array  entity fields
   */
  static function get_entity($type, $id=null, $params=array()) {
    return static::_call_rest_func("get_entity", $type, $id, $params);
  }

  /**
   * GET a resource's members.
   * @return  array  type/id/params triads
   */
  static function get_members($type, $id=null, $params=array()) {
    return static::_call_rest_func("get_members", $type, $id, $params);
  }

  /**
   * PUT a resource's entity.
   * @return  null
   */
  static function put_entity($type, $id=null, $params=array()) {
    return static::_call_rest_func("put_entity", $type, $id, $params);
  }

  /**
   * PUT a resource's members.
   * @return  null
   */
  static function put_members($type, $id=null, $params=array()) {
    return static::_call_rest_func("put_members", $type, $id, $params);
  }

  /**
   * POST a resource's entity.
   * @return  array  type/id/params/new_flag array (default new_flag is true)
   */
  static function post_entity($type, $id=null, $params=array()) {
    return static::_call_rest_func("post_entity", $type, $id, $params);
  }

  /**
   * POST a resource's members.
   * @return  null
   */
  static function post_members($type, $id=null, $params=array()) {
    return static::_call_rest_func("post_members", $type, $id, $params);
  }

  /**
   * DELETE a resource.
   * @return  null
   */
  static function delete($type, $id=null, $params=array()) {
    return static::_call_rest_func("delete", $type, $id, $params);
  }

  /**
   * Find a resource's relationships.
   * @return  array  type/id/params triads
   */
  static function relationships($type, $id=null, $params=array()) {
    if (is_array($type)) {
      list ($type, $id, $params) = static::_split_triad($type);
    }

    $results = array();
    foreach (static::registry(true) as $resource) {
      $class = "Controller_Rest_$resource";
      if (class_exists($class) && method_exists($class, "relationships")) {
        if ($tmp = call_user_func("$class::relationships", $type, $id, $params)) {
          $results = array_merge($results, $tmp);
        }
      }
    }

    return $results;
  }

  /**
   * GET a resource's output.  This returns an array of the url, entity, members, and
   * relationships of the resource.
   *
   * When building the members and relationship members lists, we maintain the array keys
   * (useful for showing item weights, etc) and the distinction between null and array()
   * (e.g. "comment" members is null, but "comments" with no members is array()).
   */
  static function get_resource($type, $id=null, $params=array()) {
    if (is_array($type)) {
      list ($type, $id, $params) = static::_split_triad($type);
    }

    $results = array();

    $results["url"] = Rest::url($type, $id, $params);

    $data = Rest::get_entity($type, $id, $params);
    if (isset($data)) {
      $results["entity"] = $data;
    }

    $data = Rest::get_members($type, $id, $params);
    if (isset($data)) {
      $results["members"] = array();
      foreach ($data as $key => $member) {
        $results["members"][$key] = Rest::url($member);
      }
    }

    $data = Rest::relationships($type, $id, $params);
    if (isset($data)) {
      foreach ($data as $r_key => $rel) {
        $results["relationships"][$r_key]["url"] = Rest::url($rel);

        $rel_members = Rest::get_members($rel);
        if (isset($rel_members)) {
          $results["relationships"][$r_key]["members"] = array();
          foreach ($rel_members as $key => $member) {
            $results["relationships"][$r_key]["members"][$key] = Rest::url($member);
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

  /**
   * Approve an "Origin" header value.  This checks the REST config and returns an
   * "Access-Control-Allow-Origin" header value if it passes or false if it fails.
   *
   * @param   string  origin
   * @return  mixed   false if failed, string if passed
   * @see  http://www.w3.org/TR/cors
   */
  static function approve_origin($origin) {
    if (empty($origin)) {
      return false;
    }

    switch (Module::get_var("rest", "cors_embedding", "none")) {
    case "all":
      return "*";

    case "list":
      $origin = strtolower($origin);
      foreach (explode(",", Module::get_var("rest", "approved_domains", "")) as $domain) {
        // Check the end of the sent origin against our list.  So, if "example.com" is approved,
        // then "foo.example.com", "http://example.com", and "https://foo.example.com" are also
        // approved, but "badexample.com" is not.
        if ((substr($origin, -strlen($domain)-1) == ".$domain") ||
            (substr($origin, -strlen($domain)-1) == "/$domain") ||
            ($origin == $domain)) {
          return $origin;
        }
      }

    case "none":
    default:
      // No checks if "none" or invalid.
    }

    return false;
  }

  static protected function _call_rest_func($func, $type, $id, $params) {
    if (is_array($type)) {
      list ($type, $id, $params) = static::_split_triad($type);
    }

    $class = "Controller_Rest_" . Inflector::convert_module_to_class_name($type);
    if (!class_exists($class) || !method_exists($class, $func)) {
      return null;
    }

    return call_user_func("$class::$func", $id, $params);
  }

  static protected function _split_triad($triad) {
    return array(Arr::get($triad, 0), Arr::get($triad, 1), Arr::get($triad, 2, array()));
  }
}
