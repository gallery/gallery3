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
class Gallery_Controller_Admin extends Controller {
  public function before() {
    parent::before();

    if (!Identity::active_user()->admin) {
      if (Identity::active_user()->guest) {
        Session::instance()->set("continue_url", URL::abs_current(true));
        HTTP::redirect("login");
      } else {
        Access::forbidden();
      }
    }

    if (Request::current()->query("reauth_check")) {
      return self::_reauth_check();
    }

    if (Auth::must_reauth_for_admin_area()) {
      return self::_prompt_for_reauth($controller_name, $args);
    }

    if (Request::current()->method() == HTTP_Request::POST) {
      Access::verify_csrf();
    }
  }

  private static function _reauth_check() {
    $session = Session::instance();
    $last_active_auth = $session->get("active_auth_timestamp", 0);
    $last_admin_area_activity = $session->get("admin_area_activity_timestamp", 0);
    $admin_area_timeout = Module::get_var("gallery", "admin_area_timeout");

    $time_remaining = max($last_active_auth, $last_admin_area_activity) +
      $admin_area_timeout - time();

    $result = new stdClass();
    $result->result = "success";
    if ($time_remaining < 30) {
      Message::success(t("Automatically logged out of the admin area for your security"));
      $result->location = URL::abs_site("");
    }

    JSON::reply($result);
  }

  private static function _prompt_for_reauth($controller_name, $args) {
    if (Request::current()->method() == HTTP_Request::GET) {
      // Avoid anti-phishing protection by passing the url as session variable.
      Session::instance()->set("continue_url", URL::abs_current(true));
    }
    // Save the is_ajax value as we lose it, if set, when we redirect
    Session::instance()->set("is_ajax_request", Request::current()->is_ajax());
    HTTP::redirect("reauthenticate");
  }
}

