<?php

// Acquire lock
$fp = fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR."gallery.lock", "w+");
if (!flock($fp, LOCK_EX | LOCK_NB)) {
  echo "Couldn't get the lock!";
  exit;
}

set_time_limit(3600);
error_reporting(E_ALL);
ini_set("display_errors", "on");

define("IN_PRODUCTION", true);

version_compare(PHP_VERSION, "5.2.3", "<") and
  exit("Gallery requires PHP 5.2.3 or newer (you're using " . PHP_VERSION  . ")");

chdir(dirname(dirname(dirname(__FILE__))));

define("EXT", ".php");
define("DOCROOT", getcwd() . "/");
define("KOHANA",  "index.php");

// If the front controller is a symlink, change to the real docroot
is_link(basename(__FILE__)) and chdir(dirname(realpath(__FILE__)));

// Define application and system paths
define("APPPATH", realpath("application") . "/");
define("MODPATH", realpath("modules") . "/");
define("THEMEPATH", realpath("themes") . "/");
define("SYSPATH", realpath("system") . "/");

define("TEST_MODE", 0);
define("VARPATH", realpath("var") . "/");
define("TMPPATH", VARPATH . "/tmp/");

if (file_exists("local.php")) {
  include("local.php");
}

define('SYSTEM_BENCHMARK', 'system_benchmark');
require SYSPATH.'core/Benchmark'.EXT;

require SYSPATH.'core/Event'.EXT;
final class Event extends Event_Core {}

require SYSPATH.'core/Kohana'.EXT;
final class Kohana extends Kohana_Core {}

require SYSPATH.'core/Kohana_Exception'.EXT;
require MODPATH.'gallery/libraries/MY_Kohana_Exception'.EXT;

require SYSPATH.'core/Kohana_Config'.EXT;
require SYSPATH.'libraries/drivers/Config'.EXT;
require SYSPATH.'libraries/drivers/Config/Array'.EXT;
final class Kohana_Config extends Kohana_Config_Core {}

Kohana::setup();

Event::run('system.ready');

Folder_Sync_Controller::cron();

// Release lock
flock($fp, LOCK_UN);
fclose($fp);
