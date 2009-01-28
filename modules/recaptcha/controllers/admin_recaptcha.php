<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Admin_Recaptcha_Controller extends Admin_Controller {
  public function index() {
    $form = recaptcha::get_configure_form();
    if (request::method() == "post") {
    $old_public_key = module::get_var("recaptcha", "public_key");
    $old_private_key = module::get_var("recaptcha", "private_key");
      if ($form->validate()) {
        $public_key = $form->configure_recaptcha->public_key->value;
        $private_key = $form->configure_recaptcha->private_key->value;

        if ($public_key && $private_key) {
          module::set_var("recaptcha", "public_key", $public_key);
          module::set_var("recaptcha", "private_key", $private_key);
          message::success(t("Recaptcha configured!"));
          log::success(t("Recaptcha public and private keys set"));
          url::redirect("admin/recaptcha");
        } else if ($public_key && !$private_key) {
          $form->configure_recaptcha->private_key->add_error("invalid");
        } else if ($private_key && !$public_key) {
          $form->configure_recaptcha->public_key->add_error("invalid");
        } else {
          module::set_var("recaptcha", "public_key", "");
          module::set_var("recaptcha", "private_key", "");
          message::success(t("Recaptcha disabled!"));
          log::success(t("Recaptcha public and private keys cleared"));
          url::redirect("admin/recaptcha");
        }
      }
    }

    recaptcha::check_config();
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_recaptcha.html");
    $view->content->public_key = module::get_var("recaptcha", "public_key");
    $view->content->private_key = module::get_var("recaptcha", "private_key");
    $view->content->form = $form;
    print $view;
  }

  public function test() {
    $view = new View("admin_recaptcha_test.html");
    $view->public_key = module::get_var("recaptcha", "public_key");
    print $view;
  }
}
