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

    // Check if we should be allowed to run this controller if in maintenance or private mode.
    Gallery::maintenance_mode($this->allow_maintenance_mode);
    Gallery::private_gallery($this->allow_private_gallery);
  }
}
