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
      url::redirect("admin/users/edit/$id");
    }

    $view = new Admin_View("admin.html");
    $view->content = $form;
    print $view;
  }
}
