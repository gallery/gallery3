<?php
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
// Set this to true to disable demo/debugging controllers
define("IN_PRODUCTION", true);

// Gallery requires PHP 5.2+
version_compare(PHP_VERSION, "5.2.3", "<") and
  exit("Gallery requires PHP 5.2.3 or newer (you're using " . PHP_VERSION  . ")");

// Gallery is not supported on Windows.
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  exit("Gallery is not supported on Windows (PHP reports that you're using: " . PHP_OS . ")");
}

// PHP 5.4 requires a timezone - if one isn't set date functions aren't going to work properly.
// We'll log this once the logging system is initialized (in the gallery_event::gallery_ready).
if (!ini_get("date.timezone")) {
  ini_set("date.timezone", "UTC");
}

// Gallery requires short_tags to be on
!ini_get("short_open_tag") and exit("Gallery requires short_open_tag to be on.");

// Suppress errors.  For information on how to debug Gallery 3, see:
// http://codex.galleryproject.org/Gallery3:FAQ#How_do_I_see_debug_information.3F
error_reporting(0);

// Disabling display_errors will  effectively disable Kohana error display
// and logging. You can turn off Kohana errors in application/config/config.php
ini_set("display_errors", false);

// Turn off session.use_trans_sid -- that feature attempts to inject session ids
// into generated URLs and forms, but it doesn't interoperate will with Gallery's
// Ajax code.
ini_set("session.use_trans_sid", false);

// Restrict all response frames to the same origin for security
header("X-Frame-Options: SAMEORIGIN");

define("EXT", ".php");
define("DOCROOT", getcwd() . "/");
define("KOHANA",  "index.php");

// If the front controller is a symlink, change to the real docroot
is_link(basename(__FILE__)) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define("APPPATH", realpath("application") . "/");
define("MODPATH", realpath("modules") . "/");
define("THEMEPATH", realpath("themes") . "/");
define("SYSPATH", realpath("system") . "/");

// We only accept a few controllers on the command line
if (PHP_SAPI == "cli") {
  switch ($arg_1 = $_SERVER["argv"][1]) {
  case "install":
    include("installer/index.php");
    exit(0);
  case "upgrade":
  case "package":
    $_SERVER["argv"] = array("index.php", "{$arg_1}r/$arg_1");
    define("TEST_MODE", 0);
    define("VARPATH", realpath("var") . "/");
    break;

  case "test":
    array_splice($_SERVER["argv"], 1, 1, "gallery_unit_test");
    define("TEST_MODE", 1);
    if (!is_dir("test/var")) {
      @mkdir("test/var", 0777, true);
      @mkdir("test/var/logs", 0777, true);
    }
    @copy("var/database.php", "test/var/database.php");
    define("VARPATH", realpath("test/var") . "/");
    break;

  default:
    print "To install:\n";
    print "  php index.php install -d database -h host -u user -p password -x table_prefix -g3p gallery3_admin_password \n\n";
    print "To upgrade:\n";
    print "  php index.php upgrade\n\n";
    print "Developer-only features:\n";
    print "  ** CAUTION! THESE FEATURES -WILL- DAMAGE YOUR INSTALL **\n";
    print "  php index.php package  # create new installer files\n";
    print "  php index.php test     # run unit tests\n";
    exit(1);
  }
} else {
  define("TEST_MODE", 0);
  define("VARPATH", realpath("var") . "/");
}
define("TMPPATH", VARPATH . "tmp/");

if (file_exists("local.php")) {
  include("local.php");
}

// Initialize.
require APPPATH . "Bootstrap" . EXT;
