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
abstract class Gallery_Controller extends Kohana_Controller {
  // Set the defaults as false (which can be overridden by other controllers)
  public $allow_maintenance_mode = false;
  public $allow_private_gallery = false;

  /**
   * This is run to initialize Gallery before executing the controller action.
   */
  public function before() {
    parent::before();

    // Restrict all response frames to the same origin for security
    $this->response->headers("X-Frame-Options", "SAMEORIGIN");

    // Initialize the modules (will run "gallery_ready" event)
    if ($this->request->is_initial()) {
      Gallery::ready();
    }

    // Check if we should be allowed to run this controller if in maintenance or private mode.
    Gallery::maintenance_mode($this->allow_maintenance_mode);
    Gallery::private_gallery($this->allow_private_gallery);
  }

  /**
   * Retrieves a value from the route args.  This is an alias of $this->request->arg().
   *
   *   $args = $this->arg();          // Returns all args
   *   $id   = $this->arg(0);         // Returns 0th arg
   *   $type = $this->arg(1, "item"); // Returns 1st arg, defaults to "item" if not set
   *
   * @param   mixed  $key      Key of the value (string or int)
   * @param   mixed  $default  Default value if the key is not set (optional)
   * @return  mixed
   */
  public function arg($key=null, $default=null) {
    return $this->request->arg($key, $default);
  }

  /**
   * Retrieves a value from the route args, and throws an HTTP 400 Bad Request error if not found.
   * Optionally, a rule argument can be specified that sets further restrictions on the value and
   * throws an HTTP 400 Bad Request if invalid.
   *
   *   $arg0 = $this->arg(0);               // must be defined (no further restrictions)
   *   $id   = $this->arg(1, "digit");      // must be [0-9]; useful for ids
   *   $type = $this->arg(2, "alpha");      // must be [A-Za-z]; useful for types
   *                                        // (e.g. movie, photo, item, group, tag,...)
   *   $name = $this->arg(3, "alpha_dash"); // must be [A-Za-z0-9-_]; useful for module-like names
   *                                        // (e.g. server_add, items_tag, admin_wind,...)
   *
   * Note that the names "digit", "alpha", and "alpha_dash" are similar to their like-named
   * functions in the Valid class, except that the filters here are more restrictive in that they
   * do not support UTF-8 and are locale-invariant (e.g. Valid::alpha() could pass "âçcéñts").
   *
   * @param   mixed  $key   Key of the value (string or int)
   * @param   string $rule  Name Default value if the key is not set
   * @return  mixed
   */
  public function arg_required($key, $rule=null) {
    $value = $this->request->arg($key);
    if (is_null($value) ||
        (($rule == "digit")      && preg_match("/[^0-9]/", $value)) ||
        (($rule == "alpha")      && preg_match("/[^A-Za-z]/", $value)) ||
        (($rule == "alpha_dash") && preg_match("/[^A-Za-z0-9-_]/", $value))) {
      throw HTTP_Exception::factory(400);
    }

    return $value;
  }
}
