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

class installer {
  static $mysqli;

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
    if (!file_exists(VARPATH)) {
      mkdir(VARPATH);
      chmod(VARPATH, 0777);
    }

    include(DOCROOT . "installer/init_var.php");
    return true;
  }

  static function unpack_sql($config) {
    $prefix = $config["prefix"];
    $buf = null;
    foreach (file(DOCROOT . "installer/install.sql") as $line) {
      $buf .= trim($line);
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
    // We know that we have either mysql or mysqli.  By default we use mysql functions, so if
    // they're not defined then do the simplest thing which will work: remap them to their mysqli
    // counterparts.
    if (!function_exists("mysql_query")) {
      function mysql_connect($host, $user, $pass) {
        list ($host, $port) = explode(":", $host . ":");
        installer::$mysqli = new mysqli($host, $user, $pass, $port);
        // http://php.net/manual/en/mysqli.connect.php says to use mysqli_connect_error() instead of
        // $mysqli->connect_error because of bugs before PHP 5.2.9
        $error = mysqli_connect_error();
        return empty($error);
      }
      function mysql_query($query) {
        return installer::$mysqli->query($query);
      }
      function mysql_num_rows($result) {
        return $result->num_rows;
      }
      function mysql_error() {
        return installer::$mysqli->error;
      }
      function mysql_select_db($db) {
        return installer::$mysqli->select_db($db);
      }
    }

    $host = empty($config["port"]) ? $config['host'] : "{$config['host']}:{$config['port']}";
    return @mysql_connect($host, $config["user"], $config["password"]);
  }

  static function select_db($config) {
    if (mysql_select_db($config["dbname"])) {
      return true;
    }

    return mysql_query("CREATE DATABASE `{$config['dbname']}`") &&
      mysql_select_db($config["dbname"]);
  }

  static function verify_mysql_version($config) {
    return version_compare(installer::mysql_version($config), "5.0.0", ">=");
  }

  static function mysql_version($config) {
    $result = mysql_query("SHOW VARIABLES WHERE variable_name = \"version\"");
    $row = mysql_fetch_object($result);
    return $row->Value;
  }

  static function db_empty($config) {
    $query = "SHOW TABLES LIKE '{$config['prefix']}items'";
    $results = mysql_query($query);
    if ($results === false) {
      $msg = mysql_error();
      return $msg;
    }
    return mysql_num_rows($results) === 0;
  }

  static function create_admin($config) {
    $salt = "";
    for ($i = 0; $i < 4; $i++) {
      $char = mt_rand(48, 109);
      $char += ($char > 90) ? 13 : ($char > 57) ? 7 : 0;
      $salt .= chr($char);
    }
    if (!$password = $config["g3_password"]) {
      $password = substr(md5(time() . mt_rand()), 0, 6);
    }
    // Escape backslash in preparation for our UPDATE statement.
    $hashed_password = str_replace("\\", "\\\\", $salt . md5($salt . $password));
    $sql = self::prepend_prefix($config["prefix"],
       "UPDATE {users} SET `password` = '$hashed_password' WHERE `id` = 2");
    if (mysql_query($sql)) {
    } else {
      throw new Exception(mysql_error());
    }

    return array("admin", $password);
  }

  static function create_admin_session($config) {
    $session_id = md5(time() . mt_rand());
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    $user_agent_len = strlen($user_agent);
    $now = time();
    $data = "session_id|s:32:\"$session_id\"";
    $data .= ";user_agent|s:{$user_agent_len}:\"$user_agent\"";
    $data .= ";user|i:2";
    $data .= ";after_install|i:1";
    $data .= ";last_activity|i:$now";
    $data = base64_encode($data);
    $sql = "INSERT INTO {sessions}(`session_id`, `last_activity`, `data`) " .
      "VALUES('$session_id', $now, '$data')";
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
       "INSERT INTO {vars} VALUES(NULL, 'gallery', 'private_key', '$key')");
    if (mysql_query($sql)) {
    } else {
      throw new Exception(mysql_error());
    }
  }

  static function prepend_prefix($prefix, $sql) {
    return preg_replace("#{([a-zA-Z0-9_]+)}#", "`{$prefix}$1`", $sql);
  }

  static function check_environment() {
    if (!function_exists("mysql_query") && !function_exists("mysqli_set_charset")) {
      $errors[] = "Gallery 3 requires a MySQL database, but PHP doesn't have either the <a href=\"http://php.net/mysql\">MySQL</a> or the  <a href=\"http://php.net/mysqli\">MySQLi</a> extension.";
    }

    if (!preg_match("/^.$/u", "ñ")) {
      $errors[] = "PHP is missing <a href=\"http://php.net/pcre\">Perl-Compatible Regular Expression</a> with UTF-8 support.";
    } else if (!preg_match("/^\pL$/u", "ñ")) {
      $errors[] = "PHP is missing <a href=\"http://php.net/pcre\">Perl-Compatible Regular Expression</a> with Unicode support.";
    }

    if (!(function_exists("spl_autoload_register"))) {
      $errors[] = "PHP is missing <a href=\"http://php.net/spl\">Standard PHP Library (SPL)</a> support";
    }

    if (!(class_exists("ReflectionClass"))) {
      $errors[] = "PHP is missing <a href=\"http://php.net/reflection\">reflection</a> support";
    }

    if (!(function_exists("filter_list"))) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/filter\">filter extension</a>";
    }

    if (!(extension_loaded("iconv"))) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/iconv\">iconv extension</a>";
    }

    if (!(extension_loaded("xml"))) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/xml\">XML Parser extension</a>";
    }

    if (!(extension_loaded("simplexml"))) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/simplexml\">SimpleXML extension</a>";
    }

    if (!extension_loaded("mbstring")) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/mbstring\">mbstring extension</a>";
    } else if (ini_get("mbstring.func_overload") & MB_OVERLOAD_STRING) {
      $errors[] = "The <a href=\"http://php.net/mbstring\">mbstring extension</a> is overloading PHP's native string functions.  Please disable it.";
    }

    if (!function_exists("json_encode")) {
      $errors[] = "PHP is missing the <a href=\"http://php.net/manual/en/book.json.php\">JavaScript Object Notation (JSON) extension</a>.  Please install it.";
    }

    if (!ini_get("short_open_tag")) {
      $errors[] = "Gallery requires <a href=\"http://php.net/manual/en/ini.core.php\">short_open_tag</a> to be on.  Please enable it in your php.ini.";
    }

    if (!function_exists("ctype_alpha")) {
      $errors[] = "Gallery requires the <a href=\"http://php.net/manual/en/book.ctype.php\">PHP Ctype</a> extension.  Please install it.";
    }

    if (self::ini_get_bool("safe_mode")) {
      $errors[] = "Gallery cannot function when PHP is in <a href=\"http://php.net/manual/en/features.safe-mode.php\">Safe Mode</a>.  Please disable safe mode.";
    }

    return @$errors;
  }

  /**
   * Convert any possible boolean ini value to true/false.
   *   On = on = 1 = true
   *   Off = off = 0 = false
   */
  static function ini_get_bool($varname) {
    $value = ini_get($varname);

    if (!strcasecmp("on", $value) || $value == 1 || $value === true) {
      return true;
    }

    if (!strcasecmp("off", $value) || $value == 0 || $value === false) {
      return false;
    }

    return false;
  }

}
