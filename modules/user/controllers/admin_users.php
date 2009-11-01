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
    $view->content->users = ORM::factory("user")
      ->orderby("name", "ASC")
      ->find_all();
    $view->content->groups = ORM::factory("group")
      ->orderby("name", "ASC")
      ->find_all();
    print $view;
  }

  public function add_user() {
    access::verify_csrf();

    $form = $this->_get_user_add_form_admin();
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
    $v = new View("user_form.html");
    $v->form = $this->_get_user_add_form_admin();
    print $v;
  }

  public function delete_user($id) {
    access::verify_csrf();

    if ($id == identity::active_user()->id || $id == user::guest()->id) {
      access::forbidden();
    }

    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }

    $form = $this->_get_user_delete_form_admin($user);
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
    print $this->_get_user_delete_form_admin($user);
  }

  public function edit_user($id) {
    access::verify_csrf();

    $user = user::lookup($id);
    if (empty($user)) {
      kohana::show_404();
    }

    $form = $this->_get_user_edit_form_admin($user);
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
      if ($user->id != identity::active_user()->id) {
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

    $v = new View("user_form.html");
    $v->form = $this->_get_user_edit_form_admin($user);
    // Don't allow the user to control their own admin bit, else you can lock yourself out
    if ($user->id == identity::active_user()->id) {
      $v->form->edit_user->admin->disabled(1);
    }
    print $v;
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

    $form = $this->_get_group_add_form_admin();
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
    print $this->_get_group_add_form_admin();
  }

  public function delete_group($id) {
    access::verify_csrf();

    $group = group::lookup($id);
    if (empty($group)) {
      kohana::show_404();
    }

    $form = $this->_get_group_delete_form_admin($group);
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

    print $this->_get_group_delete_form_admin($group);
  }

  public function edit_group($id) {
    access::verify_csrf();

    $group = group::lookup($id);
    if (empty($group)) {
       kohana::show_404();
    }

    $form = $this->_get_group_edit_form_admin($group);
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

    print $this->_get_group_edit_form_admin($group);
  }

  /* User Form Definitions */
  static function _get_user_edit_form_admin($user) {
    $form = new Forge(
      "admin/users/edit_user/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $group = $form->group("edit_user")->label(t("Edit user"));
    $group->input("name")->label(t("Username"))->id("g-username")->value($user->name);
    $group->inputs["name"]->error_messages(
      "in_use", t("There is already a user with that username"));
    $group->input("full_name")->label(t("Full name"))->id("g-fullname")->value($user->full_name);
    self::_add_locale_dropdown($group, $user);
    $group->password("password")->label(t("Password"))->id("g-password");
    $group->password("password2")->label(t("Confirm password"))->id("g-password2")
      ->matches($group->password);
    $group->input("email")->label(t("Email"))->id("g-email")->value($user->email);
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url);
    $group->checkbox("admin")->label(t("Admin"))->id("g-admin")->checked($user->admin);
    $form->add_rules_from($user);
    $minimum_length = module::get_var("user", "mininum_password_length", 5);
    $form->edit_user->password
      ->rules($minimum_length ? "length[$minimum_length, 40]" : "length[40]");

    module::event("user_edit_form_admin", $user, $form);
    $group->submit("")->value(t("Modify User"));
    return $form;
  }

  static function _get_user_add_form_admin() {
    $form = new Forge("admin/users/add_user", "", "post", array("id" => "g-add-user-form"));
    $group = $form->group("add_user")->label(t("Add user"));
    $group->input("name")->label(t("Username"))->id("g-username")
      ->error_messages("in_use", t("There is already a user with that username"));
    $group->input("full_name")->label(t("Full name"))->id("g-fullname");
    $group->password("password")->label(t("Password"))->id("g-password");
    $group->password("password2")->label(t("Confirm password"))->id("g-password2")
      ->matches($group->password);
    $group->input("email")->label(t("Email"))->id("g-email");
    $group->input("url")->label(t("URL"))->id("g-url");
    self::_add_locale_dropdown($group);
    $group->checkbox("admin")->label(t("Admin"))->id("g-admin");
    $form->add_rules_from(ORM::factory("user"));

    $minimum_length = module::get_var("user", "mininum_password_length", 5);
    $form->add_user->password
      ->rules($minimum_length ? "required|length[$minimum_length, 40]" : "length[40]");

    module::event("user_add_form_admin", $user, $form);
    $group->submit("")->value(t("Add User"));
    return $form;
  }

  private function _add_locale_dropdown(&$form, $user=null) {
    $locales = locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }
    if (count($locales) > 1) {
      // Put "none" at the first position in the array
      $locales = array_merge(array("" => t("« none »")), $locales);
      $selected_locale = ($user && $user->locale) ? $user->locale : "";
      $form->dropdown("locale")
        ->label(t("Language Preference"))
        ->options($locales)
        ->selected($selected_locale);
    }
  }

  private function _get_user_delete_form_admin($user) {
    $form = new Forge("admin/users/delete_user/$user->id", "", "post",
                      array("id" => "g-delete-user-form"));
    $group = $form->group("delete_user")->label(
      t("Are you sure you want to delete user %name?", array("name" => $user->name)));
    $group->submit("")->value(t("Delete user %name", array("name" => $user->name)));
    return $form;
  }

  /* Group Form Definitions */
  private function _get_group_edit_form_admin($group) {
    $form = new Forge("admin/users/edit_group/$group->id", "", "post", array("id" => "g-edit-group-form"));
    $form_group = $form->group("edit_group")->label(t("Edit group"));
    $form_group->input("name")->label(t("Name"))->id("g-name")->value($group->name);
    $form_group->inputs["name"]->error_messages(
      "in_use", t("There is already a group with that name"));
    $form_group->submit("")->value(t("Save"));
    $form->add_rules_from($group);
    return $form;
  }

  private function _get_group_add_form_admin() {
    $form = new Forge("admin/users/add_group", "", "post", array("id" => "g-add-group-form"));
    $form->set_attr('class', "g-one-quarter");
    $form_group = $form->group("add_group")->label(t("Add group"));
    $form_group->input("name")->label(t("Name"))->id("g-name");
    $form_group->inputs["name"]->error_messages(
      "in_use", t("There is already a group with that name"));
    $form_group->submit("")->value(t("Add group"));
    $form->add_rules_from(ORM::factory("group"));
    return $form;
  }

  private function _get_group_delete_form_admin($group) {
    $form = new Forge("admin/users/delete_group/$group->id", "", "post",
                      array("id" => "g-delete-group-form"));
    $form_group = $form->group("delete_group")->label(
      t("Are you sure you want to delete group %group_name?", array("group_name" => $group->name)));
    $form_group->submit("")->value(t("Delete"));
    return $form;
  }
}
