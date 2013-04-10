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
class Recaptcha_Controller_Admin_Recaptcha extends Controller_Admin {
  public function action_index() {
    $form = Recaptcha::get_configure_form();
    if (Request::$current->method() == "POST") {
      // @todo move the "save" part of this into a separate controller function
      Access::verify_csrf();
      $old_public_key = Module::get_var("recaptcha", "public_key");
      $old_private_key = Module::get_var("recaptcha", "private_key");
      if ($form->validate()) {
        $public_key = $form->configure_recaptcha->public_key->value;
        $private_key = $form->configure_recaptcha->private_key->value;

        if ($public_key && $private_key) {
          Module::set_var("recaptcha", "public_key", $public_key);
          Module::set_var("recaptcha", "private_key", $private_key);
          Message::success(t("reCAPTCHA configured!"));
          GalleryLog::success("recaptcha", t("reCAPTCHA public and private keys set"));
          HTTP::redirect("admin/recaptcha");
        } else if ($public_key && !$private_key) {
          $form->configure_recaptcha->private_key->add_error("invalid");
        } else if ($private_key && !$public_key) {
          $form->configure_recaptcha->public_key->add_error("invalid");
        } else {
          Module::set_var("recaptcha", "public_key", "");
          Module::set_var("recaptcha", "private_key", "");
          Message::success(t("No keys provided.  reCAPTCHA is disabled!"));
          GalleryLog::success("recaptcha", t("reCAPTCHA public and private keys cleared"));
          HTTP::redirect("admin/recaptcha");
        }
      }
    }

    Recaptcha::check_config();
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("reCAPTCHA");
    $view->content = new View("admin/recaptcha.html");
    $view->content->public_key = Module::get_var("recaptcha", "public_key");
    $view->content->private_key = Module::get_var("recaptcha", "private_key");
    $view->content->form = $form;
    print $view;
  }
}
