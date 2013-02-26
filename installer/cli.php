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
/**
 * Command line parameters:
 * -h     Database host          (default: localhost)
 * -u     Database user          (default: root)
 * -p     Database user password (default: )
 * -d     Database name          (default: gallery3)
 * -x     Table prefix           (default: )
 */
if (installer::already_installed()) {
  print "Gallery 3 is already installed.\n";
  return;
}

$errors = installer::check_environment();
if ($errors) {
  oops(implode($errors, "\n"));
}

$config = parse_cli_params();
if (!installer::connect($config)) {
  oops("Unable to connect to the database.\n" . mysql_error() . "\n");
} else if (!installer::select_db($config)) {
  oops("Database {$config['dbname']} doesn't exist and can't be created.  " .
       "Please create the database by hand.");
} else if (is_string($count = installer::db_empty($config)) || !$count) {
  if (is_string($count)) {
    oops($count);
  } else {
    oops("Database {$config['dbname']} already has Gallery 3 tables in it. \n" .
         "    Please remove the Gallery 3 tables, change your prefix,\n" .
         "    or specify an empty database.\n");
  }
} else if (!installer::unpack_var()) {
  oops("Unable to create files inside the 'var' directory");
} else if (!installer::unpack_sql($config)) {
  oops("Failed to create database tables\n" . mysql_error());
} else if (!installer::create_database_config($config)) {
  oops("Couldn't create var/database.php");
} else {
  system("chmod -R 777 " . VARPATH);
  try {
    list ($user, $password) = installer::create_admin($config);
    print "Your Gallery has been successfully installed!\n";
    print "We've created an account for you to use:\n";
    print "  username: $user\n";
    print "  password: $password\n";
    print "\n";

    installer::create_private_key($config);
    exit(0);
  } catch (Exception $e) {
    oops($e->getMessage());
  }
}

function oops($message) {
  print "Oops! Something went wrong during the installation:\n\n";

  print "==> " . $message;
  print "\n";
  print "For help you can try:\n";
  print "  * The Gallery 3 FAQ   - http://codex.galleryproject.org/Gallery3:FAQ\n";
  print "  * The Gallery Forums - http://galleryproject.org/forum\n";
  print "\n\n** INSTALLATION FAILED **\n";
  exit(1);
}

function parse_cli_params() {
  $config = array("host" => "localhost",
                  "user" => "root",
                  "password" => "",
                  "dbname" => "gallery3",
                  "prefix" => "",
                  "g3_password" => "",
                  "type" => function_exists("mysqli_set_charset") ? "mysqli" : "mysql");

  $argv = $_SERVER["argv"];
  for ($i = 1; $i < count($argv); $i++) {
    switch (strtolower($argv[$i])) {
    case "-d":
      $config["dbname"] = $argv[++$i];
      break;
    case "-h":
      list ($config["host"], $config["port"]) = explode(":", $argv[++$i]);
      break;
    case "-u":
      $config["user"] = $argv[++$i];
      break;
    case "-p":
      $config["password"] = $argv[++$i];
      break;
    case "-x":
      $config["prefix"] = $argv[++$i];
      break;
    case "-g3p":
      $config["g3_password"] = $argv[++$i];
      break;
    }
  }

  return $config;
}
