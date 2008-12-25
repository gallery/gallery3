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
    $view->content->groups = ORM::factory("group")->orderby("name")->find_all();
    print $view;
  }

  public function create() {
    $form = user::get_add_form_admin();
    if ($form->validate()) {
      $user = user::create($form->add_user->inputs["name"]->value,
                           $form->add_user->full_name->value, $form->add_user->password->value);
      $user->email = $form->add_user->email->value;
      $user->save();
      log::add("user", sprintf(_("Created user %s"), $user->name));
      message::add(sprintf(_("Created user %s"), $user->name));
      url::redirect("admin/users");
    }

    print $form;
  }

  public function delete($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $form = user::get_delete_form_admin($user);
    if ($form->validate()) {
      $name = $user->name;
      $user->delete();

      log::add("user", sprintf(_("Deleted user %s"), $name));
      message::add(sprintf(_("Deleted user %s"), $name));
      url::redirect("admin/users");
    }

    print $form;
  }

  public function edit($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $form = user::get_edit_form($user, "admin/users/edit/$id");
    if (request::method() =="post" && $form->validate()) {
      $user->name = $form->edit_user->uname->value;
      $user->full_name = $form->edit_user->full_name->value;
      $user->password = $form->edit_user->password->value;
      $user->email = $form->edit_user->email->value;
      $user->save();
      message::add(sprintf(_("Changed user %s"), $user->name));
      url::redirect("admin/users");
    }

    $view = new Admin_View("admin.html");
    $view->content = $form;
    print $view;
  }
}
