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

  /**
   * Display comments based on criteria.
   *  @see Rest_Controller::_index()
   */
  public function _index() {
    throw new Exception("@todo Comment_Controller::_index NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_create($resource)
   */
  public function _create($user) {
    throw new Exception("@todo User_Controller::_create NOT IMPLEMENTED");
  }

  /**
   * @see Rest_Controller::_show($resource)
   */
  public function _show($user) {
    throw new Exception("@todo User_Controller::_show NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_update($resource)
   */
  public function _update($user) {
    if ($user->guest || (!user::active()->admin && $user->id != user::active()->id)) {
      access::forbidden();
    }

    $form = user::get_edit_form($user, "");
    $form->edit_user->password->rules("-required");
    if ($form->validate()) {
      $user->full_name = $form->edit_user->full_name->value;
      $user->password = $form->edit_user->password->value;
      $user->email = $form->edit_user->email->value;
      $user->save();
      if ($continue = $this->input->get("continue")) {
        url::redirect($continue);
      }
    }
    print $form;
  }

  /**
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($user) {
    throw new Exception("@todo User_Controller::_delete NOT IMPLEMENTED");
  }

  /**
   * Present a form for editing a user
   *  @see Rest_Controller::form($resource)
   */
  public function _form_edit($user) {
    if ($user->guest || user::active()->id != $user->id) {
      access::forbidden();
    }

    print user::get_edit_form(
      $user,
      "users/{$user->id}?_method=put&continue=" . $this->input->get("continue"));
  }

  /**
   * Present a form for adding a user
   *  @see Rest_Controller::form($resource)
   */
  public function _form_add($parameters) {
    throw new Exception("@todo User_Controller::_form_add NOT IMPLEMENTED");
  }
}
