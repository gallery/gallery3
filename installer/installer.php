<?php defined("SYSPATH") or die("No direct script access.");
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
class installer {
  static function already_installed() {
    return file_exists(VARPATH . "database.php");
  }

  static function var_writable() {
    if (is_writable(VARPATH)) {
      return true;
    }

    if (@mkdir(VARPATH)) {
      return true;
    }

    return false;
  }

  static function setup_var() {
    $errors = array();
    if (is_writable(VARPATH)) {
      return;
    }

    if (is_writable(dirname(VARPATH)) && !mkdir(VARPATH)) {
      $errors["Filesystem"] =
        sprintf("The %s directory doesn't exist and can't be created", VARPATH);
    }

    if ($errors) {
      throw new InstallException($errors);
    }
  }

  static function create_database_config($config) {
    $db_config_file = VARPATH . "database.php";
    ob_start();
    extract($config);
    include(DOCROOT . "installer/database_config.php");
    $output = ob_get_clean();
    return file_put_contents($db_config_file, $output) !== false;
  }

  static function unpack_var() {
    include(DOCROOT . "installer/init_var.php");
    return true;
  }

  static function unpack_sql() {
    foreach (file(DOCROOT . "installer/install.sql") as $line) {
      $buf .= $line;
      if (preg_match("/;$/", $buf)) {
        if (!mysql_query($buf)) {
          return false;
        }
        $buf = "";
      }
    }
    return true;
  }

  static function connect($config) {
    return mysql_connect($config["host"], $config["user"], $config["password"]);
  }

  static function select_db($config) {
    if (mysql_select_db($config["dbname"])) {
      return true;
    }

    return mysql_query("CREATE DATABASE {$config['dbname']}") &&
      mysql_select_db($config["dbname"]);
  }

  static function db_empty($config) {
    return mysql_num_rows(mysql_query("SHOW TABLES FROM {$config['dbname']}")) == 0;
  }

  static function create_admin($config) {
    $errors = array();
    $salt = "";
    for ($i = 0; $i < 4; $i++) {
      $char = mt_rand(48, 109);
      $char += ($char > 90) ? 13 : ($char > 57) ? 7 : 0;
      $salt .= chr($char);
    }
    $password = substr(md5(time() * rand()), 0, 6);
    $hashed_password = $salt . md5($salt . $password);
    if (mysql_query("UPDATE `users` SET `password` = '$hashed_password' WHERE `id` = 2")) {
    } else {
      $errors["Database"] = "Unable to set admin password.  Error details:\n" . mysql_error();
    }

    if ($errors) {
      throw new InstallException($errors);
    }

    return array("admin", $password);
  }
}