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
class Admin_Controller extends Controller {
  private $theme;

  public function __construct($theme=null) {
    if (!identity::active_user()->admin) {
      if (identity::active_user()->guest) {
        Session::instance()->set("continue_url", url::abs_current(true));
        url::redirect("login");
      } else {
        access::forbidden();
      }
    }

    parent::__construct();
  }

  public function __call($controller_name, $args) {
    if (Input::instance()->get("reauth_check")) {
      return self::_reauth_check();
    }
    if (auth::must_reauth_for_admin_area()) {
      return self::_prompt_for_reauth($controller_name, $args);
    }

    if (request::method() == "post") {
      access::verify_csrf();
    }

    if ($controller_name == "index") {
      $controller_name = "dashboard";
    }
    $controller_name = "Admin_{$controller_name}_Controller";
    if ($args) {
      $method = array_shift($args);
    } else {
      $method = "index";
    }

    if (!class_exists($controller_name) || !method_exists($controller_name, $method)) {
      throw new Kohana_404_Exception();
    }

    call_user_func_array(array(new $controller_name, $method), $args);
  }

  private static function _reauth_check() {
    $session = Session::instance();
    $last_active_auth = $session->get("active_auth_timestamp", 0);
    $last_admin_area_activity = $session->get("admin_area_activity_timestamp", 0);
    $admin_area_timeout = module::get_var("gallery", "admin_area_timeout");

    $time_remaining = max($last_active_auth, $last_admin_area_activity) +
      $admin_area_timeout - time();

    $result = new stdClass();
    $result->result = "success";
    if ($time_remaining < 30) {
      message::success(t("Automatically logged out of the admin area for your security"));
      $result->location = url::abs_site("");
    }

    json::reply($result);
  }

  private static function _prompt_for_reauth($controller_name, $args) {
    if (request::method() == "get") {
      // Avoid anti-phishing protection by passing the url as session variable.
      Session::instance()->set("continue_url", url::abs_current(true));
    }
    // Save the is_ajax value as we lose it, if set, when we redirect
    Session::instance()->set("is_ajax_request", request::is_ajax());
    url::redirect("reauthenticate");
  }
}

