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
    $form->set_attr('class', "g-narrow");
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null);
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
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
      $user = self::guest();
    }
    return $user;
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
   * Return the array of group ids this user belongs to
   *
   * @return array
   */
  static function group_ids() {
    return Session::instance()->get("group_ids", array(1));
  }

  /**
   * Make sure that we have a session and group_ids cached in the session.  This is one
   * of the first calls to reference the user so call the Identity::instance to load the
   * driver classes.
   */
  static function load_user() {
    $session = Session::instance();
    if (!($user = $session->get("user"))) {
      $session->set("user", $user = self::guest());
    }

    // The installer cannot set a user into the session, so it just sets an id which we should
    // upconvert into a user.
    // @todo what is user id===2
    if ($user === 2) {
      $user = model_cache::get("user", 2);
      self::login($user);
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
   * Log in as a given user.
   * @param object $user the user object.
   */
  static function login($user) {
    // @todo make this an interface call
    $user->login_count += 1;
    $user->last_login = time();
    $user->save();

    self::set_active($user);
    module::event("user_login", $user);
  }

  /**
   * Log out the active user and destroy the session.
   * @param object $user the user object.
   */
  static function logout() {
    $user = self::active();
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
   * @see Identity_Core::is_writable.
   */
  static function is_writable() {
    return Identity::instance()->is_writable();
  }

  /**
   * @see Identity_Driver::guest.
   */
  static function guest() {
    return Identity::instance()->guest();
  }

  /**
   * @see Identity_Driver::create_user.
   */
  static function create($name, $full_name, $password) {
    return Identity::instance()->create_user($name, $full_name, $password);
  }

  /**
   * @see Identity_Driver::is_correct_password.
   */
  static function is_correct_password($user, $password) {
    return Identity::instance()->is_correct_password($user, $password);
  }

  /**
   * @see Identity_Driver::hash_password.
   */
  static function hash_password($password) {
    return Identity::instance()->hash_password($password);
  }

  /**
   * Look up a user by id.
   * @param integer      $id the user id
   * @return User_Definition  the user object, or null if the id was invalid.
   */
  static function lookup($id) {
    return Identity::instance()->lookup_user_by_field("id", $id);
  }

  /**
   * Look up a user by name.
   * @param integer      $name the user name
   * @return User_Definition  the user object, or null if the name was invalid.
   */
  static function lookup_by_name($name) {
    return Identity::instance()->lookup_user_by_field("name", $name);
  }

  /**
   * Look up a user by hash.
   * @param string       $name the user name
   * @return User_Definition  the user object, or null if the name was invalid.
   */
  static function lookup_by_hash($hash) {
    return Identity::instance()->lookup_user_by_field("hash", $hash);
  }

  /**
   * @see Identity_Driver::get_user_list.
   */
  static function get_user_list($filter=array()) {
    return Identity::instance()->get_user_list($filter);
  }

  /**
   * @see Identity_Driver::get_edit_rules.
   */
  static function get_edit_rules() {
    return Identity::instance()->get_edit_rules("user");
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