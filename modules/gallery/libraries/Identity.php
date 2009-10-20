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
  protected static $instance;

  protected static $active;

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
  static function & instance() {
   if (!isset(self::$instance)) {
      // Create a new instance
      self::$instance = new Identity();
    }

    return self::$instance;
  }

  /**
   * Returns a singleton instance of Identity.
   * There can only be one Identity driver configured at a given point
   *
   * @param   string  configuration
   * @return  Identity_Core
   */
  static function reset() {
    self::$instance = new Identity();
  }

  /**
   * Loads the configured driver and validates it.
   *
   * @return  void
   */
  public function __construct() {
    $name = $config = module::get_var("gallery", "identity_provider", "user");

    // Test the config group name
    if (($this->config = Kohana::config("identity.".$config)) === NULL) {
      throw new Exception("@todo NO USER LIBRARY CONFIGURATION FOR: $name");
    }

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
    if ( !($this->driver instanceof Identity_Driver)) {
      throw new Kohana_Exception("core.driver_implements", $this->config["driver"],
                                 get_class($this), "Identity_Driver");
    }

    Kohana::log("debug", "Identity Library initialized");
  }

  /**
   * Return a list of installed Identity Drivers.
   *
   * @return boolean true if the driver supports updates; false if read only
   */
  static function providers() {
    if (empty(self::$active)) {
      $drivers = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
      foreach (module::active() as $module) {
        $module_name = $module->name;
        if (file_exists(MODPATH . "{$module->name}/config/identity.php") &&
            ($info = module::info($module_name))) {
          $drivers->$module_name = $info->description;
        }
      }
      self::$active = $drivers;
    }
    return self::$active;
  }

  /**
   * @see Identity_Driver::activate.
   */
  static function activate() {
    self::instance()->driver->activate();
  }

  /**
   * @see Identity_Driver::deactivate.
   */
  static function deactivate() {
    self::instance()->driver->deactivate();
  }

  /**
   * Determine if if the current driver supports updates.
   *
   * @return boolean true if the driver supports updates; false if read only
   */
  static function is_writable() {
    return !empty(self::instance()->config["allow_updates"]);
  }

  /**
   * @see Identity_Driver::guest.
   */
  static function guest() {
    return self::instance()->driver->guest();
  }

  /**
   * @see Identity_Driver::create_user.
   */
  static function create_user($name, $full_name, $password) {
    return self::instance()->driver->create_user($name, $full_name, $password);
  }

  /**
   * @see Identity_Driver::is_correct_password.
   */
  static function is_correct_password($user, $password) {
    return self::instance()->driver->is_correct_password($user, $password);
  }

  /**
   * @see Identity_Driver::lookup_user.
   */
  static function lookup_user($id) {
    return self::instance()->driver->lookup_user($id);
  }

  /**
   * @see Identity_Driver::lookup_user_by_name.
   */
  static function lookup_user_by_name($name) {
    return self::instance()->driver->lookup_user_by_name($name);
  }

  /**
   * @see Identity_Driver::create_group.
   */
  static function create_group($name) {
    return self::instance()->driver->create_group($name);
  }

  /**
   * @see Identity_Driver::everybody.
   */
  static function everybody() {
    return self::instance()->driver->everybody();
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  static function registered_users() {
    return self::instance()->driver->everybody();
  }

  /**
   * @see Identity_Driver::lookup_group_by_name.
   */
  static function lookup_group_by_name($name) {
    return self::instance()->driver->lookup_group_by_name($name);
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  static function get_user_list($ids) {
    return self::instance()->driver->get_user_list($ids);
  }
} // End Identity
