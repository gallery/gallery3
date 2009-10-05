<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Admin_Users_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_users.html");
    $view->content->users = user::users(array("orderby" => array("name")));
    $view->content->groups = group::groups(array("orderby" => array("name")));
    print $view;
  }

  public function add_user() {
    access::verify_csrf();

    $form = user::get_add_form_admin();
    $valid = $form->validate();
    $name = $form->add_user->inputs["name"]->value;
    if ($user = user::lookup_by_name($name)) {
      $form->add_user->inputs["name"]->add_error("in_use", 1);
      $valid = false;
    }

    if ($valid) {
      $user = user::create(
        $name, $form->add_user->full_name->value, $form->add_user->password->value);
      $user->email = $form->add_user->email->value;
      $user->admin = $form->add_user->admin->checked;

      if ($form->add_user->locale) {
        $desired_locale = $form->add_user->locale->value;
        $user->locale = $desired_locale == "none" ? null : $desired_locale;
      }
      $user->save();
      module::event("user_add_form_admin_completed", $user, $form);

      message::success(t("Created user %user_name", array("user_name" => $user->name)));
      print json_encode(array("result" => "success"));
    } else {
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function add_user_form() {
    print user::get_add_form_admin();
  }

  public function delete_user($id) {
    access::verify_csrf();

    if ($id == user::active()->id || $id == user::guest()->id) {
      access::forbidden();
    }

    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }

    $form = user::get_delete_form_admin($user);
    if($form->validate()) {
      $name = $user->name;
      $user->delete();
    } else {
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }

    $message = t("Deleted user %user_name", array("user_name" => $name));
    log::success("user", $message);
    message::success($message);
    print json_encode(array("result" => "success"));
  }

  public function delete_user_form($id) {
    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }
    print user::get_delete_form_admin($user);
  }

  public function edit_user($id) {
    access::verify_csrf();

    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }

    $form = user::get_edit_form_admin($user);
    $valid = $form->validate();
    if ($valid) {
      $new_name = $form->edit_user->inputs["name"]->value;
      $temp_user = user::lookup_by_name($new_name);
      if ($new_name != $user->name &&
          ($temp_user && $temp_user->id != $user->id)) {
        $form->edit_user->inputs["name"]->add_error("in_use", 1);
        $valid = false;
      } else {
        $user->name = $new_name;
      }
    }

    if ($valid) {
      $user->full_name = $form->edit_user->full_name->value;
      if ($form->edit_user->password->value) {
        $user->password = $form->edit_user->password->value;
      }
      $user->email = $form->edit_user->email->value;
      $user->url = $form->edit_user->url->value;
      if ($form->edit_user->locale) {
        $desired_locale = $form->edit_user->locale->value;
        $user->locale = $desired_locale == "none" ? null : $desired_locale;
      }

      // An admin can change the admin status for any user but themselves
      if ($user->id != user::active()->id) {
        $user->admin = $form->edit_user->admin->checked;
      }
      $user->save();
      module::event("user_edit_form_admin_completed", $user, $form);

      message::success(t("Changed user %user_name", array("user_name" => $user->name)));
      print json_encode(array("result" => "success"));
    } else {
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function edit_user_form($id) {
    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }

    $form = user::get_edit_form_admin($user);
    // Don't allow the user to control their own admin bit, else you can lock yourself out
    if ($user->id == user::active()->id) {
      $form->edit_user->admin->disabled(1);
    }
    print $form;
  }

  public function add_user_to_group($user_id, $group_id) {
    access::verify_csrf();
    $group = group::lookup($group_id);
    $user = user::lookup($user_id);
    $group->add($user);
    $group->save();
  }

  public function remove_user_from_group($user_id, $group_id) {
    access::verify_csrf();
    $group = group::lookup($group_id);
    $user = user::lookup($user_id);
    $group->remove($user);
    $group->save();
  }

  public function group($group_id) {
    $view = new View("admin_users_group.html");
    $view->group = group::lookup($group_id);
    print $view;
  }

  public function add_group() {
    access::verify_csrf();

    $form = group::get_add_form_admin();
    $valid = $form->validate();
    if ($valid) {
      $new_name = $form->add_group->inputs["name"]->value;
      $group = group::lookup_by_name($new_name);
      if (!empty($group)) {
        $form->add_group->inputs["name"]->add_error("in_use", 1);
        $valid = false;
      }
    }

    if ($valid) {
      $group = group::create($new_name);
      $group->save();
      message::success(
        t("Created group %group_name", array("group_name" => $group->name)));
      print json_encode(array("result" => "success"));
    } else {
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function add_group_form() {
    print group::get_add_form_admin();
  }

  public function delete_group($id) {
    access::verify_csrf();

    $group = group::lookup($id);
    if (empty($group)) {
      kohana::show_404();
    }

    $form = group::get_delete_form_admin($group);
    if ($form->validate()) {
      $name = $group->name;
      $group->delete();
    } else {
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }

    $message = t("Deleted group %group_name", array("group_name" => $name));
    log::success("group", $message);
    message::success($message);
    print json_encode(array("result" => "success"));
  }

  public function delete_group_form($id) {
    $group = group::lookup($id);
    if (empty($group)) {
      kohana::show_404();
    }

    print group::get_delete_form_admin($group);
  }

  public function edit_group($id) {
    access::verify_csrf();

    $group = group::lookup($id);
    if (empty($group)) {
       kohana::show_404();
    }

    $form = group::get_edit_form_admin($group);
    $valid = $form->validate();

    if ($valid) {
      $new_name = $form->edit_group->inputs["name"]->value;
      $group = group::lookup_by_name($name);
      if ($group->loaded) {
        $form->edit_group->inputs["name"]->add_error("in_use", 1);
        $valid = false;
      }
    }

    if ($valid) {
      $group->name = $form->edit_group->inputs["name"]->value;
      $group->save();
      message::success(
        t("Changed group %group_name", array("group_name" => $group->name)));
      print json_encode(array("result" => "success"));
    } else {
      message::error(
        t("Failed to change group %group_name", array("group_name" => $group->name)));
      print json_encode(array("result" => "error",
                              "form" => $form->__toString()));
    }
  }

  public function edit_group_form($id) {
    $group = group::lookup($id);
    if (empty($group)) {
      kohana::show_404();
    }

    print group::get_edit_form_admin($group);
  }

}
