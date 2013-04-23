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
class Gallery_Controller_Admin extends Controller {
  public function before() {
    parent::before();

    if (!Identity::active_user()->admin) {
      if (Identity::active_user()->guest) {
        Session::instance()->set("continue_url", URL::abs_current(true));
        $this->redirect("login");
      } else {
        Access::forbidden();
      }
    }

    $time_remaining = Auth::get_time_remaining_for_admin_area();

    if ($this->request->query("reauth_check")) {
      $result["result"] = "success";
      if ($time_remaining < 30) {
        Message::success(t("Automatically logged out of the admin area for your security"));
        $result["location"] = URL::abs_site("");
      }
      $this->response->json($result);
    }

    if ($time_remaining < 0) {
      return self::_prompt_for_reauth();
    } else {
      Session::instance()->set("admin_area_activity_timestamp", time());
    }

    if (Request::current()->method() == HTTP_Request::POST) {
      Access::verify_csrf();
    }
  }

  private static function _prompt_for_reauth() {
    if (Request::current()->method() == HTTP_Request::GET) {
      // Avoid anti-phishing protection by passing the url as session variable.
      Session::instance()->set("continue_url", URL::abs_current(true));
    }
    // Save the is_ajax value as we lose it, if set, when we redirect
    Session::instance()->set("is_ajax_request", Request::current()->is_ajax());
    $this->redirect("reauthenticate");
  }
}

