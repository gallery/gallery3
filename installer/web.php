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
if (installer::already_installed()) {
  $content = render("success.html.php");
} else {
  switch ($_GET["step"]) {
  default:
  case "welcome":
    $errors = check_environment();
    if ($errors) {
      $content = render("environment_errors.html.php", array("errors" => $errors));
    } else {
      $content = render("get_db_info.html.php");
    }
    break;

  case "save_db_info":
    $config = array("host" => $_POST["dbhost"],
                    "user" => $_POST["dbuser"],
                    "password" => $_POST["dbpass"],
                    "dbname" => $_POST["dbname"],
                    "prefix" => "",
                    "type" => function_exists("mysqli_set_charset") ? "mysqli" : "mysql");

    if (!installer::connect($config)) {
      $content = render("invalid_db_info.html.php");
    } else if (!installer::select_db($config)) {
      $content = render("missing_db.html.php");
    } else if (!installer::db_empty($config)) {
      $content = render("db_not_empty.html.php");
    } else if (!installer::unpack_var()) {
      $content = oops("Unable to create files inside the <code>var</code> directory");
    } else if (!installer::unpack_sql()) {
      $content = oops("Failed to create tables in your database:" . mysql_error());
    } else if (!installer::create_database_config($config)) {
      $content = oops("Couldn't create var/database.php");
    } else {
      try {
        list ($user, $password) = installer::create_admin($config);
        $content = render("success.html.php", array("user" => $user, "password" => $password));
      } catch (Exception $e) {
        $content = oops($e->getMessage());
      }
    }
    break;
  }
}

include("views/install.html.php");

function render($view, $args=array()) {
  ob_start();
  extract($args);
  include(DOCROOT . "installer/views/" . $view);
  return ob_get_clean();
}

function oops($error) {
  return render("oops.html.php", array("error" => $error));
}

function check_environment() {
  if (!function_exists("mysql_query") && !function_exists("mysqli_set_charset")) {
    $errors[] = "Gallery 3 requires a MySQL database, but PHP doesn't have either the the <a href=\"http://php.net/mysql\">MySQL</a> or the  <a href=\"http://php.net/mysqli\">MySQLi</a> extension.";
  }

  if (!@preg_match("/^.$/u", utf8_encode("\xF1"))) {
    $errors[] = "PHP is missing <a href=\"http://php.net/pcre\">Perl-Compatible Regular Expression</a> support.";
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

  if (extension_loaded("mbstring") && (ini_get("mbstring.func_overload") & MB_OVERLOAD_STRING)) {
    $errors[] = "The <a href=\"http://php.net/mbstring\">mbstring extension</a> is overloading PHP's native string functions.  Please disable it.";
  }

  return $errors;
}
