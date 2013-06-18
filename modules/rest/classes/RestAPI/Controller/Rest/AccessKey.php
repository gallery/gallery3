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
class RestAPI_Controller_Rest_AccessKey extends Controller_Rest {
  public function before() {
    if ($this->request->method() != HTTP_Request::GET) {
      // Check login using "user" and "password" fields in POST.  Fire a 403 Forbidden if it fails.
      if (!Validation::factory($this->request->post())
        ->rule("user", "Auth::validate_login", array(":validation", ":data", "user", "password"))
        ->check()) {
        throw Rest_Exception::factory(403);
      }

      // Set the access key
      $this->request->headers("X-Gallery-Request-Key", Rest::access_key());
    }

    return parent::before();
  }

  public function action_get() {
    // We want to return an empty response with either status 200 or 403, depending on if guest
    // access is allowed.  Since Controller_Rest::before() would have already fired a 403
    // if a login was required, we have nothing left to do here - this will return a 200.
  }

  public function action_post() {
    // If we got here, login was already successful - simply return the key.
    $this->rest_response = Rest::access_key();
  }
}
