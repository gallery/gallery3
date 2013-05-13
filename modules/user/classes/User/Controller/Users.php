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
class User_Controller_Users extends Controller {
  /**
   * Edit a user.  This generates the form, validates it, edits the user, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   */
  public function action_edit() {
    $user_id = $this->request->arg(0, "digit");
    $user = User::lookup($user_id);
    if (empty($user) || $user->guest || $user->id != Identity::active_user()->id) {
      Access::forbidden();
    }

    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-edit-user-form")
      ->add("user", "group")
      ->add("other", "group");
    $form->user
      ->set("label", t("Edit your profile"))
      ->add("name", "input")
      ->add("full_name", "input")
      ->add("url", "input")
      ->add("locale", "select");
    $form->user->locale
      ->set("opts", Controller_Admin_Users::get_locale_options());
    $form->other
      ->add("submit", "input|submit", t("Save"));

    // Get the labels and error messages for the user group.
    Controller_Admin_Users::get_user_form_labels($form->user);
    Controller_Admin_Users::get_user_form_error_messages($form->user);

    // Link the ORM model and call the form event.
    $form->user->orm("link", array("model" => $user));
    Module::event("user_edit_form", $user, $form);

    if ($form->load()->validate()) {
      if ($user->changed("locale")) {
        // Can't use Request or Cookie objects for client side cookies since
        // they're not signed.
        setcookie("g_locale", "", time() - 24 * 3600, "/");
      }
      $user->save();
      Module::event("user_edit_form_completed", $user, $form);
      Message::success(t("User information updated"));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "user");

    $this->response->ajax_form($form);
  }

  /**
   * Change a user's password.  This form requires reauthentification to be processed.
   * This generates the form, validates it, edits the user, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   *
   * @see Controller_Reauthenticate::index()
   */
  public function action_change_password() {
    $user_id = $this->request->arg(0, "digit");
    $user = User::lookup($user_id);
    if (empty($user) || $user->guest || $user->id != Identity::active_user()->id) {
      Access::forbidden();
    }

    $form = Formo::form()
      ->attr("id", "g-change-password-user-form")
      ->add_script_text(Controller_Admin_Users::get_password_strength_script())
      ->add("user", "group")
      ->add("other", "group");
    $form->user
      ->set("label", t("Change your password"))
      ->add("name", "input|hidden")
      ->add("password_check", "input|password")
      ->add("password", "input|password")
      ->add("password2", "input|password");
    $form->user->password
      ->set("label", t("New password"));
    $form->user->password2
      ->set("label", t("Confirm new password"))
      ->add_rule("matches", array(":form_val", "password", "password2"));
    $form->other
      ->add("submit", "input|submit", t("Save"));

    // Get the error messages, link the ORM model, and call the form event.
    $form->user->orm("link", array("model" => $user));
    $form->user->set_var_fields("error_messages",
      Controller_Admin_Users::get_user_form_error_messages());
    Module::event("user_change_password_form", $user, $form);

    // Add reauthentication-related details (largely copied from Controller_Reauthenticate)
    $form->user->name
      ->set("can_be_empty", true)
      ->add_rule("not_empty", array(":value"))
      ->add_rule("equals", array(":value", $user->name))
      ->callback("fail", array("Access::forbidden"));
    $form->user->password_check
      ->set("label", t("Old password"))
      ->add_rule("not_empty", array(":value"), t("Incorrect password"))
      ->add_rule("Auth::validate_too_many_failed_logins", array(":form_val", "name"),
                 t("Too many incorrect passwords.  Try again later"))
      ->add_rule("Auth::validate_username_and_password", array(":form_val", "name", "password_check"),
                 t("Incorrect password"));

    if ($form->load()->validate()) {
      // Reauthenticate attempt is valid.
      Auth::reauthenticate($user);

      $user->save();
      Module::event("user_change_password_form_completed", $user, $form);
      Module::event("user_password_change", $user);
      Message::success(t("Password changed"));
    } else if ($form->user->password_check->error()) {
      // Reauthenticate attempt is invalid.
      $name = $user->name;
      Module::event("user_auth_failed", $name);
      GalleryLog::warning("user", t("Failed password change for %name", array("name" => $name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "user");

    $this->response->ajax_form($form);
  }

  /**
   * Change a user's email.  This form requires reauthentification to be processed.
   * This generates the form, validates it, edits the user, and returns a response.
   * This can be used as an ajax dialog (preferable) or a normal view.
   *
   * @see Controller_Reauthenticate::index()
   */
  public function action_change_email() {
    $user_id = $this->request->arg(0, "digit");
    $user = User::lookup($user_id);
    if (empty($user) || $user->guest || $user->id != Identity::active_user()->id) {
      Access::forbidden();
    }

    $form = Formo::form()
      ->attr("id", "g-change-email-user-form")
      ->add("user", "group")
      ->add("other", "group");
    $form->user
      ->set("label", t("Change your email address"))
      ->add("name", "input|hidden")
      ->add("password_check", "input|password")
      ->add("email", "input");
    $form->user->email
      ->set("label", t("New email address"));
    $form->other
      ->add("submit", "input|submit", t("Save"));

    // Get the error messages, link the ORM model, and call the form event.
    $form->user->orm("link", array("model" => $user));
    $form->user->set_var_fields("error_messages",
      Controller_Admin_Users::get_user_form_error_messages());
    Module::event("user_change_email_form", $user, $form);

    // Add reauthentication-related details (largely copied from Controller_Reauthenticate)
    $form->user->name
      ->set("can_be_empty", true)
      ->add_rule("not_empty", array(":value"))
      ->add_rule("equals", array(":value", $user->name))
      ->callback("fail", array("Access::forbidden"));
    $form->user->password_check
      ->set("label", t("Current password"))
      ->add_rule("not_empty", array(":value"), t("Incorrect password"))
      ->add_rule("Auth::validate_too_many_failed_logins", array(":form_val", "name"),
                 t("Too many incorrect passwords.  Try again later"))
      ->add_rule("Auth::validate_username_and_password", array(":form_val", "name", "password_check"),
                 t("Incorrect password"));

    if ($form->load()->validate()) {
      // Reauthenticate attempt is valid.
      Auth::reauthenticate($user);

      $user->save();
      Module::event("user_change_email_form_completed", $user, $form);
      Message::success(t("Email address changed"));
    } else if ($form->user->password_check->error()) {
      // Reauthenticate attempt is invalid.
      $name = $user->name;
      Module::event("user_auth_failed", $name);
      GalleryLog::warning("user", t("Failed email change for %name", array("name" => $name)));
    }

    // Merge the groups together for presentation purposes
    $form->merge_groups("other", "user");

    $this->response->ajax_form($form);
  }
}
