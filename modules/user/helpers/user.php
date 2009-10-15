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
 * This is the API for handling users.
 *
 * Note: by design, this class does not do any permission checking.
 */
class user_Core {
  /**
   * @see Identity_Driver::guest.
   */
  static function guest() {
    return Identity::guest();
  }

  /**
   * @see Identity_Driver::create_user.
   */
  static function create($name, $full_name, $password) {
    return Identity::create_user($name, $full_name, $password);
  }

  /**
   * @see Identity_Driver::is_correct_password.
   */
  static function is_correct_password($user, $password) {
    return Identity::is_correct_password($user, $password);
  }

  /**
   * @see Identity_Driver::hash_password.
   */
  static function hash_password($password) {
    return Identity::hash_password($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Definition  the user object, or null if the id was invalid.
   */
  static function lookup($id) {
    return self::_lookup_user_by_field("id", $id);
  }

  /**
   * Look up a user by name.
   * @param integer      $name the user name
   * @return User_Definition  the user object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return self::_lookup_user_by_field("name", $name);
  }

  /**
   * Look up a user by hash.
   * @param string       $name the user name
   * @return User_Definition  the user object, or null if the name was invalid.
   */
  static function lookup_by_hash($hash) {
    return self::_lookup_user_by_field("hash", $hash);
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  static function get_user_list($filter=array()) {
    return Identity::get_user_list($filter);
  }

  /**
   * @see Identity_Driver::get_edit_rules.
   */
  static function get_edit_rules() {
    return Identity::get_edit_rules("user");
  }

  private static function _lookup_user_by_field($field_name, $value) {
    try {
      $user = model_cache::get("user", $value, $field_name);
      if ($user->loaded) {
        return $user;
      }
    } catch (Exception $e) {
      if (strpos($e->getMessage(), "MISSING_MODEL") === false) {
       throw $e;
      }
    }
    return null;
  }
}