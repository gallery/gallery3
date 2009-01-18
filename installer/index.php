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
 * The main install program to install Gallery3.
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

define("DOCROOT", dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// Define application and system paths
define('APPPATH', DOCROOT . 'core' . DIRECTORY_SEPARATOR);
define('MODPATH', DOCROOT . 'modules' . DIRECTORY_SEPARATOR);
define('THEMEPATH', DOCROOT . 'themes' . DIRECTORY_SEPARATOR);
define('SYSPATH', DOCROOT . 'kohana' . DIRECTORY_SEPARATOR);

define('VARPATH', DOCROOT . 'var' . DIRECTORY_SEPARATOR);
define('TEST_MODE', 0);
define('EXT', ".php");

include DOCROOT . "installer/helpers/installer.php";

if (PHP_SAPI == "cli") {
  installer::command_line();
  exit;
}

if (file_exists(VARPATH . "installed")) {
  header("Location: ../index.php/albums/1");
  exit;
}

set_error_handler(create_function('$errno, $errstr, $errfile, $errline',
  'throw new ErrorException($errstr, 0, $errno, $errfile, $errline);'));

// Set exception handler
set_exception_handler(array("installer", "print_exception"));

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
  installer::print_exception($e);
  die("Specifed User does not have sufficient authority to install Gallery3\n");
}

$config_valid &= installer::check_docroot_writable();

installer::display_requirements(!$config_valid);

if ($config_valid) {
  print installer::install();
}


