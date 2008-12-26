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
class Admin_Users_Controller extends Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_users.html");
    $view->content->users = ORM::factory("user")->orderby("name")->find_all();
    print $view;
  }

  public function create() {
    $form = user::get_add_form_admin();
    rest::http_content_type(rest::JSON);
    if($form->validate()) {
      $user = user::create($form->add_user->inputs["name"]->value,
                           $form->add_user->full_name->value, $form->add_user->password->value);
      $user->email = $form->add_user->email->value;
      $user->save();
      message::success(sprintf(_("Created user %s"), $user->name));
      print json_encode(array("result" => "success"));
    } else {
      message::error(_("Failed to create user"));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function create_form() {
    print user::get_add_form_admin();
  }
    
  public function delete($id) {
    rest::http_content_type(rest::JSON);
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $name = $user->name;
    $user->delete();

    log::success("user", sprintf(_("Deleted user %s"), $name));
    message::success(sprintf(_("Deleted user %s"), $name));
    print json_encode(array("result" => "success"));
  }
  
  public function delete_form($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }
    print user::get_delete_form_admin($user);
  }

  public function edit($id) {
    rest::http_content_type(rest::JSON);
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $form = user::get_edit_form_admin($user);
    $form->edit_user->password->rules("-required");
    if($form->validate()) {
      $user->name = $form->edit_user->uname->value;
      $user->full_name = $form->edit_user->full_name->value;
      $user->password = $form->edit_user->password->value;
      $user->email = $form->edit_user->email->value;
      $user->save();
      message::success(sprintf(_("Changed user %s"), $user->name));
      print json_encode(array("result" => "success"));
    } else {
      message::error(sprintf(_("Failed to change user %s"), $user->name));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }
  
  public function edit_form($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    print user::get_edit_form_admin($user);
  }
}
