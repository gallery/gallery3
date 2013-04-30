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
  static function login($user) {
    Identity::set_active_user($user);
    if (Identity::is_writable()) {
      $user->login_count += 1;
      $user->last_login = time();
      $user->save();
    }
    GalleryLog::info("user", t("User %name logged in", array("name" => $user->name)));
    Module::event("user_login", $user);
  }

  static function reauthenticate($user) {
    if (!Request::current()->is_ajax()) {
      Message::success(t("Successfully re-authenticated!"));
    }
    Module::event("user_auth", $user);
  }

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
   * Validate the username and password.  This uses a syntax similar to Valid::matches().
   */
  static function validate_username_and_password($array, $name, $password) {
    $user = Identity::lookup_user_by_name($array[$name]);
    return (!empty($user) && Identity::is_correct_password($user, $array[$password]));
  }

  /**
   * After there have been 5 failed auth attempts, any failure leads to getting locked out for a
   * minute.
   */
  static function too_many_failures($name) {
    $failed = ORM::factory("FailedAuth")
      ->where("name", "=", $name)
      ->find();
    return ($failed->loaded() &&
            $failed->count > 5 &&
            (time() - $failed->time < 60));
  }

  /**
   * Validate that there haven't been too many failed login attempts.
   * This uses a syntax similar to Valid::matches().
   */
  static function validate_too_many_failed_logins($array, $name) {
    return !Auth::too_many_failures($array[$name]);
  }

  /**
   * Record a failed authentication for this user
   */
  static function record_failed_attempt($name) {
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
  static function clear_failed_attempts($user) {
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