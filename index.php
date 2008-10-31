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

// Set this to true to disable demo/debugging controllers
define('IN_PRODUCTION', true);

// Gallery requires PHP 5.2+
version_compare(PHP_VERSION, '5.2', '<') and exit('Gallery requires PHP 5.2 or newer.');

// Set the error reporting level.  Use E_ALL unless you have a special need.
error_reporting(E_ALL);

// Disabling display_errors will  effectively disable Kohana error display
// and logging. You can turn off Kohana errors in application/config/config.php
ini_set('display_errors', true);

define('EXT', '.php');
define('DOCROOT', getcwd().DIRECTORY_SEPARATOR);
define('GALLERY',  basename(__FILE__));

// If the front controller is a symlink, change to the real docroot
is_link(GALLERY) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define('APPPATH', realpath('core') . "/");
define('VARPATH', realpath('var') . "/");
define('MODPATH', realpath('modules') . "/");
define('THEMEPATH', realpath('themes') . "/");
define('SYSPATH', realpath('kohana') . "/");

// Override any settings here in index.local.php
file_exists('index.local.php') and include('index.local.php');

// Initialize.
require SYSPATH . 'core/Bootstrap' . EXT;