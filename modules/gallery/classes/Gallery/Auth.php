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
  static function get_login_form($url) {
    $form = new Forge($url, "", "post", array("id" => "g-login-form"));
    $form->set_attr("class", "g-narrow");
    $form->hidden("continue_url")->value(Session::instance()->get("continue_url"));
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null)
      ->callback("Auth::validate_too_many_failed_logins")
      ->error_messages(
        "too_many_failed_logins", t("Too many failed login attempts.  Try again later"));
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }

  static function login($user) {
    Identity::set_active_user($user);
    if (Identity::is_writable()) {
      $user->login_count += 1;
      $user->last_login = time();
      $user->save();
    }
    Log::info("user", t("User %name logged in", array("name" => $user->name)));
    Module::event("user_login", $user);
  }

  static function logout() {
    $user = Identity::active_user();
    if (!$user->guest) {
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        Log::add("error", $e);
      }
      Module::event("user_logout", $user);
    }
    Log::info("user", t("User %name logged out", array("name" => $user->name)),
              t('<a href="%url">%user_name</a>',
                array("url" => UserProfile::url($user->id),
                      "user_name" => HTML::clean($user->name))));
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

  static function validate_too_many_failed_logins($name_input) {
    if (Auth::too_many_failures($name_input->value)) {
      $name_input->add_error("too_many_failed_logins", 1);
    }
  }

  static function validate_too_many_failed_auth_attempts($form_input) {
    if (Auth::too_many_failures(Identity::active_user()->name)) {
      $form_input->add_error("too_many_failed_auth_attempts", 1);
    }
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
    ORM::factory("FailedAuth")
      ->where("name", "=", $user->name)
      ->delete_all();
  }

  /**
   * Checks whether the current user (= admin) must
   * actively re-authenticate before access is given
   * to the admin area.
   */
  static function must_reauth_for_admin_area() {
    if (!Identity::active_user()->admin) {
      Access::forbidden();
    }

    $session = Session::instance();
    $last_active_auth = $session->get("active_auth_timestamp", 0);
    $last_admin_area_activity = $session->get("admin_area_activity_timestamp", 0);
    $admin_area_timeout = Module::get_var("gallery", "admin_area_timeout");

    if (max($last_active_auth, $last_admin_area_activity) + $admin_area_timeout < time()) {
      return true;
    }

    $session->set("admin_area_activity_timestamp", time());
    return false;
  }
}