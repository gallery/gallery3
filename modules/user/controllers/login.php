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
    $form = new Forge(url::current(true), "", "post", array("id" => "gLoginForm"));
    $group = $form->group("login_form")->label(_("Login"));
    $group->input("name")->label(_("Name"))->id("gName")->class(null);
    $group->password("password")->label(_("Password"))->id("gPassword")->class(null);
    $group->submit(_("Login"));
    $group->inputs["name"]->error_messages("invalid_login", _("Invalid name or password"));

    if ($form->validate()) {
      $user = ORM::factory("user")->where("name", $group->inputs["name"]->value)->find();
      if ($user->loaded &&
          user::is_correct_password($user, $group->password->value)) {
        user::login($user);
        if ($continue = $this->input->get("continue")) {
          url::redirect($continue);
        }
        return;
      } else {
        $group->inputs["name"]->add_error("invalid_login", 1);
      }
    }

    print $form->render();
  }
}