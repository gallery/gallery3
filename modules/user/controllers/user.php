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
   * Return the form for creating / modifying users.
   */
  private function _get_form($user) {
    $form = new Forge(url::current(true), "", "post", array("id" => "gUser"));
    $group = $form->group(_("User Info"));
    $group->input("name")
      ->label(_("Name"))
      ->id("gName")
      ->class(null)
      ->value($user->name);
    $group->input("display_name")
      ->label(_("Display Name"))
      ->id("gDisplayName")
      ->class(null)
      ->value($user->display_name);
    $group->password("password")
      ->label(_("Password"))
      ->id("gPassword")
      ->class(null);
    $group->input("email")
      ->label(_("Email"))
      ->id("gEmail")
      ->class(null)
      ->value($user->email);
    $group->submit(_("Modify"));

    $this->_add_validation_rules(ORM::factory("user")->validation_rules, $form);

    return $form;
  }

  /**
   * @todo Refactor this into a more generic location
   */
  private function _add_validation_rules($rules, $form) {
    foreach ($form->inputs as $name => $input) {
      if (isset($input->inputs)) {
        $this->_add_validation_rules($rules, $input);
      }
      if (isset($rules[$name])) {
        $input->rules($rules[$name]);
      }
    }
  }

  /**
   * @see Rest_Controller::_get($resource)
   */
  public function _get($user) {
    $form = $this->_get_form($user);
    print $form->render("form.html");
  }

  /**
   *  @see Rest_Controller::_put($resource)
   */
  public function _put($resource) {
    throw new Exception("@todo User_Controller::_put NOT IMPLEMENTED");
  }

  /**
   *  @see Rest_Controller::_post($resource)
   */
  public function _post($user) {
    $form = $this->_get_form($user);
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
    print $form->render("form.html");
  }

  /**
   *  @see Rest_Controller::_delete($resource)
   */
  public function _delete($resource) {
    throw new Exception("@todo User_Controller::_delete NOT IMPLEMENTED");
  }
}