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
class auth_Core {
  static function get_login_form($url) {
    $form = new Forge($url, "", "post", array("id" => "g-login-form"));
    $form->set_attr('class', "g-narrow");
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null)
      ->callback("auth::validate_too_many_failed_logins")
      ->error_messages(
        "too_many_failed_logins", t("Too many failed login attempts.  Try again later"));
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }

  static function login($user) {
    identity::set_active_user($user);
    if (identity::is_writable()) {
      $user->login_count += 1;
      $user->last_login = time();
      $user->save();
    }
    log::info("user", t("User %name logged in", array("name" => $user->name)));
    module::event("user_login", $user);
  }

  static function logout() {
    $user = identity::active_user();
    if (!$user->guest) {
      try {
        Session::instance()->destroy();
      } catch (Exception $e) {
        Kohana_Log::add("error", $e);
      }
      module::event("user_logout", $user);
    }
    log::info("user", t("User %name logged out", array("name" => $user->name)),
              t('<a href="%url">%user_name</a>',
                array("url" => user_profile::url($user->id),
                      "user_name" => html::clean($user->name))));
  }

  /**
   * After there have been 5 failed login attempts, any failure leads to getting locked out for a
   * minute.
   */
  static function too_many_failed_logins($name) {
    $failed_login = ORM::factory("failed_login")
      ->where("name", "=", $name)
      ->find();
    return ($failed_login->loaded() &&
            $failed_login->count > 5 &&
            (time() - $failed_login->time < 60));
  }

  static function validate_too_many_failed_logins($name_input) {
    if (self::too_many_failed_logins($name_input->value)) {
      $name_input->add_error("too_many_failed_logins", 1);
    }
  }

  /**
   * Record a failed login for this user
   */
  static function record_failed_login($name) {
    $failed_login = ORM::factory("failed_login")
      ->where("name", "=", $name)
      ->find();
    if (!$failed_login->loaded()) {
      $failed_login->name = $name;
    }
    $failed_login->time = time();
    $failed_login->count++;
    $failed_login->save();
  }

  /**
   * Clear any failed logins for this user
   */
  static function record_successful_login($user) {
    db::build()
      ->delete("failed_logins")
      ->where("name", "=", $user->name)
      ->execute();
  }
}