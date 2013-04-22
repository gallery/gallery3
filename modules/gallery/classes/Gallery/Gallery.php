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
class Gallery_Gallery {
  const VERSION = "3.1+K3";
  const CODE_NAME = "";
  const RELEASE_CHANNEL = "git";
  const RELEASE_BRANCH = "master";

  /**
   * This function is called when the Gallery is fully initialized.  We relay it to modules as the
   * "gallery_ready" event.  Any module that wants to perform an action at the start of every
   * request should implement the <module>Event::gallery_ready() handler.
   */
  static function ready() {
    // Don't keep a session for robots; it's a waste of database space.
    if (Request::user_agent("robot")) {
      Session::instance()->destroy();
    }

    Module::event("gallery_ready");
  }

  /**
   * This function is called right before the Kohana framework shuts down.  We relay it to modules
   * as the "gallery_shutdown" event.  Any module that wants to perform an action at the start of
   * every request should implement the <module>Event::gallery_shutdown() handler.
   */
  static function shutdown() {
    Module::event("gallery_shutdown");
  }

  /**
   * Return a unix timestamp in a user specified format including date and time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date_time($timestamp) {
    return date(Module::get_var("gallery", "date_time_format"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the date.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date($timestamp) {
    return date(Module::get_var("gallery", "date_format"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function time($timestamp) {
    return date(Module::get_var("gallery", "time_format", "H:i:s"), $timestamp);
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
  static function find_file($directory, $file) {
    $file_name = substr($file, 0, -strlen($ext = strrchr($file, '.')));
    $file_name = Kohana::find_file($directory, $file_name, substr($ext, 1));
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
      $count = count($parts);
      foreach ($parts as $idx => $part) {
        // If this part is "modules" or "themes" make sure that the part 2 after this
        // is the target directory, and if it is then we're done.  This check makes
        // sure that if Gallery is installed in a directory called "modules" or "themes"
        // We don't parse the directory structure incorrectly.
        if (in_array($part, array("modules", "themes")) &&
            $idx + 2 < $count &&
            $parts[$idx + 2] == $directory) {
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

  /**
   * Return a string describing this version of Gallery and the type of release.
   */
  static function version_string() {
    if (Gallery::RELEASE_CHANNEL == "git") {
      $build_number = Gallery::build_number();
      return sprintf(
        "%s (branch %s, %s)", Gallery::VERSION, Gallery::RELEASE_BRANCH,
        $build_number ? " build $build_number" : "unknown build number");
    } else {
      return sprintf("%s (%s)", Gallery::VERSION, Gallery::CODE_NAME);
    }
  }

  /**
   * Return the contents of the .build_number file, which should be a single integer
   * or return null if the .build_number file is missing.
   */
  static function build_number() {
    $build_file = DOCROOT . ".build_number";
    if (file_exists($build_file))  {
      $result = parse_ini_file(DOCROOT . ".build_number");
      return $result["build_number"];
    }
    return null;
  }

  /**
   * Return true if we should show the profiler at the bottom of the page.  Note that this
   * function is called at database setup time so it cannot rely on the database.
   */
  static function show_profiler() {
    return file_exists(VARPATH . "PROFILE");
  }

  /**
   * Return true if we should allow Javascript and CSS combining for performance reasons.
   * Typically we want this, but it's convenient for developers to be able to disable it.
   */
  static function allow_css_and_js_combining() {
    return !file_exists(VARPATH . "DONT_COMBINE");
  }
}