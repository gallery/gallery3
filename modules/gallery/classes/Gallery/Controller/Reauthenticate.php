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
class Gallery_Controller_Reauthenticate extends Controller {
  public function action_index() {
    // By design, this bears a strong resemblance to the login controller.  Aside from a
    // search'n'replace for login and reauthenticate, the differences are that:
    // - it's restricted to admins
    // - it's fixed to the current user
    // - it doesn't need allow_maintenance_mode or allow_private_gallery, which would be
    //   redundant with the admin-only restriction
    // - the main validation rules have moved from username to password so errors are shown there

    $user = Identity::active_user();

    if (!$user->admin) {
      if ($this->request->is_ajax()) {
        // We should never be able to get here since the admin reauth_check
        // won't work for non-admins.
        Access::forbidden();
      } else {
        // The user could have navigated here directly.  This isn't a security
        // breach, but they still shouldn't be here.
        $this->redirect(Item::root()->abs_url());
      }
    }

    // Define our reauthenticate form.
    $form = Formo::form()
      ->attr("id", "g-reauthenticate-form")
      ->html(array(
          t("The administration session has expired, please re-authenticate to access the administration area."),
          t("You are currently logged in as %user_name.", array("user_name" => $user->name))
        ))
      ->add_script_text(
          '$("#g-reauthenticate-form").ready(function() {
            $("#g-password").focus();
          });'
        )
      ->add("continue_url", "input|hidden", Session::instance()->get_once("continue_url"))
      ->add("reauthenticate", "group");
    $form->reauthenticate
      ->set("label", t("Re-authenticate"))
      ->add("password", "input|password")
      ->add("submit", "input|submit", t("Submit"));
    $form->reauthenticate->password
      ->attr("id", "g-password")
      ->set("label", t("Password"))
      ->add_rule("Auth::validate_reauthenticate", array(":validation", ":field", ":value"))
      ->set("error_messages", static::get_reauthenticate_error_messages());

    Module::event("user_reauthenticate_form", $form);

    if ($form->sent()) {
      // Reauthenticate attempted - regenerate the session id to avoid session trapping.
      Session::instance()->regenerate();
    }

    if ($form->load()->validate()) {
      Module::event("user_reauthenticate_form_completed", $form);
      $continue_url = $form->continue_url->val();
      $form->set("response", $continue_url ? $continue_url : Item::root()->abs_url());
    }

    $this->response->ajax_form($form);
  }

  public static function get_reauthenticate_error_messages() {
    return array(
      "invalid"           => t("Incorrect password"),
      "too_many_failures" => t("Too many incorrect passwords.  Try again later")
    );
  }
}
