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
class Gallery_Auth {
  /**
   * Validate a login attempt, and add error messages or run callbacks as needed.
   *
   * @param  Validation $v        validation object (":validation")
   * @param  array      $data     data array        (":data" in Validation, ":form_val" in Formo)
   * @param  string     $name     username field name, to which errors are attached
   * @param  string     $password password field name
   */
  static function validate_login(Validation $v, $data, $name, $password) {
    if (empty($data[$name]) || empty($data[$password])) {
      $v->error($name, "invalid");
    } else if (!static::validate_too_many_failures($data[$name])) {
      $v->error($name, "too_many_failures");
    } else {
      $user = Identity::lookup_user_by_name($data[$name]);
      if (!empty($user) && Identity::is_correct_password($user, $data[$password])) {
        static::_login($user);
      } else {
        static::_login_failed($data[$name]);
        $v->error($name, "invalid");
      }
    }
  }

  /**
   * Validate a re-authenticate attempt, and add error messages or run callbacks as needed.
   *
   * @param  Validation $v     validation object   (":validation")
   * @param  string     $field password field name (":field")
   * @param  string     $value password value      (":value")
   */
  static function validate_reauthenticate(Validation $v, $field, $value) {
    $user = Identity::active_user();
    if (!static::validate_too_many_failures($user->name)) {
      $v->error($field, "too_many_failures");
    } else {
      if (Identity::is_correct_password($user, $value)) {
        static::_reauthenticate($user);
      } else {
        static::_reauthenticate_failed($user->name);
        $v->error($field, "invalid");
      }
    }
  }

  /**
   * Logout a user.  Unlike login and re-authenticate, little validation is needed for logout
   * aside from CSRF, so controllers can call this function directly.
   *
   * @param object $user
   */
  static function logout() {
    $user = Identity::active_user();
    if (!$user->guest) {
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        Log::instance()->add(Log::ERROR, $e);
      }
      Module::event("user_logout", $user);
    }
    GalleryLog::info("user", t("User %name logged out", array("name" => $user->name)),
              t('<a href="%url">%user_name</a>',
                array("url" => UserProfile::url($user->id),
                      "user_name" => HTML::clean($user->name))));
  }

  /**
   * Login a user.  This is intended as a callback after passing validation.
   * As such, this function performs no validation of its own.
   *
   * @param object $user
   */
  protected static function _login($user) {
    Identity::set_active_user($user);
    if (Identity::is_writable()) {
      $user->login_count += 1;
      $user->last_login = time();
      $user->save();
    }
    GalleryLog::info("user", t("User %name logged in", array("name" => $user->name)));
    Session::instance()->set("active_auth_timestamp", time());
    static::_clear_failed_attempts($user);
    Module::event("user_login", $user);
  }

  /**
   * Reauthenticate a user.  This is intended as a callback after passing validation.
   * As such, this function performs no validation of its own.
   *
   * @param object $user
   */
  protected static function _reauthenticate($user) {
    if (!Request::current()->is_ajax()) {
      Message::success(t("Successfully re-authenticated!"));
    }
    Session::instance()->set("active_auth_timestamp", time());
    static::_clear_failed_attempts($user);
    Module::event("user_auth", $user);
  }

  /**
   * Process a login failure.  This is intended as a callback after failing validation.
   * As such, this function performs no validation of its own.
   *
   * @param string $name
   */
  protected static function _login_failed($name) {
    GalleryLog::warning("user", t("Failed login for %name", array("name" => $name)));
    static::_record_failed_attempt($name);
    Module::event("user_auth_failed", $name);
  }

  /**
   * Process a re-authenticate failure.  This is intended as a callback after failing validation.
   * As such, this function performs no validation of its own.
   *
   * @param string $name
   */
  protected static function _reauthenticate_failed($name) {
    GalleryLog::warning("user", t("Failed re-authentication for %name", array("name" => $name)));
    static::_record_failed_attempt($name);
    Module::event("user_auth_failed", $name);
  }

  /**
   * Validate that there haven't been too many failed login/re-authenticate attempts.
   * After 5 failed auth attempts, any failure leads to getting locked out for a minute.
   *
   * @param  string $name
   * @return boolean
   */
  static function validate_too_many_failures($name) {
    $failed = ORM::factory("FailedAuth")
      ->where("name", "=", $name)
      ->find();
    return !($failed->loaded() &&
            $failed->count > 5 &&
            (time() - $failed->time < 60));
  }

  /**
   * Record a failed authentication for this user
   */
  protected static function _record_failed_attempt($name) {
    $failed = ORM::factory("FailedAuth")
      ->where("name", "=", $name)
      ->find();
    if (!$failed->loaded()) {
      $failed->name = $name;
    }
    $failed->time = time();
    $failed->count++;
    $failed->save();
  }

  /**
   * Clear any failed logins for this user
   */
  protected static function _clear_failed_attempts($user) {
    DB::delete("failed_auths")
      ->where("name", "=", $user->name)
      ->execute();
  }

  /**
   * Checks how much time is remaining before the current (admin) user
   * must actively re-authenticate before access is given to the admin
   * area.  This can be negative if the user has already timed out.
   */
  static function get_time_remaining_for_admin_area() {
    $last_active_auth         = Session::instance()->get("active_auth_timestamp", 0);
    $last_admin_area_activity = Session::instance()->get("admin_area_activity_timestamp", 0);
    $admin_area_timeout       = Module::get_var("gallery", "admin_area_timeout", 90 * 60);

    return max($last_active_auth, $last_admin_area_activity) + $admin_area_timeout - time();
  }
}