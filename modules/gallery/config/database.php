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

if (file_exists(VARPATH . "database.php")) {
  include(VARPATH . "database.php");

  // Transform the result for Kohana 3.  We need to up-convert var/database.php
  // eventually to avoid having to do this, but since we call Database::instance()
  // very early in the bootstrap to make sure that the database is ok, we can't rely
  // on the upgrader to convert the file so massage it by hand here.
  $default = $config["default"];
  return array(
    "default" => array(
      "type" => "MySQL", // Kohana 3 doesn't have a MySQLi module yet
      "connection" => array(
        "hostname" => (
          !empty($default["connection"]["host"]) ?
          $default["connection"]["host"] :
          $default["connection"]["socket"]),
        "database" => $default["connection"]["database"],
        "username" => $default["connection"]["user"],
        "password" => $default["connection"]["pass"],
        "persistent" => $default["persistent"]
      ),
      "table_prefix" => $default["table_prefix"],
      "charset" => $default["character_set"],
      "caching" => $default["cache"]));
}
