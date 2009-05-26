<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class core_Core {
  /**
   * If Gallery is in maintenance mode, then force all non-admins to get routed to a "This site is
   * down for maintenance" page.
   */
  static function maintenance_mode() {
    $maintenance_mode = Kohana::config("core.maintenance_mode", false, false);

    if (Router::$controller != "login" && !empty($maintenance_mode) && !user::active()->admin) {
      Router::$controller = "maintenance";
      Router::$controller_path = APPPATH . "controllers/maintenance.php";
      Router::$method = "index";
    }
  }

  /**
   * This function is called when the Gallery is fully initialized.  We relay it to modules as the
   * "gallery_ready" event.  Any module that wants to perform an action at the start of every
   * request should implement the <module>_event::gallery_ready() handler.
   */
  static function ready() {
    module::event("gallery_ready");
  }

  /**
   * This function is called right before the Kohana framework shuts down.  We relay it to modules
   * as the "gallery_shutdown" event.  Any module that wants to perform an action at the start of
   * every request should implement the <module>_event::gallery_shutdown() handler.
   */
  static function shutdown() {
    module::event("gallery_shutdown");
  }
}