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
   * Check if we need to halt controller execution and send an auth-related reponse to the user
   * instead.  Controllers can overload this function to add checks of their own.  For example:
   *   public function check_auth($auth) {
   *     if (Example::foo()) {
   *       $auth->login = true;
   *     }
   *     return parent::check_auth($auth);
   *   }
   *
   * @see  Controller::execute() for more details on how $auth is processed
   * @see  Controller_Admin::check_auth() for how this is used in the admin area
   * @param   object  $auth  Auth as directed by child classes
   * @return  object  $auth  Auth as directed to parent classes
   */
  public function check_auth($auth) {
    // See if we need to login because we're in maintenance mode.  This will force all non-admins
    // back to the login page, unless the controller has "$allow_maintenance_mode == true".
    // The site theme will put a "This site is down for maintenance" message on the login page.
    if (Module::get_var("gallery", "maintenance_mode", 0) &&
        !Identity::active_user()->admin &&
        !$this->allow_maintenance_mode) {
      $auth->continue_url = "admin/maintenance";
      $auth->login = true;
    }

    // See if we need to login because we have a private gallery.  This will force all guests
    // back to the login page, unless the controller has "$allow_private_gallery == true".
    if (Identity::active_user()->guest &&
        !Access::user_can(Identity::guest(), "view", Item::root()) &&
        (php_sapi_name() != "cli") &&
        !$this->allow_private_gallery) {
      $auth->login = true;
    }

    return $auth;
  }

  /**
   * Overload Controller::execute() to add initialization and auth checking before executing the
   * controller as normal.  Controllers should use before() and after() functions instead of trying
   * to overload this implementation.
   */
  public function execute() {
    // Initialize the modules (will run "gallery_ready" event).
    if ($this->request->is_initial()) {
      Gallery::ready();
    }

    // Restrict all response frames to the same origin for security.
    $this->response->headers("X-Frame-Options", "SAMEORIGIN");

    // If is_ajax_request was previously set, make this request ajax.
    if (Session::instance()->get_once("is_ajax_request")) {
      $this->request->make_ajax();
    }

    // Populate $auth with our defaults, then run check_auth().
    $auth = new stdClass();
    $auth->login = false;
    $auth->reauthenticate = false;
    $auth->continue_url = $this->request->uri();
    $auth = $this->check_auth($auth);

    // If check_auth() generated a response (e.g. admin reauth_check), halt controller and send it.
    if ($this->response->body()) {
      return $this->response;
    }

    if ($auth->login || $auth->reauthenticate) {
      $url = $auth->login ? "login" : "reauthenticate";

      // Set the continue_url and is_ajax values in the session so they're kept if we redirect.
      Session::instance()->set("continue_url", URL::abs_site($auth->continue_url));
      Session::instance()->set("is_ajax_request", $this->request->is_ajax());

      if (Theme::$is_admin) {
        // At this point we're in the admin theme and it doesn't have themed pages for this, so we
        // can't just do an internal sub-request.  So we redirect, where we'll run this code again
        // with the site theme.  This will still maintain the variables set in the Session.
        $this->redirect($url);
      } else {
        // Show the page without redirecting the browser.
        $this->response = Request::factory($url)->execute();
      }
    } else {
      // No auth required - execute the controller's before(), action, and after() as normal.
      parent::execute();
    }

    return $this->response;
  }
}
