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

/**
 * Provides a driver-based interface for managing users and groups.
 */
class IdentityProvider_Core {
  protected static $instance;

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
  static function &instance() {
   if (empty(self::$instance)) {
      // Create a new instance
      self::$instance = new IdentityProvider();
    }

    return self::$instance;
  }

  /**
   * Frees the current instance of the identity provider so the next call to instance will reload
   *
   * @param   string  configuration
   * @return  Identity_Core
   */
  static function reset() {
    self::$instance = null;
    Kohana_Config::instance()->clear("identity");
  }

  /**
   * Return a commen confirmation message
   */
  static function confirmation_message() {
    return t("Are you sure you want to change your Identity Provider? Continuing will delete all existing users.");
  }

  static function change_provider($new_provider) {
    if (!identity::active_user()->admin && PHP_SAPI != "cli") {
      // Below, the active user is set to the primary admin.
      access::forbidden();
    }

    $current_provider = module::get_var("gallery", "identity_provider");
    if (!empty($current_provider)) {
      module::uninstall($current_provider);
    }

    try {
      IdentityProvider::reset();
      $provider = new IdentityProvider($new_provider);

      module::set_var("gallery", "identity_provider", $new_provider);

      if (class_exists("{$new_provider}_installer") &&
          method_exists("{$new_provider}_installer", "initialize")) {
        call_user_func("{$new_provider}_installer::initialize");
      }

      if (!$provider->admin_user()) {
        throw new Exception("IdentityProvider $new_provider: Couldn't find the admin user!");
      }

      module::event("identity_provider_changed", $current_provider, $new_provider);

      identity::set_active_user($provider->admin_user());
      Session::instance()->regenerate();
    } catch (Exception $e) {
      static $restore_already_running;

      // In case of error, make an attempt to restore the old provider.  Since that's calling into
      // this function again and can fail, we should be sure not to get into an infinite recursion.
      if (!$restore_already_running) {
        $restore_already_running = true;

        // Make sure new provider is not in the database
        try {
          module::uninstall($new_provider);
        } catch (Exception $e2) {
          Kohana_Log::add("error", "Error uninstalling failed new provider\n" .
                          $e2->getMessage() . "\n" . $e2->getTraceAsString());
        }

        try {
          // Lets reset to the current provider so that the gallery installation is still
          // working.
          module::set_var("gallery", "identity_provider", null);
          IdentityProvider::change_provider($current_provider);
          module::activate($current_provider);
        } catch (Exception $e2) {
          Kohana_Log::add("error", "Error restoring original identity provider\n" .
                          $e2->getMessage() . "\n" . $e2->getTraceAsString());
        }

        message::error(
          t("Error attempting to enable \"%new_provider\" identity provider, reverted to \"%old_provider\" identity provider",
            array("new_provider" => $new_provider, "old_provider" => $current_provider)));

        $restore_already_running = false;
      }
      throw $e;
    }
  }

  /**
   * Loads the configured driver and validates it.
   *
   * @return  void
   */
  public function __construct($config=null) {
    if (empty($config)) {
      $config = module::get_var("gallery", "identity_provider", "user");
    }

    // Test the config group name
    if (($this->config = Kohana::config("identity." . $config)) === NULL) {
      throw new Exception("@todo NO_USER_LIBRARY_CONFIGURATION_FOR: $config");
    }

    // Set driver name
    $driver = "IdentityProvider_" . ucfirst($this->config["driver"])  ."_Driver";

    // Load the driver
    if ( ! Kohana::auto_load($driver)) {
      throw new Kohana_Exception("core.driver_not_found", $this->config["driver"],
                                 get_class($this));
    }

    // Initialize the driver
    $this->driver = new $driver($this->config["params"]);

    // Validate the driver
    if ( !($this->driver instanceof IdentityProvider_Driver)) {
      throw new Kohana_Exception("core.driver_implements", $this->config["driver"],
                                 get_class($this), "IdentityProvider_Driver");
    }

    Kohana_Log::add("debug", "Identity Library initialized");
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
   * @see IdentityProvider_Driver::guest.
   */
  public function guest() {
    return $this->driver->guest();
  }

  /**
   * @see IdentityProvider_Driver::admin_user.
   */
  public function admin_user() {
    return $this->driver->admin_user();
  }

  /**
   * @see IdentityProvider_Driver::create_user.
   */
  public function create_user($name, $full_name, $password, $email) {
    return $this->driver->create_user($name, $full_name, $password, $email);
  }

  /**
   * @see IdentityProvider_Driver::is_correct_password.
   */
  public function is_correct_password($user, $password) {
    return $this->driver->is_correct_password($user, $password);
  }

  /**
   * @see IdentityProvider_Driver::lookup_user.
   */
  public function lookup_user($id) {
    return $this->driver->lookup_user($id);
  }

  /**
   * @see IdentityProvider_Driver::lookup_user_by_name.
   */
  public function lookup_user_by_name($name) {
    return $this->driver->lookup_user_by_name($name);
  }

  /**
   * @see IdentityProvider_Driver::create_group.
   */
  public function create_group($name) {
    return $this->driver->create_group($name);
  }

  /**
   * @see IdentityProvider_Driver::everybody.
   */
  public function everybody() {
    return $this->driver->everybody();
  }

  /**
   * @see IdentityProvider_Driver::registered_users.
   */
  public function registered_users() {
    return $this->driver->registered_users();
  }

  /**
   * @see IdentityProvider_Driver::lookup_group.
   */
  public function lookup_group($id) {
    return $this->driver->lookup_group($id);
  }

  /**
   * @see IdentityProvider_Driver::lookup_group_by_name.
   */
  public function lookup_group_by_name($name) {
    return $this->driver->lookup_group_by_name($name);
  }

  /**
   * @see IdentityProvider_Driver::get_user_list.
   */
  public function get_user_list($ids) {
    return $this->driver->get_user_list($ids);
  }

  /**
   * @see IdentityProvider_Driver::groups.
   */
  public function groups() {
    return $this->driver->groups();
  }

  /**
   * @see IdentityProvider_Driver::add_user_to_group.
   */
  public function add_user_to_group($user, $group) {
    return $this->driver->add_user_to_group($user, $group);
  }

  /**
   * @see IdentityProvider_Driver::remove_user_to_group.
   */
  public function remove_user_from_group($user, $group) {
    return $this->driver->remove_user_from_group($user, $group);
  }
} // End Identity
