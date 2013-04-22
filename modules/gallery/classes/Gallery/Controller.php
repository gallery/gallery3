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
abstract class Gallery_Controller extends Kohana_Controller {
  // Set the defaults as false (which can be overridden by other controllers)
  public $allow_maintenance_mode = false;
  public $allow_private_gallery = false;

  /**
   * This is run to initialize Gallery before executing the controller action.
   */
  public function before() {
    parent::before();

    // Restrict all response frames to the same origin for security
    $this->response->headers("X-Frame-Options", "SAMEORIGIN");

    // Initialize the modules (will run "gallery_ready" event)
    if ($this->request->is_initial()) {
      Gallery::ready();
    }

    // See if we need to login because we're in maintenance mode.  This will force all non-admins
    // back to the login page, unless the controller has "$allow_maintenance_mode == true".
    // The site theme will put a "This site is down for maintenance" message on the login page.
    if (Module::get_var("gallery", "maintenance_mode", 0) &&
        !Identity::active_user()->admin &&
        !$this->allow_maintenance_mode) {
      $this->request->post("continue_url", URL::abs_site("admin/maintenance"));
      $this->request->action("show_login");
    }

    // See if we need to login because we have a private gallery.  This will force all guests
    // back to the login page, unless the controller has "$allow_private_gallery == true".
    if (Identity::active_user()->guest &&
        !Access::user_can(Identity::guest(), "view", Item::root()) &&
        (php_sapi_name() != "cli") &&
        !$this->allow_private_gallery) {
      $this->request->action("show_login");
    }
  }

  public function action_show_login() {
    // Get continue_url from post, or set to current URL if not found.  Then, set in session.
    $continue_url = Arr::get($this->request->post(), "continue_url", URL::abs_current());
    Session::instance()->set("continue_url", $continue_url);

    if (Theme::$is_admin) {
      // At this point we're in the admin theme and it doesn't have a themed login page, so
      // we can't just swap in the login controller and have it work.  So redirect to the
      // login where we'll run this code again with the site theme.  This will maintain the
      // continue_url since it's set in the session.
      $this->redirect("login");
    } else {
      // Show the login page without redirecting.
      $this->response = Request::factory("login")->execute();
    }
  }
}
