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

class identity_Core {
  protected static $available;

  /**
   * Return a list of installed Identity Drivers.
   *
   * @return boolean true if the driver supports updates; false if read only
   */
  static function providers() {
    if (empty(self::$available)) {
      $drivers = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
      foreach (module::available() as $module_name => $module) {
        if (file_exists(MODPATH . "{$module_name}/config/identity.php")) {
          $drivers->$module_name = $module->description;
        }
      }
      self::$available = $drivers;
    }
    return self::$available;
  }

  /**
   * Frees the current instance of the identity provider so the next call to instance will reload
   *
   * @param   string  configuration
   * @return  Identity_Core
   */
  static function reset() {
    IdentityProvider::reset();
  }

  /**
   * Make sure that we have a session and group_ids cached in the session.
   */
  static function load_user() {
    try {
      // Call IdentityProvider::instance() now to force the load of the user interface classes.
      // We are about to load the active user from the session and which needs the user definition
      // class, which can't be reached by Kohana's heiracrchical lookup.
      IdentityProvider::instance();

      $session = Session::instance();
      if (!($user = $session->get("user"))) {
        identity::set_active_user($user = identity::guest());
      }

      // The installer cannot set a user into the session, so it just sets an id which we should
      // upconvert into a user.
      // @todo set the user name into the session instead of 2 and then use it to get the
      //       user object
      if ($user === 2) {
        $session->delete("user");  // delete it so that identity code isn't confused by the integer
        auth::login(IdentityProvider::instance()->admin_user());
      }

      // Cache the group ids for a day to trade off performance for security updates.
      if (!$session->get("group_ids") || $session->get("group_ids_timeout", 0) < time()) {
        $ids = array();
        foreach ($user->groups() as $group) {
          $ids[] = $group->id;
        }
        $session->set("group_ids", $ids);
        $session->set("group_ids_timeout", time() + 86400);
      }
    } catch (Exception $e) {
      // Log it, so we at least have so notification that we swallowed the exception.
      Kohana_Log::add("error", "load_user Exception: " .
                      $e->getMessage() . "\n" . $e->getTraceAsString());
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        // We don't care if there was a problem destroying the session.
      }
      url::redirect(item::root()->abs_url());
    }
  }

  /**
   * Return the array of group ids this user belongs to
   *
   * @return array
   */
  static function group_ids_for_active_user() {
    return Session::instance()->get("group_ids", array(1));
  }

  /**
   * Return the active user.  If there's no active user, return the guest user.
   *
   * @return User_Definition
   */
  static function active_user() {
    // @todo (maybe) cache this object so we're not always doing session lookups.
    $user = Session::instance()->get("user", null);
    if (!isset($user)) {
      // Don't do this as a fallback in the Session::get() call because it can trigger unnecessary
      // work.
      $user = identity::guest();
    }
    return $user;
  }

  /**
   * Change the active user.
   * @param User_Definition $user
   */
  static function set_active_user($user) {
    $session = Session::instance();
    $session->set("user", $user);
    $session->delete("group_ids");
    identity::load_user();
  }

  /**
   * Determine if if the current driver supports updates.
   *
   * @return boolean true if the driver supports updates; false if read only
   */
  static function is_writable() {
    return IdentityProvider::instance()->is_writable();
  }

  /**
   * @see IdentityProvider_Driver::guest.
   */
  static function guest() {
    return IdentityProvider::instance()->guest();
  }

  /**
   * @see IdentityProvider_Driver::admin_user.
   */
  static function admin_user() {
    return IdentityProvider::instance()->admin_user();
  }

  /**
   * @see IdentityProvider_Driver::create_user.
   */
  static function create_user($name, $full_name, $password, $email) {
    return IdentityProvider::instance()->create_user($name, $full_name, $password, $email);
  }

  /**
   * @see IdentityProvider_Driver::is_correct_password.
   */
  static function is_correct_password($user, $password) {
    return IdentityProvider::instance()->is_correct_password($user, $password);
  }

  /**
   * @see IdentityProvider_Driver::lookup_user.
   */
  static function lookup_user($id) {
    return IdentityProvider::instance()->lookup_user($id);
  }

  /**
   * @see IdentityProvider_Driver::lookup_user_by_name.
   */
  static function lookup_user_by_name($name) {
    return IdentityProvider::instance()->lookup_user_by_name($name);
  }

  /**
   * @see IdentityProvider_Driver::create_group.
   */
  static function create_group($name) {
    return IdentityProvider::instance()->create_group($name);
  }

  /**
   * @see IdentityProvider_Driver::everybody.
   */
  static function everybody() {
    return IdentityProvider::instance()->everybody();
  }

  /**
   * @see IdentityProvider_Driver::registered_users.
   */
  static function registered_users() {
    return IdentityProvider::instance()->registered_users();
  }

  /**
   * @see IdentityProvider_Driver::lookup_group.
   */
  static function lookup_group($id) {
    return IdentityProvider::instance()->lookup_group($id);
  }

  /**
   * @see IdentityProvider_Driver::lookup_group_by_name.
   */
  static function lookup_group_by_name($name) {
    return IdentityProvider::instance()->lookup_group_by_name($name);
  }

  /**
   * @see IdentityProvider_Driver::get_user_list.
   */
  static function get_user_list($ids) {
    return IdentityProvider::instance()->get_user_list($ids);
  }

  /**
   * @see IdentityProvider_Driver::groups.
   */
  static function groups() {
    return IdentityProvider::instance()->groups();
  }

  /**
   * @see IdentityProvider_Driver::add_user_to_group.
   */
  static function add_user_to_group($user, $group) {
    return IdentityProvider::instance()->add_user_to_group($user, $group);
  }

  /**
   * @see IdentityProvider_Driver::remove_user_to_group.
   */
  static function remove_user_from_group($user, $group) {
    return IdentityProvider::instance()->remove_user_from_group($user, $group);
  }
}