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
define("DOCROOT", dirname(dirname(__FILE__)) . "/");
define("VARPATH", DOCROOT . "var/");
define("SYSPATH", "DEFINED_TO_SOMETHING_SO_THAT_WE_CAN_KEEP_CONSISTENT_PREAMBLES_IN_THE_INSTALLER");

if (version_compare(PHP_VERSION, "5.2.3", "<")) {
  print "Gallery 3 requires PHP 5.2.3 or newer.\n";
  exit;
}

require(DOCROOT . "installer/installer.php");
if (php_sapi_name() == "cli") {
  include("cli.php");
} else {
  if ($_GET["page"] == "check") {
    include("check.html.php");
  } else {
    include("web.php");
  }
}

