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
      ->add("continue_url", "input|hidden", Session::instance()->get("continue_url"))
      ->add("login", "group");
    $form->login
      ->set("label", t("Login"))
      ->add("username", "input")
      ->add("password", "input|password")
      ->add("submit", "input|submit", t("Login"));
    $form->login->username
      ->attr("id", "g-username")
      ->set("label", t("Username"))
      ->add_rule("Auth::validate_too_many_failed_logins", array(":form_val", "username"),
                 t("Too many failed login attempts.  Try again later"))
      ->add_rule("Auth::validate_username_and_password", array(":form_val", "username", "password"),
                 t("Invalid name or password"));
    $form->login->password
      ->attr("id", "g-password")
      ->set("label", t("Password"));

    // Define our basic form view.
    $view = new View("gallery/login.html");
    $view->form = $form;

    if ($form->sent()) {
      // Login attempted - regenerate the session id to avoid session trapping.
      Session::instance()->regenerate();

      if ($form->load()->validate()) {
        // Login attempt is valid.
        $user = Identity::lookup_user_by_name($form->login->username->val());
        Auth::login($user);

        if ($this->request->is_ajax()) {
          $this->response->json(array("result" => "success"));
          return;
        } else {
          $continue_url = $form->continue_url->val();
          $this->redirect($continue_url ? $continue_url : Item::root()->abs_url());
        }
      } else {
        // Login attempt is invalid.
        $name = $form->login->username->val();
        GalleryLog::warning("user", t("Failed login for %name", array("name" => $name)));
        Module::event("user_auth_failed", $name);

        if ($this->request->is_ajax()) {
          $this->response->json(array("result" => "error", "html" => (string)$view));
          return;
        }
      }
    }

    // Login not yet attempted (ajax or non-ajax) or login failed (non-ajax).
    if ($this->request->is_ajax()) {
      // Send the basic login view.
      $this->response->body($view);
    } else {
      // Wrap the basic login view in a theme.
      $view_theme = new View_Theme("required/page.html", "other", "login");
      $view_theme->page_title = t("Log in to Gallery");
      $view_theme->content = $view;
      $this->response->body($view_theme);
    }
  }
}