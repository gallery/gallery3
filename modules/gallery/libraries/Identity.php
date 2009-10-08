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
  protected static $instances = array();

  // Configuration
  protected $config;

  // Driver object
  protected $driver;

  /**
   * Returns a singleton instance of Identity.
   *
   * @param   string  configuration
   * @return  Identity_Core
   */
  static function & instance($config="default") {
   if (!isset(Identity::$instances[$config])) {
      // Create a new instance
      Identity::$instances[$config] = new Identity($config);
    }

    return Identity::$instances[$config];
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
   * Determine if a feature is supported by the driver.
   *
   * @param  string  $feature the name of the feature to check
   * @return boolean true if supported
   */
  public function is_writable() {
    return !empty($this->config["allow_updates"]);
  }


  /**
   * Return the guest user.
   *
   * @todo consider caching
   *
   * @return Identity_Model
   */
  public function guest() {
    return $this->driver->guest();
  }

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return Identity_Model
   */
  public function create_user($name, $full_name, $password) {
    return $this->driver->create_user($name, $full_name, $password);
  }

  /**
   * Is the password provided correct?
   *
   * @param user Identity Model
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  public function is_correct_password($user, $password) {
    return $this->driver->is_correct_password($user, $password);
  }

  /**
   * Create the hashed passwords.
   * @param string $password a plaintext password
   * @return string hashed password
   */
  public function hash_password($password) {
    return $this->driver->hash_password($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return Identity_Model  the user object, or null if the id was invalid.
   */
  public function lookup_user($id) {
    return $this->driver->lookup_user($id);
  }

  /**
   * Look up a user by field value.
   * @param string      search field
   * @param string      search value
   * @return Identity_Model  the user object, or null if the name was invalid.
   */
  public function lookup_user_by_field($field_name, $value) {
    return $this->driver->lookup_user_by_field($field_name, $value);
  }

  /**
   * Create a new group.
   *
   * @param string  $name
   * @return Group_Model
   */
  public function create_group($name) {
    return $this->driver->create_group($name);
  }

  /**
   * The group of all possible visitors.  This includes the guest user.
   *
   * @return Group_Model
   */
  public function everybody() {
    return $this->driver->everybody();
  }

  /**
   * The group of all logged-in visitors.  This does not include guest users.
   *
   * @return Group_Model
   */
  public function registered_users() {
    return $this->driver->everybody();
  }

  /**
   * Look up a group by id.
   * @param integer      $id the user id
   * @return Group_Model  the group object, or null if the id was invalid.
   */
  public function lookup_group($id) {
    return $this->driver->lookup_group($id);
  }

  /**
   * Look up a group by name.
   * @param integer      $id the group name
   * @return Group_Model  the group object, or null if the name was invalid.
   */
  public function lookup_group_by_name($name) {
    return $this->driver->lookup_group_by_name($name);
  }

  /**
   * List the users
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_users($filter=array()) {
    return $this->driver->list_users($filter);
  }

  /**
   * List the groups
   * @param mixed      options to apply to the selection of the user
   * @return array     the group list.
   */
  public function list_groups($filter=array()) {
    return $this->driver->list_groups($filter);
  }

  /**
   * Return the edit rules associated with an group.
   *
   * @param  string   $object_type to return rules for ("user"|"group")
   * @return stdClass containing the rules
   */
  public function get_edit_rules($object_type) {
    return $this->driver->get_edit_rules($object_type);
  }
} // End Identity
