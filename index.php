<?php
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
// Set this to true to disable demo/debugging controllers
define("IN_PRODUCTION", true);

// Gallery requires PHP 5.2+
version_compare(PHP_VERSION, "5.2.3", "<") and
  exit("Gallery requires PHP 5.2.3 or newer (you're using " . PHP_VERSION  . ")");

// Gallery requires short_tags to be on
!ini_get("short_open_tag") and exit("Gallery requires short_open_tag to be on.");

// Set the error reporting level.  Use E_ALL unless you have a special need.
error_reporting(0);

// Disabling display_errors will  effectively disable Kohana error display
// and logging. You can turn off Kohana errors in application/config/config.php
ini_set("display_errors", false);

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
  case "upgrade":
  case "package":
    $_SERVER["argv"] = array("index.php", "{$arg_1}r/$arg_1");
    define("TEST_MODE", 0);
    define("VARPATH", realpath("var") . "/");
    break;

  case "test":
    array_splice($_SERVER["argv"], 1, 1, "gallery_unit_test");
    define("TEST_MODE", 1);
    @mkdir("test/var/logs", 0777, true);
    define("VARPATH", realpath("test/var") . "/");
    @copy("var/database.php", VARPATH . "database.php");
    break;

  default:
    print "To upgrade:\n  php index.php upgrade\n\n\n";
    print "Developer-only features:\n  ** CAUTION! THESE FEATURES -WILL- DAMAGE YOUR INSTALL **\n";
    print "  php index.php package  # create new installer files\n";
    print "  php index.php test     # run unit tests\n";
    exit(1);
  }
} else {
  define("TEST_MODE", 0);
  define("VARPATH", realpath("var") . "/");
}
define("TMPPATH", VARPATH . "/tmp/");

if (file_exists("local.php")) {
  include("local.php");
}

// Initialize.
require SYSPATH . "core/Bootstrap" . EXT;
