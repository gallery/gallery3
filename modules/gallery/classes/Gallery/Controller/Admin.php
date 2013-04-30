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
  /**
   * Do auth checks for the admin area.
   */
  public function check_auth($auth) {
    if (!Identity::active_user()->admin) {
      // We're not an admin - redirect guests to login or non-admin users to a 403 Forbidden.
      if (Identity::active_user()->guest) {
        $auth->login = true;
      } else {
        Access::forbidden();
      }
    } else {
      $time_remaining = Auth::get_time_remaining_for_admin_area();

      if ($this->request->query("reauth_check")) {
        // This is the ajax query checking to see if our admin session has expired.
        $result["result"] = "success";
        if ($time_remaining < 30) {
          Message::success(t("Automatically logged out of the admin area for your security"));
          $result["location"] = URL::abs_site("");  // site root
        }
        $this->response->json($result);
      } else {
        // Go to the reauth page if they've timed out, otherwise refresh the activity timestamp.
        if ($time_remaining < 0) {
          $auth->reauthenticate = true;
        } else {
          Session::instance()->set("admin_area_activity_timestamp", time());
        }
      }
    }

    return parent::check_auth($auth);
  }
}
