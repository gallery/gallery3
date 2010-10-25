<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
  const VERSION = "3.0+ (git)";

  /**
   * If Gallery is in maintenance mode, then force all non-admins to get routed to a "This site is
   * down for maintenance" page.
   */
  static function maintenance_mode() {
    // @todo: we need a mechanism here to identify controllers that are still legally accessible
    // when the entire Gallery is in maintenance mode.  Perhaps a controller class function or
    // method?
    // https://sourceforge.net/apps/trac/gallery/ticket/1411
    if (Router::$controller != "login" &&
        Router::$controller != "combined" &&
        module::get_var("gallery", "maintenance_mode", 0) &&
        !identity::active_user()->admin) {
      Session::instance()->set("continue_url", url::abs_site("admin/maintenance"));
      Router::$controller = "login";
      Router::$controller_path = MODPATH . "gallery/controllers/login.php";
      Router::$method = "html";
    }
  }

  /**
   * If the gallery is only available to registered users and the user is not logged in, present
   * the login page.
   */
  static function private_gallery() {
    // @todo: we need a mechanism here to identify controllers that are still legally accessible
    // when the entire Gallery is private.  Perhaps a controller class function or method?
    // https://sourceforge.net/apps/trac/gallery/ticket/1411
    if (Router::$controller != "login" &&
        Router::$controller != "combined" &&
        Router::$controller != "digibug" &&
        Router::$controller != "rest" &&
        identity::active_user()->guest &&
        !access::user_can(identity::guest(), "view", item::root()) &&
        php_sapi_name() != "cli") {
      if (Router::$controller == "admin") {
        // At this point we're in the admin theme and it doesn't have a themed login page, so
        // we can't just swap in the login controller and have it work.  So redirect back to the
        // root item where we'll run this code again with the site theme.
        url::redirect(item::root()->abs_url());
      } else {
        Session::instance()->set("continue_url", url::abs_current());
        Router::$controller = "login";
        Router::$controller_path = MODPATH . "gallery/controllers/login.php";
        Router::$method = "html";
      }
    }
  }

  /**
   * This function is called when the Gallery is fully initialized.  We relay it to modules as the
   * "gallery_ready" event.  Any module that wants to perform an action at the start of every
   * request should implement the <module>_event::gallery_ready() handler.
   */
  static function ready() {
    // Don't keep a session for robots; it's a waste of database space.
    if (request::user_agent("robot")) {
      Session::instance()->abort_save();
    }

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
    return date(module::get_var("gallery", "date_time_format"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the date.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date($timestamp) {
    return date(module::get_var("gallery", "date_format"), $timestamp);
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
    if (!$file_name) {
      if (file_exists(DOCROOT . "lib/$directory/$file")) {
        return "lib/$directory/$file";
      } else if (file_exists(DOCROOT . "lib/$file")) {
        return "lib/$file";
      }
    }

    if (is_string($file_name)) {
      // make relative to DOCROOT
      $parts = explode("/", $file_name);
      foreach ($parts as $idx => $part) {
        if (in_array($part, array("application", "modules", "themes", "lib"))) {
          break;
        }
        unset($parts[$idx]);
      }
      $file_name = implode("/", $parts);
    }
    return $file_name;
  }

  /**
   * Set the PATH environment variable to the paths specified.
   * @param  array   Array of paths.  Each array entry can contain a colon separated list of paths.
   */
  static function set_path_env($paths) {
    $path_env = array();
    foreach ($paths as $path) {
      if ($path) {
        array_push($path_env, $path);
      }
    }
    putenv("PATH=" .  implode(":", $path_env));
  }
}