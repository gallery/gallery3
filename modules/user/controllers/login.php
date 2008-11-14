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
    $form = new Forge();
    $form->input("username")->rules("required|length[4,32]");
    $form->password("password")->rules("required|length[5,40]");
    $form->submit("Login");
    print $form->render("login.html", true);
  }

  public function process() {
    $form = new Forge("login.html", true);
    $form->input("username")->rules("required|length[4,32]");
    $form->password("password")->rules("required|length[5,40]");
    $form->submit("Login");

    $response = array();
    if ($form->validate()) {
      // Load the user
      $user = ORM::factory("user")->where("name", $form->username->value)->find();
      if (!$user->loaded) {
        $response["error_message"] = _("Invalid username or password");
      } else {
        if (user::is_correct_password($user, $form->password->value)) {
          user::login($user);
          $response["error_message"] = "";
        } else {
          $response["error_message"] = _("Invalid username or password");
        }
      }
    } else {
      $response["error_message"] = _("Invalid username or password");
    }

    print json_encode($response);
  }

}