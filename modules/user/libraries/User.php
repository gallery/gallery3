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
 * This is the generalized wrapper to provide user management.  The actual user management 
 * fucntionality is implemented by a driver module.  This will insulate gallery3 user management
 * from various CMS implementations.
 *
 */
class User_Core implements User_Driver {
  
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
      self::$instance = new User($config);
    }

    return self::$instance;
  }

  /**
   * On first instance creation, sets up the driver.
   */
  protected function __construct($config = array()) {
    // Load config
    $config += Kohana::config('user');

    //  Set the driver class name
    $driver = "User_{$config['driver']}_Driver";
    if (!Kohana::auto_load($driver)) {
      // @todo change to gallery specific exceptions
      throw new Exception("@todo DRIVER_NOT_DEFINED {$config['driver']}");
    }

    // Load the driver
    $driver = new $driver();

    if (!($driver instanceof User_Driver)) {
      // @todo change to gallery specific exceptions
      throw new Exception(
        "@todo User_Driver_INTERFACE_NOT_IMPLEMENTED: {$config['driver']}");
    }

    $this->_driver = $driver;
    $this->_config = $config;

    Kohana::log('debug', 'Auth Library initialized');
  }

  /**
   * @see User_Driver::install
   */
  public function install() {
    $this->_driver->install();

    $user_module = ORM::factory("module")->where("name", "user")->find();
    $user_module->name = "user";
    $user_module->version = 1;
    $user_module->save();

    $user = $this->_driver->get_user_by_name("admin");
    if (!$user->loaded) {
      $user = $this->_driver->create_user("admin", "admin", "admin");
    }

    foreach (array("administrator", "registered") as $group_name) {
      $group = $user = $this->_driver->get_group_by_name($group_name);
      if (!$group->loaded) {
        $group = $this->_driver->create_group($group_name);
        // Don't assume we can use ORM relationship to join groups and users. Use the interface.
        $this->_driver->add_user_to_group($group->id, $user->id);
      }
    }

    $db = Database::instance();
    $db->query("UPDATE `items` SET `owner_id` = {$user->id} WHERE `owner_id` IS NULL");
  }

  /**
   * @see User_Driver::uninstall
   */
  public function uninstall() {
    $this->_driver->uninstall();
    ORM::factory("module")->where("name", "user")->find()->delete();
  }

  /**
   * @see User_Driver::install
   */
  public function create_user($name, $display_name, $password, $email=null) {
    $this->_driver->create_user($name, $display_name, $password, $email);
  }

  /**
   * @see User_Driver::update_user
   */
  public function update_user($id, $name, $display_name, $password, $email=null) {
    $this->_driver->update_user($id, $name, $display_name, $password, $email);
  }
  
  /**
   * @see User_Driver::get_user
   */
  public function get_user($id) {
    $this->_driver->get_user($id);
  }

  /**
   * @see User_Driver::get_user_by_name
   */
  public function get_user_by_name($name) {
    $this->_driver->get_user_by_name($name);
  }

  /**
   * @see User_Driver::delete_user
   */
  public function delete_user($id) {
    $this->_driver->delete_user($id);
  }

  /**
   * @see User_Driver::create_group
   */
  public function create_group($group_name) {
    $this->_driver->create_group($group_name);
  }

  /**
   * @see User_Driver::rename_group
   */
  public function rename_group($id, $new_name) {
    $this->_driver->rename_group($id, $new_name);
  }

  /**
   * @see User_Driver::get_group
   */
  public function get_group($id) {
    $this->_driver->get_group($id);
  }

  /**
   * @see User_Driver::get_group_by_name
   */
  public function get_group_by_name($group_name) {
    $this->_driver->get_group_by_name($group_name);
  }

  /**
   * @see User_Driver::delete_group
   */
  public function delete_group($id) {
    $this->_driver->delete_group($id);
  }

  /**
   * @see User_Driver::add_user_to_group
   */
  public function add_user_to_group($group_id, $user_id) {
    $this->_driver->add_user_to_group($group_id, $user_id);
  }

  /**
   * @see User_Driver::remove_user_from_group
   */
  public function remove_user_from_group($group_id, $user_id) {
   $this->_driver->remove_user_from_group($group_id, $user_id);
  }
}