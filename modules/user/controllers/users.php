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
class Users_Controller extends Controller {
  public function update($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $form = $this->_get_edit_form($user);
    try {
      $valid = $form->validate();
      $user->full_name = $form->edit_user->full_name->value;
      $user->url = $form->edit_user->url->value;

      if (count(locales::installed()) > 1 &&
          $user->locale != $form->edit_user->locale->value) {
        $user->locale = $form->edit_user->locale->value;
        $flush_locale_cookie = true;
      }

      $user->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_user->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      if (isset($flush_locale_cookie)) {
        // Delete the session based locale preference
        setcookie("g_locale", "", time() - 24 * 3600, "/");
      }

      $user->save();
      module::event("user_edit_form_completed", $user, $form);
      message::success(t("User information updated"));
      json::reply(array("result" => "success",
                        "resource" => url::site("users/{$user->id}")));
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function change_password($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $form = $this->_get_change_password_form($user);
    try {
      $valid = $form->validate();
      $user->password = $form->change_password->password->value;
      $user->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->change_password->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $user->save();
      module::event("user_change_password_form_completed", $user, $form);
      message::success(t("Password changed"));
      module::event("user_auth", $user);
      module::event("user_password_change", $user);
      json::reply(array("result" => "success",
                        "resource" => url::site("users/{$user->id}")));
    } else {
      log::warning("user", t("Failed password change for %name", array("name" => $user->name)));
      $name = $user->name;
      module::event("user_auth_failed", $name);
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function change_email($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $form = $this->_get_change_email_form($user);
    try {
      $valid = $form->validate();
      $user->email = $form->change_email->email->value;
      $user->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->change_email->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $user->save();
      module::event("user_change_email_form_completed", $user, $form);
      message::success(t("Email address changed"));
      module::event("user_auth", $user);
      json::reply(array("result" => "success",
                        "resource" => url::site("users/{$user->id}")));
    } else {
      log::warning("user", t("Failed email change for %name", array("name" => $user->name)));
      $name = $user->name;
      module::event("user_auth_failed", $name);
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function form_edit($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    print $this->_get_edit_form($user);
  }

  public function form_change_password($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    print $this->_get_change_password_form($user);
  }

  public function form_change_email($id) {
    $user = user::lookup($id);
    if (!$user || $user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    print $this->_get_change_email_form($user);
  }

  private function _get_change_password_form($user) {
    $form = new Forge(
      "users/change_password/$user->id", "", "post", array("id" => "g-change-password-user-form"));
    $group = $form->group("change_password")->label(t("Change your password"));
    $group->password("old_password")->label(t("Old password"))->id("g-password")
      ->callback("auth::validate_too_many_failed_auth_attempts")
      ->callback("user::valid_password")
      ->error_messages("invalid_password", t("Incorrect password"))
      ->error_messages(
        "too_many_failed_auth_attempts",
        t("Too many incorrect passwords.  Try again later"));
    $group->password("password")->label(t("New password"))->id("g-password")
      ->error_messages("min_length", t("Your new password is too short"));
    $group->script("")
      ->text(
        '$("form").ready(function(){$(\'input[name="password"]\').user_password_strength();});');
    $group->password("password2")->label(t("Confirm new password"))->id("g-password2")
      ->matches($group->password)
      ->error_messages("matches", t("The passwords you entered do not match"));

    module::event("user_change_password_form", $user, $form);
    $group->submit("")->value(t("Save"));
    return $form;
  }

  private function _get_change_email_form($user) {
    $form = new Forge(
      "users/change_email/$user->id", "", "post", array("id" => "g-change-email-user-form"));
    $group = $form->group("change_email")->label(t("Change your email address"));
    $group->password("password")->label(t("Current password"))->id("g-password")
      ->callback("auth::validate_too_many_failed_auth_attempts")
      ->callback("user::valid_password")
      ->error_messages("invalid_password", t("Incorrect password"))
      ->error_messages(
        "too_many_failed_auth_attempts",
        t("Too many incorrect passwords.  Try again later"));
    $group->input("email")->label(t("New email address"))->id("g-email")->value($user->email)
      ->error_messages("email", t("You must enter a valid email address"))
      ->error_messages("length", t("Your email address is too long"))
      ->error_messages("required", t("You must enter a valid email address"));

    module::event("user_change_email_form", $user, $form);
    $group->submit("")->value(t("Save"));
    return $form;
  }

  private function _get_edit_form($user) {
    $form = new Forge("users/update/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $group = $form->group("edit_user")->label(t("Edit your profile"));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname")->value($user->full_name)
      ->error_messages("length", t("Your name is too long"));
    self::_add_locale_dropdown($group, $user);
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url)
      ->error_messages("url", t("You must enter a valid url"));

    module::event("user_edit_form", $user, $form);
    $group->submit("")->value(t("Save"));
    return $form;
  }

  /** @todo combine with Admin_Users_Controller::_add_locale_dropdown */
  private function _add_locale_dropdown(&$form, $user=null) {
    $locales = locales::installed();
    if (count($locales) <= 1) {
      return;
    }

    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }

    // Put "none" at the first position in the array
    $locales = array_merge(array("" => t("« none »")), $locales);
    $selected_locale = ($user && $user->locale) ? $user->locale : "";
    $form->dropdown("locale")
      ->label(t("Language preference"))
      ->options($locales)
      ->selected($selected_locale);
  }
}
