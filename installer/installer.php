<?php defined("SYSPATH") or die("No direct script access.");
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

  static function unpack_sql($config) {
    $prefix = $config["prefix"];
    foreach (file(DOCROOT . "installer/install.sql") as $line) {
      $buf .= $line;
      if (preg_match("/;$/", $buf)) {
        if (!mysql_query(self::prepend_prefix($prefix, $buf))) {
          return false;
        }
        $buf = "";
      }
    }
    return true;
  }

  static function connect($config) {
    return @mysql_connect($config["host"], $config["user"], $config["password"]);
  }

  static function select_db($config) {
    if (mysql_select_db($config["dbname"])) {
      return true;
    }

    return mysql_query("CREATE DATABASE {$config['dbname']}") &&
      mysql_select_db($config["dbname"]);
  }

  static function db_empty($config) {
    $query = "SHOW TABLES IN {$config['dbname']} LIKE '{$config['prefix']}items'";
    return mysql_num_rows(mysql_query($query)) == 0;
  }

  static function create_admin($config) {
    $salt = "";
    for ($i = 0; $i < 4; $i++) {
      $char = mt_rand(48, 109);
      $char += ($char > 90) ? 13 : ($char > 57) ? 7 : 0;
      $salt .= chr($char);
    }
    $password = substr(md5(time() * rand()), 0, 6);
    $hashed_password = $salt . md5($salt . $password);
    $sql = self::prepend_prefix($config["prefix"],
       "UPDATE {users} SET `password` = '$hashed_password' WHERE `id` = 2");
    if (mysql_query($sql)) {
    } else {
      throw new Exception(mysql_error());
    }

    return array("admin", $password);
  }

  static function create_admin_session($config) {
    $session_id = md5(time() * rand());
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    $user_agent_len = strlen($user_agent);
    $now = time();
    $data = "session_id|s:32:\"$session_id\"";
    $data .= ";user_agent|s:{$user_agent_len}:\"$user_agent\"";
    $data .= ";user|i:2";
    $data .= ";after_install|i:1";
    $data .= ";last_activity|i:$now";
    $data = base64_encode($data);
    $sql = "INSERT INTO {sessions} VALUES('$session_id', $now, '$data')";
    $sql = self::prepend_prefix($config["prefix"], $sql);
    if (mysql_query($sql)) {
      setcookie("g3sid", $session_id, 0, "/", "", false, false);
    } else {
      throw new Exception(mysql_error());
    }
  }

  static function create_private_key($config) {
    $key = md5(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
    $sql = self::prepend_prefix($config["prefix"],
       "INSERT INTO {vars} VALUES(NULL, 'core', 'private_key', '$key')");
    if (mysql_query($sql)) {
    } else {
      throw new Exception(mysql_error());
    }
  }

  static function prepend_prefix($prefix, $sql) {
    return  preg_replace("#{([a-zA-Z0-9_]+)}#", "{$prefix}$1", $sql);
  }
}