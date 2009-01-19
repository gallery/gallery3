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
 * -t     Table prefix           (default: )
 * -m     Modules to install     (default: core, user)
 */
define("DOCROOT", dirname(dirname(__FILE__)) . "/");
define("VARPATH", DOCROOT . "var/");

// Define this to get the preamable to pass
// @todo: change all preambles to key off of VARPATH instead
define("SYSPATH", "anything");

require(DOCROOT . "installer/installer.php");
installer::command_line();
