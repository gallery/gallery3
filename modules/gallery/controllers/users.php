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
class Users_Controller extends REST_Controller {
  protected $resource_type = "user";

  public function _update($user) {
    if ($user->guest || $user->id != user::active()->id) {
      access::forbidden();
    }

    $form = user::get_edit_form($user);
    $valid = $form->validate();
    if ($valid) {
      $user->full_name = $form->edit_user->full_name->value;
      if ($form->edit_user->password->value) {
        $user->password = $form->edit_user->password->value;
      }
      $user->email = $form->edit_user->email->value;
      $user->url = $form->edit_user->url->value;
      if ($form->edit_user->locale) {
        $desired_locale = $form->edit_user->locale->value;
        $new_locale = $desired_locale == "none" ? null : $desired_locale;
        if ($new_locale != $user->locale) {
          // Delete the session based locale preference
          setcookie("g_locale", "", time() - 24 * 3600, "/");
        }
        $user->locale = $new_locale;
      }
      $user->save();
      module::event("user_edit_form_completed", $user, $form);

      message::success(t("User information updated."));
      print json_encode(
        array("result" => "success",
              "resource" => url::site("users/{$user->id}")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function _form_edit($user) {
    if ($user->guest || $user->id != user::active()->id) {
      access::forbidden();
    }

    print user::get_edit_form($user);
  }
}
