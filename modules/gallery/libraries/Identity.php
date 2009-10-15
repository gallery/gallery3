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

/**
 * Provides a driver-based interface for managing users and groups.
 */
class Identity_Core {
  protected static $instances;

  // Configuration
  protected $config;

  // Driver object
  protected $driver;

  /**
   * Returns a singleton instance of Identity.
   * There can only be one Identity driver configured at a given point
   *
   * @param   string  configuration
   * @return  Identity_Core
   */
  static function & instance($config="default") {
   if (!isset(Identity::$instances)) {
      // Create a new instance
      Identity::$instances = new Identity($config);
    }

    return Identity::$instances;
  }

  /**
   * Loads the configured driver and validates it.
   *
   * @param   array|string  custom configuration or config group name
   * @return  void
   */
  public function __construct($config="default") {
    if (is_string($config)) {
      $name = $config;

      // Test the config group name
      if (($config = Kohana::config("identity.".$config)) === NULL) {
        throw new Exception("@todo NO USER LIBRARY CONFIGURATION FOR: $name");
      }

      if (is_array($config)) {
        // Append the default configuration options
        $config += Kohana::config("identity.default");
      } else {
        // Load the default group
        $config = Kohana::config("identity.default");
      }

      // Cache the config in the object
      $this->config = $config;

      // Set driver name
      $driver = "Identity_".ucfirst($this->config["driver"])."_Driver";

      // Load the driver
      if ( ! Kohana::auto_load($driver)) {
        throw new Kohana_Exception("core.driver_not_found", $this->config["driver"],
                                   get_class($this));
      }

      // Initialize the driver
      $this->driver = new $driver($this->config["params"]);

      // Validate the driver
      if ( !($this->driver instanceof Identity_Driver))
        throw new Kohana_Exception("core.driver_implements", $this->config["driver"],
                                   get_class($this), "Identity_Driver");

      Kohana::log("debug", "Identity Library initialized");
    }
  }

  /**
   * Determine if if the current driver supports updates.
   *
   * @return boolean true if the driver supports updates; false if read only
   */
  public function is_writable() {
    return !empty($this->config["allow_updates"]);
  }

  /**
   * @see Identity_Driver::guest.
   */
  public function guest() {
    return $this->driver->guest();
  }

  /**
   * @see Identity_Driver::create_user.
   */
  public function create_user($name, $full_name, $password) {
    return $this->driver->create_user($name, $full_name, $password);
  }

  /**
   * @see Identity_Driver::is_correct_password.
   */
  public function is_correct_password($user, $password) {
    return $this->driver->is_correct_password($user, $password);
  }

  /**
   * @see Identity_Driver::hash_password.
   */
  public function hash_password($password) {
    return $this->driver->hash_password($password);
  }

  /**
   * @see Identity_Driver::lookup_user_by_field.
   */
  public function lookup_user_by_field($field_name, $value) {
    return $this->driver->lookup_user_by_field($field_name, $value);
  }

  /**
   * @see Identity_Driver::create_group.
   */
  public function create_group($name) {
    return $this->driver->create_group($name);
  }

  /**
   * @see Identity_Driver::everybody.
   */
  public function everybody() {
    return $this->driver->everybody();
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  public function registered_users() {
    return $this->driver->everybody();
  }

  /**
   * @see Identity_Driver::lookup_group_by_field.
   */
  public function lookup_group_by_field($field_name, $value) {
    return $this->driver->lookup_group_by_field($field_name, $value);
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  public function get_user_list($filter=array()) {
    return $this->driver->get_user_list($filter);
  }

  /**
   * @see Identity_Driver::get_group_list.
   */
  public function get_group_list($filter=array()) {
    return $this->driver->get_group_list($filter);
  }

  /**
   * @see Identity_Driver::get_edit_rules.
   */
  public function get_edit_rules($object_type) {
    return $this->driver->get_edit_rules($object_type);
  }
} // End Identity
