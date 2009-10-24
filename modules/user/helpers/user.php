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
  static function get_login_form($url) {
    $form = new Forge($url, "", "post", array("id" => "g-login-form"));
    $form->set_attr('class', "g-one-quarter");
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null);
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }

  /**
   * Make sure that we have a session and group_ids cached in the session.
   */
  static function load_user() {
    $session = Session::instance();
    if (!($user = $session->get("user"))) {
      $session->set("user", $user = user::guest());
    }

    // The installer cannot set a user into the session, so it just sets an id which we should
    // upconvert into a user.
    if ($user === 2) {
      $user = model_cache::get("user", 2);
      user::login($user);
      $session->set("user", $user);
    }

    if (!$session->get("group_ids")) {
      $ids = array();
      foreach ($user->groups as $group) {
        $ids[] = $group->id;
      }
      $session->set("group_ids", $ids);
    }
  }

  /**
   * Return the array of group ids this user belongs to
   *
   * @return array
   */
  static function group_ids() {
    return Session::instance()->get("group_ids", array(1));
  }

  /**
   * Return the active user.  If there's no active user, return the guest user.
   *
   * @return User_Model
   */
  static function active() {
    // @todo (maybe) cache this object so we're not always doing session lookups.
    $user = Session::instance()->get("user", null);
    if (!isset($user)) {
      // Don't do this as a fallback in the Session::get() call because it can trigger unnecessary
      // work.
      $user = user::guest();
    }
    return $user;
  }

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
   * Change the active user.
   *
   * @return User_Model
   */
  static function set_active($user) {
    $session = Session::instance();
    $session->set("user", $user);
    $session->delete("group_ids");
    self::load_user();
  }

  /**
   * Create a new user.
   *
   * @param string  $name
   * @param string  $full_name
   * @param string  $password
   * @return User_Model
   */
  static function create($name, $full_name, $password) {
    $user = ORM::factory("user")->where("name", $name)->find();
    if ($user->loaded) {
      throw new Exception("@todo USER_ALREADY_EXISTS $name");
    }

    $user->name = $name;
    $user->full_name = $full_name;
    $user->password = $password;

    // Required groups
    $user->add(group::everybody());
    $user->add(group::registered_users());

    $user->save();
    return $user;
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
    $sanitizedPassword = html::specialchars($password, false);
    $guess = (strlen($valid) == 32) ? md5($sanitizedPassword)
          : ($salt . md5($salt . $sanitizedPassword));
    if (!strcmp($guess, $valid)) {
      return true;
    }

    return false;
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
   * Log in as a given user.
   * @param object $user the user object.
   */
  static function login($user) {
    $user->login_count += 1;
    $user->last_login = time();
    $user->save();

    user::set_active($user);
    module::event("user_login", $user);
  }

  /**
   * Log out the active user and destroy the session.
   * @param object $user the user object.
   */
  static function logout() {
    $user = user::active();
    if (!$user->guest) {
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        Kohana::log("error", $e);
      }
      module::event("user_logout", $user);
    }
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