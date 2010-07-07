<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Reauthenticate_Controller extends Controller {
  public function index($share_translations_form=null) {
    if (!identity::active_user()->admin) {
      access::forbidden();
    }
    $reauthenticate = Session::instance()->get("reauthenticate", array());
    if (empty($reauthenticate["in_dialog"])) {
      self::_show_form(self::_form());
    } else {
      print json_encode(array("form" => (string) self::_form()));
    }
  }

  public function auth() {
    if (!identity::active_user()->admin) {
      access::forbidden();
    }
    access::verify_csrf();

    $reauthenticate = Session::instance()->get("reauthenticate", array());
    Kohana_Log::add("error", Kohana::debug($reauthenticate));

    $form = self::_form();
    $valid = $form->validate();
    $user = identity::active_user();
    if ($valid) {
      message::success(t("Successfully re-authenticated!"));
      module::event("user_auth", $user);
      Session::instance()->delete("reauthenticate");
      if (empty($reauthenticate["in_dialog"])) {
        url::redirect($reauthenticate["continue_url"]);
      } else {
        self::_call_admin_function($reauthenticate);
      }
    } else {
      $name = $user->name;
      log::warning("user", t("Failed re-authentication for %name", array("name" => $name)));
      module::event("user_auth_failed", $name);
      if (empty($reauthenticate["in_dialog"])) {
        self::_show_form($form);
      } else {
        print json_encode(array("form" => (string) $form));
      }
    }
  }

  private static function _show_form($form) {
    $view = new Theme_View("page.html", "other", "reauthenticate");
    $view->page_title = t("Re-authenticate");
    $view->content = new View("reauthenticate.html");
    $view->content->form = $form;
    $view->content->user_name = identity::active_user()->name;

    print $view;
  }

  private static function _form() {
    $form = new Forge("reauthenticate/auth", "", "post", array("id" => "g-reauthenticate-form"));
    $form->set_attr('class', "g-narrow");
    $form->hidden("continue_url")->value(Session::instance()->get("continue_url", "admin"));
    $group = $form->group("reauthenticate")->label(t("Re-authenticate"));
    $group->password("password")->label(t("Password"))->id("g-password")->class(null)
      ->callback("auth::validate_too_many_failed_auth_attempts")
      ->callback("user::valid_password")
      ->error_messages("invalid_password", t("Incorrect password"))
      ->error_messages(
        "too_many_failed_auth_attempts",
        t("Too many incorrect passwords.  Try again later"));
    $group->submit("")->value(t("Submit"));
    return $form;
  }

  private static function _call_admin_function($reauthenticate) {
    $controller_name = $reauthenticate["controller"];
    $args = $reauthenticate["args"];
    if ($controller_name == "index") {
      $controller_name = "dashboard";
    }

    $controller_name = "Admin_{$controller_name}_Controller";
    if ($args) {
      $method = array_shift($args);
    } else {
      $method = "index";
    }

    if (!method_exists($controller_name, $method)) {
      throw new Kohana_404_Exception();
    }

    call_user_func_array(array(new $controller_name, $method), $args);
  }
}
