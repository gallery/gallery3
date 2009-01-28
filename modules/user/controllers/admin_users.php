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

  public function add_user() {
    $form = user::get_add_form_admin();

    $valid = $form->validate();
    $name = $form->add_user->inputs["name"]->value;
    $user = ORM::factory("user")->where("name", $name)->find();
    if ($user->loaded) {
      $form->add_user->inputs["name"]->add_error("in_use", 1);
      $valid = false;
    }

    if ($valid) {
      $user = user::create(
        $name, $form->add_user->full_name->value, $form->add_user->password->value);
      $user->email = $form->add_user->email->value;
      $user->save();
      message::success(t("Created user %user_name", array("user_name" => $user->name)));
      print json_encode(array("result" => "success"));
    } else {
      message::error(t("Failed to create user"));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function add_user_form() {
    print user::get_add_form_admin();
  }

  public function delete_user($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $form = user::get_delete_form_admin($user);
    if($form->validate()) {
      $name = $user->name;
      $user->delete();
    } else {
      message::error(t("Failed to delete user"));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }

    $message = t("Deleted user %user_name", array("user_name" => $name));
    log::success("user", $message);
    message::success($message);
    print json_encode(array("result" => "success"));
  }

  public function delete_user_form($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }
    print user::get_delete_form_admin($user);
  }

  public function edit_user($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    $form = user::get_edit_form_admin($user);
    $form->edit_user->password->rules("-required");
    $valid = $form->validate();
    if ($valid) {
      $new_name = $form->edit_user->inputs["name"]->value;
      $user = ORM::factory("user")->where("name", $new_name)->find();
      if ($user->loaded) {
        $form->edit_user->inputs["name"]->add_error("in_use", 1);
        $valid = false;
      }
    }

    if ($valid) {
      $user->name = $new_name;
      $user->full_name = $form->edit_user->full_name->value;
      $user->password = $form->edit_user->password->value;
      $user->email = $form->edit_user->email->value;
      $user->save();
      message::success(t("Changed user %user_name", array("user_name" => $user->name)));
      print json_encode(array("result" => "success"));
    } else {
      message::error(t("Failed to change user %user_name", array("user_name" => $user->name)));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function edit_user_form($id) {
    $user = ORM::factory("user", $id);
    if (!$user->loaded) {
      kohana::show_404();
    }

    print user::get_edit_form_admin($user);
  }

  public function add_user_to_group($user_id, $group_id) {
    access::verify_csrf();
    $group = ORM::factory("group", $group_id);
    $user = ORM::factory("user", $user_id);
    $group->add($user);
    $group->save();
  }

  public function remove_user_from_group($user_id, $group_id) {
    access::verify_csrf();
    $group = ORM::factory("group", $group_id);
    $user = ORM::factory("user", $user_id);
    $group->remove($user);
    $group->save();
  }

  public function group($group_id) {
    $view = new View("admin_users_group.html");
    $view->group = ORM::factory("group", $group_id);
    print $view;
  }
}
