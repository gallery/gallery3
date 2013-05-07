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
class Gallery_Request extends Kohana_Request {
  protected $_args;

  /**
   * Retrieves a value from the route args, and throws an HTTP 400 Bad Request error if not found.
   * Optionally, a rule argument can be specified that sets additional restrictions on the value and
   * throws an HTTP 400 Bad Request if invalid.
   *
   *   $args = $request->arg();                // returns all args (at least 1 must be defined)
   *   $arg0 = $request->arg(0);               // returns 0th arg (must be defined)
   *   $id   = $request->arg(1, "digit");      // returns 1st arg (must be defined and [0-9])
   *                                           // useful for ids
   *   $type = $request->arg(2, "alpha");      // returns 2nd arg (must be defined and [A-Za-z])
   *                                           // useful for types (photo, item, group, tag,...)
   *   $name = $request->arg(3, "alpha_dash"); // returns 3rd arg (must be defined and [A-Za-z0-9-_])
   *                                           // useful for names (server_add, items_tag, admin_wind,...)
   *
   * Note that the names "digit", "alpha", and "alpha_dash" are similar to their like-named
   * functions in the Valid class, except that the filters here are more restrictive in that they
   * do not support UTF-8 and are locale-invariant (e.g. Valid::alpha() could pass "âçcéñts").
   *
   * @param   mixed   $key   Key of the value (optional - string or int)
   * @param   string  $rule  Rule (optional - "digit", "alpha", or "alpha_dash")
   * @return  mixed
   */
  public function arg($key=null, $rule=null) {
    $this->_init_args();

    if (is_null($key)) {
      if (empty($this->_args)) {
        throw HTTP_Exception::factory(400);
      }
      return $this->_args;
    }

    $value = Arr::get($this->_args, $key);
    if (is_null($value) ||
        (($rule == "digit")      && preg_match("/[^0-9]/", $value)) ||
        (($rule == "alpha")      && preg_match("/[^A-Za-z]/", $value)) ||
        (($rule == "alpha_dash") && preg_match("/[^A-Za-z0-9-_]/", $value))) {
      throw HTTP_Exception::factory(400);
    }

    return $value;
  }

  /**
   * Retrieves a value from the route args that is optional.  If not found, it will return the
   * default value (instead of a HTTP 400 Bad Request error as with arg()).  This follows a
   * syntax similar to Request::param().
   *
   *   $args = $request->arg_optional();         // returns all args
   *   $opt1 = $request->arg_optional(0);        // returns 0th arg value or null if not found
   *   $opt2 = $request->arg_optional(1, "foo"); // returns 1st arg value or "foo" if not found
   *
   * @param   mixed  $key      Key of the value (optional - string or int)
   * @param   mixed  $default  Default value if the arg is not found (optional)
   * @return  mixed
   */
  public function arg_optional($key=null, $default=null) {
    $this->_init_args();

    if (is_null($key)) {
      return $this->_args;
    }

    return Arr::get($this->_args, $key, $default);
  }

  /**
   * Make the current request an ajax request.  This sets Request::requested_with() such that
   * Request::is_ajax() will return true.  We use this in some sub-requests.
   * @see  Request::is_ajax()
   */
  public function make_ajax() {
    return $this->requested_with("xmlhttprequest");
  }

  /**
   * Initialize the args array if not already set or if the force argument is specified.
   * This pulls it from the param array and parses it.
   */
  protected function _init_args($force=false) {
    if (!isset($this->_args) || $force) {
      $this->_args = preg_replace("|/+|", "/", trim($this->param("args", ""), "/"));
      if ($this->_args === "") {
        $this->_args = array();
      } else {
        $this->_args = explode("/", $this->_args);
      }
    }
  }

  /**
   * Overload Request::__construct() to clean our URI and protect against XSS.  This happens after
   * Request::factory() has autodetected the URI (if not specified) and removed the base URL and
   * index (i.e. it's fed something like "my_controller/my_action?q=123"), but before our routes,
   * params, and args are determined, so everything we do downstream is safe.  We make no attempt
   * to be careful with "&" since Request::__construct() removes any query params before use.
   *
   * @see Request::__construct() and Request::factory()
   */
  public function __construct($uri, $client_params=array(), $allow_external=true, $injected_routes=array()) {
    $uri = Purifier::clean_html($uri);
    parent::__construct($uri, $client_params, $allow_external, $injected_routes);
  }
}
