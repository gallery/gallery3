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
 * -t     Table prefix           (default: )
 * -f     Response file          (default: not used)
 *        The response file is a php file that contains the following syntax;
 *        $config[key] = value;
 *        Where key is one of "host", "user", "password", "dbname", "prefix".  Values specified
 *        on the command line will override values contained in this file
 */

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

include DOCROOT . "/installer/helpers/installer.php";

// @todo Log the results of failed call
if (installer::failed()) {
  installer::display_requirements();
  die;
}
installer::display_requirements();

$install_config = installer::parse_cli_parms($argv);


