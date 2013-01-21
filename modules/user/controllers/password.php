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
class Password_Controller extends Controller {
  const ALLOW_MAINTENANCE_MODE = true;
  const ALLOW_PRIVATE_GALLERY = true;

  public function reset() {
    $form = self::_reset_form();
    if (request::method() == "post") {
      // @todo separate the post from get parts of this function
      access::verify_csrf();
      // Basic validation (was some user name specified?)
      if ($form->validate()) {
        $this->_send_reset($form);
      } else {
        json::reply(array("result" => "error", "html" => (string)$form));
      }
    } else {
      print $form;
    }
  }

  public function do_reset() {
    if (request::method() == "post") {
      $this->_change_password();
    } else {
      $user = user::lookup_by_hash(Input::instance()->get("key"));
      if (!empty($user)) {
        print $this->_new_password_form($user->hash);
      } else {
        throw new Exception("@todo FORBIDDEN", 503);
      }
    }
  }

  private function _send_reset($form) {
    $user_name = $form->reset->inputs["name"]->value;
    $user = user::lookup_by_name($user_name);
    if ($user && !empty($user->email)) {
      $user->hash = random::hash();
      $user->save();
      $message = new View("reset_password.html");
      $message->confirm_url = url::abs_site("password/do_reset?key=$user->hash");
      $message->user = $user;

      Sendmail::factory()
        ->to($user->email)
        ->subject(t("Password Reset Request"))
        ->header("Mime-Version", "1.0")
        ->header("Content-type", "text/html; charset=UTF-8")
        ->message($message->render())
        ->send();

      log::success(
        "user",
        t("Password reset email sent for user %name", array("name" => $user->name)));
    } else if (!$user) {
      // Don't include the username here until you're sure that it's XSS safe
      log::warning(
                   "user", t("Password reset email requested for user %user_name, which does not exist.",
                             array("user_name" => $user_name)));
    } else  {
      log::warning(
          "user", t("Password reset failed for %user_name (has no email address on record).",
                    array("user_name" => $user->name)));
    }

    // Always pretend that an email has been sent to avoid leaking
    // information on what user names are actually real.
    message::success(t("Password reset email sent"));
    json::reply(array("result" => "success"));
  }

  private static function _reset_form() {
    $form = new Forge(url::current(true), "", "post", array("id" => "g-reset-form"));
    $group = $form->group("reset")->label(t("Reset Password"));
    $group->input("name")->label(t("Username"))->id("g-name")->class(null)
      ->rules("required")
      ->error_messages("required", t("You must enter a user name"));
    $group->submit("")->value(t("Reset"));

    return $form;
  }

  private function _new_password_form($hash=null) {
    $template = new Theme_View("page.html", "other", "reset");

    $form = new Forge("password/do_reset", "", "post", array("id" => "g-change-password-form"));
    $group = $form->group("reset")->label(t("Change Password"));
    $hidden = $group->hidden("hash");
    if (!empty($hash)) {
      $hidden->value($hash);
    }
    $minimum_length = module::get_var("user", "minimum_password_length", 5);
    $input_password = $group->password("password")->label(t("Password"))->id("g-password")
      ->rules($minimum_length ? "required|length[$minimum_length, 40]" : "length[40]");
    $group->password("password2")->label(t("Confirm Password"))->id("g-password2")
      ->matches($group->password);
    $group->inputs["password2"]->error_messages(
      "mistyped", t("The password and the confirm password must match"));
    $group->submit("")->value(t("Update"));

    $template->content = $form;
    return $template;
  }

  private function _change_password() {
    $view = $this->_new_password_form();
    if ($view->content->validate()) {
      $user = user::lookup_by_hash(Input::instance()->post("hash"));
      if (empty($user)) {
        throw new Exception("@todo FORBIDDEN", 503);
      }

      $user->password = $view->content->reset->password->value;
      $user->hash = null;
      $user->save();
      message::success(t("Password reset successfully"));
      url::redirect(item::root()->abs_url());
    } else {
      print $view;
    }
  }
}