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
 * This is the API for handling users.
 *
 * Note: by design, this class does not do any permission checking.
 */
class user_Core {
  /**
   * Return the guest user.
   *
   * @todo consider caching
   *
   * @return User_Model
   */
  static function guest() {
    return model_cache::get("user", 1);
  }

  /**
   * Return an admin user.  Prefer the currently logged in user, if possible.
   *
   * @return User_Model
   */
  static function admin_user() {
    $active = identity::active_user();
    if ($active->admin) {
      return $active;
    }

    return ORM::factory("user")->where("admin", "=", 1)->order_by("id", "ASC")->find();
  }

  /**
   * Is the password provided correct?
   *
   * @param user User Model
   * @param string $password a plaintext password
   * @return boolean true if the password is correct
   */
  static function is_correct_password($user, $password) {
    $valid = $user->password;

    // Try phpass first, since that's what we generate.
    if (strlen($valid) == 34) {
      require_once(MODPATH . "user/lib/PasswordHash.php");
      $hashGenerator = new PasswordHash(10, true);
      return $hashGenerator->CheckPassword($password, $valid);
    }

    $salt = substr($valid, 0, 4);
    // Support both old (G1 thru 1.4.0; G2 thru alpha-4) and new password schemes:
    $guess = (strlen($valid) == 32) ? md5($password) : ($salt . md5($salt . $password));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    // Passwords with <&"> created by G2 prior to 2.1 were hashed with entities
    $sanitizedPassword = html::chars($password, false);
    $guess = (strlen($valid) == 32) ? md5($sanitizedPassword)
          : ($salt . md5($salt . $sanitizedPassword));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    return false;
  }

  static function valid_password($password_input) {
    if (!user::is_correct_password(identity::active_user(), $password_input->value)) {
      $password_input->add_error("invalid_password", 1);
    }
  }

  static function valid_username($text_input) {
    if (!self::lookup_by_name($text_input->value)) {
      $text_input->add_error("invalid_username", 1);
    }
  }

  /**
   * Create the hashed passwords.
   * @param string $password a plaintext password
   * @return string hashed password
   */
  static function hash_password($password) {
    require_once(MODPATH . "user/lib/PasswordHash.php");
    $hashGenerator = new PasswordHash(10, true);
    return $hashGenerator->HashPassword($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Model  the user object, or null if the id was invalid.
   */
  static function lookup($id) {
    return self::_lookup_user_by_field("id", $id);
  }

  /**
   * Look up a user by name.
   * @param integer      $name the user name
   * @return User_Model  the user object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return self::_lookup_user_by_field("name", $name);
  }

  /**
   * Look up a user by hash.
   * @param integer      $hash the user hash value
   * @return User_Model  the user object, or null if the name was invalid.
   */
  static function lookup_by_hash($hash) {
    return self::_lookup_user_by_field("hash", $hash);
  }

  /**
   * List the users
   * @param mixed      filters (@see Database.php
   * @return array     the user list.
   */
  static function get_user_list($filter=array()) {
    $user = ORM::factory("user");

    foreach($filter as $method => $args) {
      switch ($method) {
      case "in":
        $user->in($args[0], $args[1]);
        break;
      default:
        $user->$method($args);
      }
    }
    return $user->find_all();
  }

  /**
   * Look up a user by field value.
   * @param string      search field
   * @param string      search value
   * @return User_Core  the user object, or null if the name was invalid.
   */
  private static function _lookup_user_by_field($field_name, $value) {
    try {
      $user = model_cache::get("user", $value, $field_name);
      if ($user->loaded()) {
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