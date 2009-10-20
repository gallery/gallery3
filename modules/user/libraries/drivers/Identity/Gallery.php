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
/*
 * Based on the Cache_Sqlite_Driver developed by the Kohana Team
 */
class Identity_Gallery_Driver implements Identity_Driver {
  /**
   * @see Identity_Driver::activate.
   */
  public function activate() {
    user::activate();
  }

  /**
   * @see Identity_Driver::deactivate.
   */
  public function deactivate() {
    user::deactivate();
  }

  /**
   * @see Identity_Driver::guest.
   */
  public function guest() {
    return user::guest();
  }

  /**
   * @see Identity_Driver::create_user.
   */
  public function create_user($name, $full_name, $password) {
    return user::create($name, $full_name, $password);
  }

  /**
   * @see Identity_Driver::is_correct_password.
   */
  public function is_correct_password($user, $password) {
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
    $sanitizedPassword = html::specialchars($password, false);
    $guess = (strlen($valid) == 32) ? md5($sanitizedPassword)
          : ($salt . md5($salt . $sanitizedPassword));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    return false;
  }

  /**
   * @see Identity_Driver::lookup_user.
   */
  public function lookup_user($id) {
    return user::lookup_by_field("id", $id);
  }

  /**
   * @see Identity_Driver::lookup_user_by_name.
   */
  public function lookup_user_by_name($name) {
    return user::lookup_by_field("name", $name);
  }

  /**
   * @see Identity_Driver::create_group.
   */
  public function create_group($name) {
    return group::create($name);
  }

  /**
   * @see Identity_Driver::everybody.
   */
  public function everybody() {
    return group::everybody();
  }

  /**
   * @see Identity_Driver::registered_users.
   */
  public function registered_users() {
    return group::registered_users();
  }

  /**
   * @see Identity_Driver::lookup_group_by_name.
   */
  static function lookup_group_by_name($name) {
    return group::lookup_by_field("name", $name);
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  public function get_user_list($ids) {
    return ORM::factory("user")
      ->in("id", ids)
      ->find_all()
      ->as_array();
  }
} // End Identity Gallery Driver

