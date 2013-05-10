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
    // - the username field is hidden with a pre-filled value, and has rules that enforce it's the
    //   current user
    // - the main validation rules have moved from username to password so errors are shown there
    // - the field labels are different
    // - the username is added to the view
    // - some ajax replies are different (@todo: try and unify this)

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
      ->add_class("g-narrow")
      ->add("continue_url", "input|hidden", Session::instance()->get("continue_url"))
      ->add("reauthenticate", "group");
    $form->reauthenticate
      ->set("label", t("Re-authenticate"))
      ->add("username", "input|hidden", Identity::active_user()->name)
      ->add("password", "input|password")
      ->add("submit", "input|submit", t("Submit"));
    $form->reauthenticate->username
      ->set("can_be_empty", true)
      ->add_rule("not_empty", array(":value"))
      ->add_rule("equals", array(":value", $user->name))
      ->callback("fail", array("Access::forbidden"));
    $form->reauthenticate->password
      ->attr("id", "g-password")
      ->set("label", t("Password"))
      ->add_rule("not_empty", array(":value"), t("Incorrect password"))
      ->add_rule("Auth::validate_too_many_failed_logins", array(":form_val", "username"),
                 t("Too many incorrect passwords.  Try again later"))
      ->add_rule("Auth::validate_username_and_password", array(":form_val", "username", "password"),
                 t("Incorrect password"));

    // Define our basic form view.
    $view = new View("gallery/reauthenticate.html");
    $view->form = $form;
    $view->username = $form->reauthenticate->username->val();

    if ($form->sent()) {
      // Reauthenticate attempted - regenerate the session id to avoid session trapping.
      Session::instance()->regenerate();

      if ($form->load()->validate()) {
        // Reauthenticate attempt is valid.
        Auth::reauthenticate($user);

        if ($this->request->is_ajax()) {
          // @todo: make reauthenticate and login use the same type of response here
          $continue_url = $form->continue_url->val();
          $this->redirect($continue_url ? $continue_url : Item::root()->abs_url());
        } else {
          $continue_url = $form->continue_url->val();
          $this->redirect($continue_url ? $continue_url : Item::root()->abs_url());
        }
      } else {
        // Reauthenticate attempt is invalid.
        $name = $form->reauthenticate->username->val();
        GalleryLog::warning("user", t("Failed re-authentication for %name", array("name" => $name)));
        Module::event("user_auth_failed", $name);

        if ($this->request->is_ajax()) {
          // @todo: make reauthenticate and login use the same type of response here
          $this->response->json(array("html" => (string)$view));
          return;
        }
      }
    }

    // Reauthenticate not yet attempted (ajax or non-ajax) or reauthenticate failed (non-ajax).
    if ($this->request->is_ajax()) {
      // Send the basic reauthenticate view.
      $this->response->body($view);
    } else {
      // Wrap the basic reauthenticate view in a theme.
      $view_theme = new View_Theme("required/page.html", "other", "reauthenticate");
      $view_theme->page_title = t("Re-authenticate");
      $view_theme->content = $view;
      $this->response->body($view_theme);
    }
  }
}
