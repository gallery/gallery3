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
class Groups_Controller extends REST_Controller {
  protected $resource_type = "group";

  /**
   * Display comments based on criteria.
   *  @see REST_Controller::_index()
   */
  public function _index() {
    throw new Exception("@todo Group_Controller::_index NOT IMPLEMENTED");
  }

  /**
   *  @see REST_Controller::_create($resource)
   */
  public function _create($resource) {
    $form = group::get_add_form();
    if ($form->validate()) {
      group::create($form->add_group->gname->value);
      if ($continue = $this->input->get("continue")) {
        url::redirect($continue);
      }
    }
    print $form;
  }

  /**
   * @see REST_Controller::_show($resource)
   */
  public function _show($user) {
    throw new Exception("@todo Group_Controller::_show NOT IMPLEMENTED");
  }

  /**
   *  @see REST_Controller::_update($resource)
   */
  public function _update($group) {
    $form = group::get_edit_form($group);
    if ($form->validate()) {
      $group->name = $form->edit_group->gname->value;
      $group->save();
      if ($continue = $this->input->get("continue")) {
        url::redirect($continue);
      }
    }
    print $form;
  }

  /**
   *  @see REST_Controller::_delete($resource)
   */
  public function _delete($group) {
    if (!(user::active()->admin) || $group->special) {
      access::forbidden();
    }
    // Prevent CSRF
    $form = group::get_delete_form($group);
    if ($form->validate()) {
      $group->delete();
      if ($continue = $this->input->get("continue")) {
        url::redirect($continue);
      }
    }
    print $form;
  }

  /**
   * Present a form for editing a user
   *  @see REST_Controller::form($resource)
   */
  public function _form_edit($group) {
    if ($group->guest || group::active()->id != $group->id) {
      access::forbidden();
    }

    print group::get_edit_form(
      $group,
      "users/{$group->id}?_method=put&continue=" . $this->input->get("continue"));
  }

  /**
   * Present a form for adding a user
   *  @see REST_Controller::form($resource)
   */
  public function _form_add($parameters) {
    throw new Exception("@todo Group_Controller::_form_add NOT IMPLEMENTED");
  }
}
