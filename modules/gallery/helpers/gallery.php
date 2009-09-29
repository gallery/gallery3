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
class gallery_Core {
  const VERSION = "3.0 git (pre-RC1)";

  /**
   * If Gallery is in maintenance mode, then force all non-admins to get routed to a "This site is
   * down for maintenance" page.
   */
  static function maintenance_mode() {
    $maintenance_mode = Kohana::config("core.maintenance_mode", false, false);

    if (Router::$controller != "login" && !empty($maintenance_mode) && !user::active()->admin) {
      Router::$controller = "maintenance";
      Router::$controller_path = MODPATH . "gallery/controllers/maintenance.php";
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

  /**
   * Return a unix timestamp in a user specified format including date and time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date_time($timestamp) {
    return date(module::get_var("gallery", "date_time_format", "Y-M-d H:i:s"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the date.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date($timestamp) {
    return date(module::get_var("gallery", "date_format", "Y-M-d"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function time($timestamp) {
    return date(module::get_var("gallery", "time_format", "H:i:s"), $timestamp);
  }

  /**
   * Provide a wrapper function for Kohana::find_file that first strips the extension and
   * then calls the Kohana::find_file and supplies the extension as the type.
   * @param   string   directory to search in
   * @param   string   filename to look for
   * @param   boolean  file required (optional: default false)
   * @return  array    if the extension is config, i18n or l10n
   * @return  string   if the file is found (relative to the DOCROOT)
   * @return  false    if the file is not found
   */
  static function find_file($directory, $file, $required=false) {
    $file_name = substr($file, 0, -strlen($ext = strrchr($file, '.')));
    $file_name = Kohana::find_file($directory, $file_name, $required, substr($ext, 1));
    if (!$file_name && file_exists(DOCROOT . "lib/$file")) {
      return "lib/$file";
    }

    return is_string($file_name) ? substr($file_name, strlen(DOCROOT)) : $file_name;
  }

}