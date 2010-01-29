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
class Users_Controller extends Controller {
  public function update($id) {
    $user = user::lookup($id);

    if ($user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $form = $this->_get_edit_form($user);
    try {
      $valid = $form->validate();
      $user->full_name = $form->edit_user->full_name->value;
      $user->password = $form->edit_user->password->value;
      $user->email = $form->edit_user->email->value;
      $user->url = $form->edit_user->url->value;

      if ($user->locale !=  $form->edit_user->locale->value) {
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
      message::success(t("User information updated."));
      print json_encode(
        array("result" => "success",
              "resource" => url::site("users/{$user->id}")));
    } else {
      print json_encode(array("result" => "error", "form" => (string) $form));
    }
  }

  public function form_edit($id) {
    $user = user::lookup($id);
    if ($user->guest || $user->id != identity::active_user()->id) {
      access::forbidden();
    }

    $v = new View("user_form.html");
    $v->form = $this->_get_edit_form($user);
    print $v;
  }

  private function _get_edit_form($user) {
    $form = new Forge("users/update/$user->id", "", "post", array("id" => "g-edit-user-form"));
    $group = $form->group("edit_user")->label(t("Edit User: %name", array("name" => $user->name)));
    $group->input("full_name")->label(t("Full Name"))->id("g-fullname")->value($user->full_name)
      ->error_messages("length", t("Your name is too long"));
    self::_add_locale_dropdown($group, $user);
    $group->password("password")->label(t("Password"))->id("g-password")
      ->error_messages("min_length", t("Your password is too short"));
    $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
      ->matches($group->password)
      ->error_messages("matches", t("The passwords you entered do not match"));
    $group->input("email")->label(t("Email"))->id("g-email")->value($user->email)
      ->error_messages("email", t("You must enter a valid email address"))
      ->error_messages("required", t("You must enter a valid email address"));
    $group->input("url")->label(t("URL"))->id("g-url")->value($user->url);

    module::event("user_edit_form", $user, $form);
    $group->submit("")->value(t("Save"));
    return $form;
  }

  /** @todo combine with Admin_Users_Controller::_add_locale_dropdown */
  private function _add_locale_dropdown(&$form, $user=null) {
    $locales = locales::installed();
    foreach ($locales as $locale => $display_name) {
      $locales[$locale] = SafeString::of_safe_html($display_name);
    }

    // Put "none" at the first position in the array
    $locales = array_merge(array("" => t("« none »")), $locales);
    $selected_locale = ($user && $user->locale) ? $user->locale : "";
    $form->dropdown("locale")
      ->label(t("Language Preference"))
      ->options($locales)
      ->selected($selected_locale);
  }
}
