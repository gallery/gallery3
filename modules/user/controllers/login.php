<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Login_Controller extends Controller {
  public function index() {
    if (request::method() == "post") {
      $this->_try_login();
    } else {
      print $this->_login_form();
    }
  }

  private function _try_login() {
    $form = $this->_login_form();

    $valid = $form->validate();
    if ($valid) {
      $user = ORM::factory("user")->where("name", $form->login->inputs["name"]->value)->find();
      if (!$user->loaded || !user::is_correct_password($user, $form->login->password->value)) {
        log::warning(
          "user", t("Failed login for %name", array("name" => $form->login->inputs["name"]->value)));
        $form->login->inputs["name"]->add_error("invalid_login", 1);
        $valid = false;
      }
    }

    if ($valid) {
      user::login($user);
      log::info("user", t("User %name logged in", array("name" => $user->name)));
      print json_encode(
        array("result" => "success"));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  private function _login_form() {
    $form = new Forge(url::current(true), "", "post", array("id" => "gLogin_Form"));
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Name"))->id("gName")->class(null);
    $group->password("password")->label(t("Password"))->id("gPassword")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }
}