<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
class User_Controller_Admin_Users extends Controller_Admin {
  /**
   * Show the users admin screen.  Users and groups are managed from here.
   */
  public function action_index() {
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Users and groups");
    $view->page_type = "collection";
    $view->page_subtype = "admin_users";

    $view->set_global(array(
      "page_size" => Module::get_var("user", "page_size", 10),  // @todo: add this as a config option
      "collection_query_callback" => array("Controller_Admin_Users::get_users_query", array())
    ));
    $view->init_collection();

    $view->content = new View("admin/users.html");
    $view->content->groups = ORM::factory("Group")
      ->order_by("name", "ASC")
      ->find_all();

    $this->response->body($view);
  }

  /**
   * Add a new user.  This generates the form, validates it, adds the user, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_add_user() {
    $user = ORM::factory("User");

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-add-user-form")
      ->add_script_text(static::get_password_strength_script())
      ->add("user", "group")
      ->add("other", "group");
    $form->user
      ->set("label", t("Add user"))
      ->add("name", "input")
      ->add("full_name", "input")
      ->add("password", "input|password")
      ->add("password2", "input|password")
      ->add("email", "input")
      ->add("url", "input")
      ->add("admin", "checkbox")
      ->add("locale", "select");
    $form->user->password2
      ->add_rule("matches", array(":form_val", "password", "password2"));
    $form->user->locale
      ->set("opts", static::get_locale_options());
    $form->other
      ->add("submit", "input|submit", t("Add user"));

    // Get the labels and error messages, link the ORM model, and call the form event.
    $form->user->orm("link", array("model" => $user));
    $form->user->set_var_fields("label", static::get_user_form_labels());
    $form->user->set_var_fields("error_messages", static::get_user_form_error_messages());
    Module::event("user_add_form_admin", $user, $form);

    if ($form->load()->validate()) {
      $user->save();
      Module::event("user_add_form_admin_completed", $user, $form);
      Message::success(t("Created user %user_name", array("user_name" => $user->name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "user");

    $this->response->ajax_form($form);
  }

  /**
   * Delete a user.  This generates the confirmation form, validates it,
   * deletes the user, and returns a response.
   */
  public function action_delete_user() {
    $id = $this->request->arg(0, "digit");
    $user = User::lookup($id);
    if (empty($user)) {
      throw HTTP_Exception::factory(404);
    }

    // You cannot delete yourself or the guest user.
    if ($id == Identity::active_user()->id || $id == User::guest()->id) {
      throw HTTP_Exception::factory(403);
    }

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-delete-user-form")
      ->add("confirm", "group");
    $form->confirm
      ->set("label", t("Delete user %name?", array("name" => $user->display_name())))
      ->html(
          t("Really delete <b>%name</b>?  Any photos, movies or albums owned by this user will transfer ownership to <b>%new_owner</b>.",
            array("name"      => $user->display_name(),
                  "new_owner" => Identity::active_user()->display_name()
        )))
      ->add("submit", "input|submit", t("Delete"));

    if ($form->load()->validate()) {
      $message = t("Deleted user %user_name", array("user_name" => $user->name));
      $user->delete();
      GalleryLog::success("user", $message);
      Message::success($message);
    }

    $this->response->ajax_form($form);
  }

  /**
   * Edit a user.  This generates the form, validates it, edits the user, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit_user() {
    $id = $this->request->arg(0, "digit");
    $user = User::lookup($id);
    if (empty($user)) {
      throw HTTP_Exception::factory(404);
    }

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-edit-user-form")
      ->add_script_text(static::get_password_strength_script())
      ->add("user", "group")
      ->add("other", "group");
    $form->user
      ->set("label", t("Edit user"))
      ->add("name", "input")
      ->add("full_name", "input")
      ->add("password", "input|password")
      ->add("password2", "input|password")
      ->add("email", "input")
      ->add("url", "input")
      ->add("admin", "checkbox")
      ->add("locale", "select");
    $form->user->password2
      ->add_rule("matches", array(":form_val", "password", "password2"));
    $form->user->locale
      ->set("opts", static::get_locale_options());
    $form->other
      ->add("submit", "input|submit", t("Modify user"));

    // Get the labels and error messages, link the ORM model, and call the form event.
    $form->user->orm("link", array("model" => $user, "write_only" => array("password")));
    $form->user->set_var_fields("label", static::get_user_form_labels());
    $form->user->set_var_fields("error_messages", static::get_user_form_error_messages());
    Module::event("user_edit_form_admin", $user, $form);

    // Don't allow the user to control their own admin bit, else you can lock yourself out
    if ($user->id == Identity::active_user()->id) {
      $form->user->admin->attr("disabled", "disabled");
    }

    if ($form->load()->validate()) {
      $user->save();
      Module::event("user_edit_form_admin_completed", $user, $form);
      Message::success(t("Changed user %user_name", array("user_name" => $user->name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "user");

    $this->response->ajax_form($form);
  }

  /**
   * Add a user to a group.  There is no form with this action.
   */
  public function action_add_user_to_group() {
    $user_id = $this->request->arg(0, "digit");
    $group_id = $this->request->arg(1, "digit");
    Access::verify_csrf();

    $user = User::lookup($user_id);
    $group = Group::lookup($group_id);
    if (empty($user) || empty($group) || $group->has("users", $user_id)) {
      throw HTTP_Exception::factory(404);
    }

    $group->add("users", $user);
    $group->save();
  }

  /**
   * Remove a user from a group.  There is no form with this action.
   */
  public function action_remove_user_from_group() {
    $user_id = $this->request->arg(0, "digit");
    $group_id = $this->request->arg(1, "digit");
    Access::verify_csrf();

    $user = User::lookup($user_id);
    $group = Group::lookup($group_id);
    if (empty($user) || empty($group) || !$group->has("users", $user_id)) {
      throw HTTP_Exception::factory(404);
    }

    $group->remove("users", $user);
    $group->save();
  }

  /**
   * Show a group.  This is accessed as a sub-request and by link in the main users view.
   */
  public function action_show_group() {
    $group_id = $this->request->arg(0, "digit");
    $view = new View("admin/users_group.html");
    $view->group = Group::lookup($group_id);
    $this->response->body($view);
  }

  /**
   * Add a new group.  This generates the form, validates it, adds the group, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_add_group() {
    $group = ORM::factory("Group");

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-add-group-form")
      ->add("group", "group")
      ->add("other", "group");
    $form->group
      ->set("label", t("Add group"))
      ->add("name", "input");
    $form->group->name
      ->set("label", t("Name"))
      ->set("error_messages", array(
          "not_empty"  => t("You must enter a group name"),
          "conflict"   => t("There is already a group with that name"),
          "max_length" => t("The group name must be less than %max_length characters",
                            array("max_length" => 255))
        ));
    $form->other
      ->add("submit", "input|submit", t("Add group"));

    // Link the ORM model
    $form->group->orm("link", array("model" => $group));

    if ($form->load()->validate()) {
      $group->save();
      Message::success(t("Created group %group_name", array("group_name" => $group->name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "group");

    $this->response->ajax_form($form);
  }

  /**
   * Delete a group.  This generates the confirmation form, validates it,
   * deletes the group, and returns a response.
   */
  public function action_delete_group() {
    $id = $this->request->arg(0, "digit");
    $group = Group::lookup($id);
    if (empty($group)) {
      throw HTTP_Exception::factory(404);
    }

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-delete-group-form")
      ->add("confirm", "group");
    $form->confirm
      ->set("label", t("Confirm Deletion"))
      ->html(t("Are you sure you want to delete group %group_name?",
               array("group_name" => $group->name)))
      ->add("submit", "input|submit", t("Delete"));

    if ($form->load()->validate()) {
      $message = t("Deleted group %group_name", array("group_name" => $group->name));
      $group->delete();
      GalleryLog::success("group", $message);
      Message::success($message);
    }

    $this->response->ajax_form($form);
  }

  /**
   * Edit a group.  This generates the form, validates it, edits the group, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit_group() {
    $id = $this->request->arg(0, "digit");
    $group = Group::lookup($id);
    if (empty($group)) {
      throw HTTP_Exception::factory(404);
    }

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-edit-group-form")
      ->add("group", "group")
      ->add("other", "group");
    $form->group
      ->set("label", t("Edit group"))
      ->add("name", "input");
    $form->group->name
      ->set("label", t("Name"))
      ->set("error_messages", array(
          "not_empty"  => t("You must enter a group name"),
          "conflict"   => t("There is already a group with that name"),
          "max_length" => t("The group name must be less than %max_length characters",
                            array("max_length" => 255))
        ));
    $form->other
      ->add("submit", "input|submit", t("Save"));

    // Link the ORM model
    $form->group->orm("link", array("model" => $group));

    if ($form->load()->validate()) {
      $group->save();
      Message::success(t("Changed group %group_name", array("group_name" => $group->name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "group");

    $this->response->ajax_form($form);
  }

  /**
   * Get the query for the user collection view.
   * @see  Controller_Admin_Users::action_index()
   */
  static function get_users_query() {
    return ORM::factory("User")->order_by("name", "ASC");
  }

  /**
   * Get user form labels.  This is a helper function for the edit/add forms.
   */
  public static function get_user_form_labels() {
    return array(
      "name"      => t("Username"),
      "full_name" => t("Full name"),
      "password"  => t("Password"),
      "password2" => t("Confirm password"),
      "email"     => t("Email"),
      "url"       => t("URL"),
      "locale"    => t("Language preference"),
      "admin"     => t("Admin")
    );
  }

  /**
   * Get user form error messages.  This is a helper function for the edit/add forms.
   */
  public static function get_user_form_error_messages() {
    return array(
      "name"      => array("not_empty"  => t("A name is required"),
                           "length"     => t("This name is too long"),
                           "conflict"   => t("There is already a user with that username")),
      "full_name" => array("length"     => t("This name is too long")),
      "password"  => array("min_length" => t("This password is too short")),
      "password2" => array("matches"    => t("The passwords you entered do not match")),
      "email"     => array("not_empty"  => t("You must enter a valid email address"),
                           "length"     => t("This email address is too long"),
                           "email"      => t("You must enter a valid email address")),
      "url"       => array("url"        => t("You must enter a valid URL"))
    );
  }

  /**
   * Return a structured set of all the possible locales.
   */
  public static function get_locale_options() {
    $locales = Locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }

    // Put "none" at the first position in the array
    $locales = array_merge(array("" => t("« none »")), $locales);

    return $locales;
  }

  /**
   * Get the script to activate the password strength indicator.
   */
  public static function get_password_strength_script() {
    return '$("form").ready(function() {
              $(\'input[name="password"]\').user_password_strength();
            });';
  }
}
