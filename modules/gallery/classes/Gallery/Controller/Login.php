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
    $this->action_html();
  }

  public function action_ajax() {
    $view = new View("gallery/login_ajax.html");
    $view->form = $this->get_login_form("login/auth_ajax");
    $this->response->body($view);
  }

  public function action_auth_ajax() {
    Access::verify_csrf();

    list ($valid, $form) = $this->auth("login/auth_ajax");
    if ($valid) {
      $this->response->json(array("result" => "success"));
    } else {
      $view = new View("gallery/login_ajax.html");
      $view->form = $form;
      $this->response->json(array("result" => "error", "html" => (string)$view));
    }
  }

  public function action_html() {
    $view = new View_Theme("required/page.html", "other", "login");
    $view->page_title = t("Log in to Gallery");
    $view->content = new View("gallery/login_ajax.html");
    $view->content->form = $this->get_login_form("login/auth_html");
    $this->response->body($view);
  }

  public function action_auth_html() {
    Access::verify_csrf();

    list ($valid, $form) = $this->auth("login/auth_html");
    if ($valid) {
      $continue_url = $form->continue_url->value;
      $this->redirect($continue_url ? $continue_url : Item::root()->abs_url());
    } else {
      $view = new View_Theme("required/page.html", "other", "login");
      $view->page_title = t("Log in to Gallery");
      $view->content = new View("gallery/login_ajax.html");
      $view->content->form = $form;
      $this->response->body($view);
    }
  }

  public function auth($url) {
    $form = $this->get_login_form($url);
    $valid = $form->validate();
    if ($valid) {
      $user = Identity::lookup_user_by_name($form->login->inputs["name"]->value);
      if (empty($user) || !Identity::is_correct_password($user, $form->login->password->value)) {
        $form->login->inputs["name"]->add_error("invalid_login", 1);
        $name = $form->login->inputs["name"]->value;
        GalleryLog::warning("user", t("Failed login for %name", array("name" => $name)));
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

  public function get_login_form($url) {
    $form = new Forge($url, "", "post", array("id" => "g-login-form"));
    $form->set_attr("class", "g-narrow");
    $form->hidden("continue_url")->value(Session::instance()->get("continue_url"));
    $group = $form->group("login")->label(t("Login"));
    $group->input("name")->label(t("Username"))->id("g-username")->class(null)
      ->callback("Auth::validate_too_many_failed_logins")
      ->error_messages(
        "too_many_failed_logins", t("Too many failed login attempts.  Try again later"));
    $group->password("password")->label(t("Password"))->id("g-password")->class(null);
    $group->inputs["name"]->error_messages("invalid_login", t("Invalid name or password"));
    $group->submit("")->value(t("Login"));
    return $form;
  }
}