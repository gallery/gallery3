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
class User_Controller_Password extends Controller {
  public $allow_maintenance_mode = true;
  public $allow_private_gallery = true;

  /**
   * Password reset request form.  This is given to users that click on "Forgot your password?".
   * It gets the username and sends them an email.
   */
  public function action_reset() {
    $form = Formo::form()
      ->attr("id", "g-reset-form")
      ->add("reset", "group");
    $form->reset
      ->set("label", t("Reset Password"))
      ->add("username", "input")
      ->add("submit", "input|submit", t("Reset"));
    $form->reset->username
      ->attr("id", "g-name")
      ->set("label", t("Username"))
      ->add_rule("not_empty", array(":value"), t("You must enter a user name"));

    if ($form->sent()) {
      if ($form->load()->validate()) {
        $user = User::lookup_by_name($form->reset->username->val());
        if ($user && !empty($user->email)) {
          $user->hash = Random::hash();
          $user->save();
          $message = new View("user/reset_password.html");
          $message->confirm_url = URL::abs_site("password/do_reset?key=$user->hash");
          $message->user = $user;

          Sendmail::factory()
            ->to($user->email)
            ->subject(t("Password Reset Request"))
            ->header("Mime-Version", "1.0")
            ->header("Content-type", "text/html; charset=UTF-8")
            ->message($message->render())
            ->send();

          GalleryLog::success(
            "user", t("Password reset email sent for user %name", array("name" => $user->name)));
        } else if (!$user) {
          // Don't include the username here until you're sure that it's XSS safe
          GalleryLog::warning("user",
            t("Password reset email requested for user %user_name, which does not exist.",
              array("user_name" => HTML::purify($form->reset->username->val()))));
        } else {
          GalleryLog::warning("user",
            t("Password reset failed for %user_name (has no email address on record).",
              array("user_name" => $user->name)));
        }

        // Always pretend that an email has been sent to avoid leaking
        // information on what user names are actually real.
        Message::success(t("Password reset email sent"));
        $this->response->json(array("result" => "success"));
      } else {
        $this->response->json(array("result" => "error", "html" => (string)$form));
      }
      return;
    }

    $this->response->body($form);
  }

  /**
   * Password reset form.  Users should get here by following the link sent to them as a result
   * of filling out the reset password request form above, which must have a "?key=12345" query.
   */
  public function action_do_reset() {
    $min_length = max(1, (int)Module::get_var("user", "minimum_password_length", 5));

    $form = Formo::form()
      ->attr("id", "g-change-password-form")
      ->add("reset", "group");
    $form->reset
      ->set("label", t("Change Password"))
      ->add("hash", "input|hidden")
      ->add("password", "input|password")
      ->add("password2", "input|password")
      ->add("submit", "input|submit", t("Update"));
    $form->reset->password
      ->attr("id", "g-password")
      ->set("label", t("Password"))
      ->add_rule("min_length", array(":value", $min_length), t("This password is too short"))
      ->add_rule("max_length", array(":value", 40),          t("This password is too long"));
    $form->reset->password2
      ->attr("id", "g-password2")
      ->set("label", t("Confirm Password"))
      ->add_rule("matches", array(":form_val", "password", "password2"),
        t("The password and the confirm password must match"));

    if ($form->sent()) {
      if ($form->load()->validate()) {
        $user = User::lookup_by_hash($form->reset->hash->val());
        if (empty($user)) {
          throw HTTP_Exception::factory(403);
        }

        $user->password = $form->reset->password->val();
        $user->hash = null;
        $user->save();
        Message::success(t("Password reset successfully"));

        $this->redirect(Item::root()->abs_url());
      }
    } else {
      // Form not yet sent - get the hash from the query key (should be in email sent to user)
      $user = User::lookup_by_hash($this->request->query("key"));
      if (empty($user)) {
        throw HTTP_Exception::factory(403);
      }

      $form->reset->hash->val($user->hash);
    }

    $view = new View_Theme("required/page.html", "other", "reset");
    $view->content = $form;
    $this->response->body($view);
  }
}
