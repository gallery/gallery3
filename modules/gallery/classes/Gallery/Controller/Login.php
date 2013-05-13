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
class Gallery_Controller_Login extends Controller {
  public $allow_maintenance_mode = true;
  public $allow_private_gallery = true;

  public function action_index() {
    // By design, this bears a strong resemblance to the reauthenticate controller.
    // For details on the differences, see the notes in the reauthenticate controller.

    // Define our login form.
    $form = Formo::form()
      ->attr("id", "g-login-form")
      ->add_class("g-narrow")
      ->add_script_text(
        // Setting the focus when ready doesn't always work with IE7, perhaps because the field is
        // not ready yet?  So set a timeout and do it the next time we're idle.
          '$("#g-login-form").ready(function() {
            setTimeout(\'$("#g-username").focus()\', 100);
          });'
        )
      ->add("continue_url", "input|hidden", Session::instance()->get_once("continue_url"))
      ->add("login", "group");
    $form->login
      ->set("label", t("Login"))
      ->add("username", "input")
      ->add("password", "input|password")
      ->add("submit", "input|submit", t("Login"));
    $form->login->username
      ->attr("id", "g-username")
      ->set("label", t("Username"))
      ->add_rule("Auth::validate_login", array(":validation", ":form_val", "username", "password"))
      ->set("error_messages", static::get_login_error_messages());
    $form->login->password
      ->attr("id", "g-password")
      ->set("label", t("Password"));

    Module::event("user_login_form", $form);

    if ($form->sent()) {
      // Login attempted - regenerate the session id to avoid session trapping.
      Session::instance()->regenerate();
    }

    if ($form->load()->validate()) {
      Module::event("user_login_form_completed", $form);
      $continue_url = $form->continue_url->val();
      $form->set("response", $continue_url ? $continue_url : Item::root()->abs_url());
    }

    $this->response->ajax_form($form);
  }

  public static function get_login_error_messages() {
    return array(
      "invalid"           => t("Invalid name or password"),
      "too_many_failures" => t("Too many failed login attempts.  Try again later")
    );
  }
}