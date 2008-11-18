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
   * @see Rest_Controller::_show($resource, $output_format)
   */
  public function _show($user, $output_format) {
    throw new Exception("@todo User_Controller::_show NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_update($resource)
   */
  public function _update($user) {
    $form = user::get_edit_form($user);
    if ($form->validate()) {
      foreach ($form->as_array() as $key => $value) {
        $user->$key = $value;
      }
      $user->save();
      if ($continue = $this->input->get("continue")) {
        url::redirect($continue);
      }
      return;
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
  public function _form($user, $form_type) {
    if ($form_type == "edit") {
      $form = user::get_edit_form($user);
      print $form;
    } else {
      return Kohana::show_404();
    }
  }
}
