<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
 * Implement the Authentication interface.
 *
 * It was extended to allow configurable drivers
 */
class Auth_Core implements Auth_Driver {

  // Session singleton
  private static $instance;

  // Configuration and driver
  protected $_config;
  protected $_driver;

  /**
   * Singleton instance of Session.
   */
  public static function instance($config = array()) {
    if (self::$instance == NULL) {
      // Create a new instance
      self::$instance = new Auth($config);
    }

    return self::$instance;
  }

  /**
   * On first instance creation, sets up the driver.
   */
  protected function __construct($config = array()) {
    // Load config
    $config += Kohana::config('auth');

    //  Set the driver class name
    $driver = "Auth_{$config['driver']}_Driver";
    if (!Kohana::auto_load($driver)) {
      // @todo change to gallery specific exceptions
      throw new Kohana_Exception("Specified Driver: '{$config['driver']}' has not been defined.");
    }

    // Load the driver
    $driver = new $driver();

    if (!($driver instanceof Auth_Driver)) {
      // @todo change to gallery specific exceptions
      throw new Kohana_Exception(
        "Specified Driver: '{$config['driver']}' has not implemented 'Auth_Driver'.");
    }

    $this->_driver = $driver;
    $this->_config = $config;

    Kohana::log('debug', 'Auth Library initialized');
  }

  /**
   * @see Auth_Driver::set_user_password
   */
  public function set_user_password($user_id, $password) {
    return $this->_driver->set_user_password($user_id, $password);
  }

  /**
   * @see Auth_Driver::is_valid_password
   */
  public function is_valid_password($user_id, $password) {
     return $this->_driver->is_valid_password($user_id, $password);
   }
}
