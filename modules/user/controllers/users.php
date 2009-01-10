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
class Users_Controller extends REST_Controller {
  protected $resource_type = "user";

  public function _update($user) {
    if ($user->guest || $user->id != user::active()->id) {
      access::forbidden();
    }

    $form = user::get_edit_form($user);
    $form->edit_user->password->rules("-required");
    if ($form->validate()) {
      // @todo: allow the user to change their name
      // @todo: handle password changing gracefully
      $user->full_name = $form->edit_user->full_name->value;
      if ($form->edit_user->password->value) {
        $user->password = $form->edit_user->password->value;
      }
      $user->email = $form->edit_user->email->value;
      $user->url = $form->edit_user->url->value;
      $user->save();

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
