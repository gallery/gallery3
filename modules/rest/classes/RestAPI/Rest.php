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
class RestAPI_Rest {
  public $type;
  public $id;
  public $params;
  public $members_info;
  public $created = false;

  // Relationships that are defined by this resource.
  // Example: Rest_UserItems::$relationships = array("Users" => "Items")
  public static $relationships = array();

  // Default REST query parameters.  These can be altered as needed in each resource class.
  public $default_params = array(
    "start" => 0,
    "num" => 100,
    "expand_members" => false
  );

  static function factory($type, $id=null, $params=array()) {
    $class = "Rest_$type";
    if (!class_exists($class)) {
      // Controller_Rest::before() catches bad requests - if we get here, it's our fault.
      throw Rest_Exception::factory(500, array("resource_type" => "invalid"));
    }

    return new $class($id, $params);
  }

  public function __construct($id, $params) {
    $this->type = substr(get_class($this), 5);  // strlen("Rest_") --> 5
    $this->id = $id;
    $this->params = $params;
    $this->members_info = array(
      "count" => null,
      "num"   => Arr::get($params, "num",   $this->default_params["num"]),
      "start" => Arr::get($params, "start", $this->default_params["start"]));
  }

  /**
   * The GET response for a typical REST resource.  This returns an array of the url, entity,
   * members, and relationships of the resource, and expands members as needed.  This is
   * called by Controller_Rest::action_get().
   *
   * When building the members and relationship members lists, we maintain the array keys
   * (useful for showing item weights, etc) and the distinction between null and array()
   * (e.g. "comments/1" members is null, but "comments" with no members is array()).
   *
   * While most resources can use this function without modification, it may be useful to
   * override it for atypical resources (e.g. Rest_Data, which returns a file's contents), or
   * simply overload it to pre-process query params (e.g. random param for Rest_Items).
   */
  public function get_response() {
    $entity  = method_exists($this, "get_entity")  ? $this->get_entity()  : null;
    $members = method_exists($this, "get_members") ? $this->get_members() : null;

    // If we're in "expand_members" mode, loop through and get the response of each member.
    $results = array();
    if (Arr::get($this->params, "expand_members", $this->default_params["expand_members"])) {
      if (!isset($members)) {
        // A null members array means the resource is not a collection - fire a 400 Bad Request.
        throw Rest_Exception::factory(400, array("expand_members" => "not_a_collection"));
      }

      foreach ($members as $key => $member) {
        // Ensure "expand_members" isn't set in the members, or else we could recurse forever...
        unset($member->params["expand_members"]);
        $results[$key] = $member->get_response();
      }
    } else {
      if (!isset($entity) && !isset($members)) {
        return null;
      }

      $results["url"] = $this->url();

      if (isset($entity)) {
        $results["entity"] = $entity;
      }

      if (isset($members)) {
        $results["members"] = array();
        foreach ($members as $key => $member) {
          $results["members"][$key] = $member->url();
        }
        if (isset($this->members_info["count"])) {
          $results["members_info"] = $this->members_info;
        }
      }

      foreach ($this->relationships() as $type => $relationship) {
        $type = Inflector::convert_class_to_module_name($type);
        $results["relationships"][$type] = $relationship->get_response();
      }
    }

    return $results;
  }

  /**
   * The POST response for a typical REST resource.  This returns an array with the url.
   */
  public function post_response() {
    return array("url" => $this->url(false));
  }

  /**
   * The PUT and DELETE responses for a typical REST resource, which are empty.
   */
  public function put_response() {
    return array();
  }
  public function delete_response() {
    return array();
  }

  /**
   * Return REST resource's absolute URL, with "sticky" query params carried over as needed.
   * @return  string  REST URL
   */
  public function url($keep_params=true) {
    $params = $keep_params ? $this->params : array();

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

    $url = URL::abs_site("rest/" . Inflector::convert_class_to_module_name($this->type));
    $url .= empty($this->id) ? "" : "/{$this->id}";
    $url .= empty($params)   ? "" : URL::query($params, false);
    return $url;
  }

  /**
   * Find a resource's relationships.  These only exist if we have an id defined.
   * @return  array  related REST objects
   */
  public function relationships() {
    $results = array();

    if ($this->id) {
      foreach (RestAPI::registry(true) as $resource) {
        $class = "Rest_$resource";
        $data = $class::$relationships;
        if (!empty($data[$this->type])) {
          $results[$data[$this->type]] = Rest::factory($resource, $this->id);
        }
      }
    }

    return $results;
  }
}
