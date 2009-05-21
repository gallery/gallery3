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
define('IN_PRODUCTION', true);

// Gallery requires PHP 5.2+
version_compare(PHP_VERSION, '5.2', '<') and exit('Gallery requires PHP 5.2 or newer.');

// Gallery requires short_tags to be on
!ini_get('short_open_tag') and exit('Gallery requires short_open_tag to be on.');

// Set the error reporting level.  Use E_ALL unless you have a special need.
error_reporting(E_NONE);

// Disabling display_errors will  effectively disable Kohana error display
// and logging. You can turn off Kohana errors in application/config/config.php
ini_set('display_errors', false);

define('EXT', '.php');
define('DOCROOT', strtr(getcwd() . '/', DIRECTORY_SEPARATOR, '/'));
define('KOHANA',  'index.php');

// If the front controller is a symlink, change to the real docroot
is_link(basename(__FILE__)) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define('APPPATH', strtr(realpath('core') . '/', DIRECTORY_SEPARATOR, '/'));
define('MODPATH', strtr(realpath('modules') . '/', DIRECTORY_SEPARATOR, '/'));
define('THEMEPATH', strtr(realpath('themes') . '/', DIRECTORY_SEPARATOR, '/'));
define('SYSPATH', strtr(realpath('kohana') . '/', DIRECTORY_SEPARATOR, '/'));

// Force a test run if we're in command line mode.
if (PHP_SAPI == 'cli') {
  array_splice($_SERVER['argv'], 1, 0, 'gallery_unit_test');
  define('TEST_MODE', 1);
  @mkdir('test/var/logs', 0777, true);
  define('VARPATH', strtr(realpath('test/var') . '/', DIRECTORY_SEPARATOR, '/'));
  @copy("var/database.php", VARPATH . "database.php");
} else {
  define('TEST_MODE', 0);
  define('VARPATH', strtr(realpath('var') . '/', DIRECTORY_SEPARATOR, '/'));
}
define('TMPPATH', VARPATH . '/tmp');

// Initialize.
require SYSPATH . 'core/Bootstrap' . EXT;
