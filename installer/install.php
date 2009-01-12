<?php
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
/**
 * Batch Install program this is to only be run from the command line. The web interface uses
 * a different approach to invoking the installer
 * Command line parameters:
 * -h     Database host          (default: localhost)
 * -u     Database user          (default: root)
 * -p     Database user password (default: )
 * -d     Database name          (default: gallery3)
 * -i     Database type          (default: mysqli)
 * -t     Table prefix           (default: )
 * -m     Modules to install     (default: core, user)
 * -f     Response file          (default: not used)
 *        The response file is a php file that contains the following syntax;
 *        $config[key] = value;
 *        Where key is one of "host", "user", "password", "dbname", "prefix".  Values specified
 *        on the command line will override values contained in this file
 */

function exception_handler($exception) {
  $code     = $exception->getCode();
  $type     = get_class($exception);
  $message  = $exception->getMessage();
  $file     = $exception->getFile();
  $line     = $exception->getLine();

  var_dump($exception);
  // Turn off error reporting
  error_reporting(0);
  exit;
}

if (PHP_SAPI != 'cli') {
  $redirect = str_replace("install.php", "index.php", $_SERVER["REQUEST_URI"]);

  header("Location: $redirect");
  return;
}

if (file_exists('var')) {
  dir("Gallery3 is already installed... exiting");
}

array_shift($argv);          // remove the script name from the arguments

define("DOCROOT", dirname(dirname(__FILE__)));
chdir(DOCROOT);
define('APPPATH', strtr(realpath('core') . '/', DIRECTORY_SEPARATOR, '/'));
define('MODPATH', strtr(realpath('modules') . '/', DIRECTORY_SEPARATOR, '/'));
define('THEMEPATH', strtr(realpath('themes') . '/', DIRECTORY_SEPARATOR, '/'));
define('SYSPATH', strtr(realpath('kohana') . '/', DIRECTORY_SEPARATOR, '/'));
define('EXT', ".php");

//set_error_handler(array('Kohana', 'exception_handler'));
set_error_handler(create_function('$x, $y', 'throw new Exception($y, $x);'));

// Set exception handler
set_exception_handler('exception_handler');

include DOCROOT . "/installer/helpers/installer.php";

// @todo Log the results of failed call
if (!installer::environment_check()) {
  installer::display_requirements();
  die;
}

installer::parse_cli_parms($argv);

$config_valid = true;

try {
  $config_valid = installer::check_database_authorization();
} catch (Exception $e) {
  die("Specifed User does not have sufficient authority to install Gallery3\n");
}

$config_valid = installer::check_docroot_writable();

installer::display_requirements(!$config_valid);

if ($config_valid) {
  // @todo do the install
}


