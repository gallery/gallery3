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
  const ALLOW_MAINTENANCE_MODE = true;
  const ALLOW_PRIVATE_GALLERY = true;

  public function ajax() {
    $view = new View("gallery/login_ajax.html");
    $view->form = Auth::get_login_form("login/auth_ajax");
    print $view;
  }

  public function auth_ajax() {
    Access::verify_csrf();

    list ($valid, $form) = $this->_auth("login/auth_ajax");
    if ($valid) {
      JSON::reply(array("result" => "success"));
    } else {
      $view = new View("gallery/login_ajax.html");
      $view->form = $form;
      JSON::reply(array("result" => "error", "html" => (string)$view));
    }
  }

  public function html() {
    $view = new View_Theme("page.html", "other", "login");
    $view->page_title = t("Log in to Gallery");
    $view->content = new View("gallery/login_ajax.html");
    $view->content->form = Auth::get_login_form("login/auth_html");
    print $view;
  }

  public function auth_html() {
    Access::verify_csrf();

    list ($valid, $form) = $this->_auth("login/auth_html");
    if ($valid) {
      $continue_url = $form->continue_url->value;
      URL::redirect($continue_url ? $continue_url : Item::root()->abs_url());
    } else {
      $view = new View_Theme("page.html", "other", "login");
      $view->page_title = t("Log in to Gallery");
      $view->content = new View("gallery/login_ajax.html");
      $view->content->form = $form;
      print $view;
    }
  }

  private function _auth($url) {
    $form = Auth::get_login_form($url);
    $valid = $form->validate();
    if ($valid) {
      $user = Identity::lookup_user_by_name($form->login->inputs["name"]->value);
      if (empty($user) || !Identity::is_correct_password($user, $form->login->password->value)) {
        $form->login->inputs["name"]->add_error("invalid_login", 1);
        $name = $form->login->inputs["name"]->value;
        Log::warning("user", t("Failed login for %name", array("name" => $name)));
        Module::event("user_auth_failed", $name);
        $valid = false;
      }
    }

    if ($valid) {
      Auth::login($user);
    }

    // Either way, regenerate the session id to avoid session trapping
    Session::instance()->regenerate();

    return array($valid, $form);
  }
}