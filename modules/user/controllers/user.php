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
class User_Controller extends REST_Controller {
  protected $resource_type = "user";

  /**
   * Present a form for editing a user
   *  @see Rest_Controller::form($resource)
   */
  public function _form($user) {
    $form = user::get_edit_form($user);
    print $form;
  }

  /**
   * @see Rest_Controller::_get($resource)
   */
  public function _get($user) {
    throw new Exception("@todo User_Controller::_get NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_put($resource)
   */
  public function _put($resource) {
  }

  /**
   *  @see Rest_Controller::_post($resource)
   */
  public function _post($user) {
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
   throw new Exception("@todo User_Controller::_put NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($resource) {
    throw new Exception("@todo User_Controller::_delete NOT IMPLEMENTED");
  }
}