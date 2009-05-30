#!/usr/bin/php -f
<?php
define('EXT', '.php');
define("DOCROOT", realpath("../") . "/");
define("VARPATH", DOCROOT . "var/");

// Define application and system paths
define("SYSPATH", DOCROOT . "system/");
define('APPPATH', DOCROOT . "application/");
define('MODPATH', DOCROOT . "modules/");
define('THEMEPATH', DOCROOT . "themes/");

$modules_list = null;
$active_modules = null;

function setup($config) {
  system("rm -rf tmp");
  mkdir("tmp");

  // Lets backup the database
  $conn = $config["connection"];
  do_system("mysqldump -u{$conn['user']} -p{$conn['pass']} -h{$conn['host']}  --add-drop-table " .
            "--compact {$conn['database']} > tmp/dump.sql");

  $db = Database::instance();
  // Drop all tables
  foreach ($db->list_tables() as $table) {
    $db->query("DROP TABLE IF EXISTS `$table`");
  }

  // Now the var directory
  rename (VARPATH, realpath("tmp/var"));
  mkdir(VARPATH);
  copy(realpath("tmp/var/database.php"), VARPATH . "database.php");

  $db->clear_cache();
  $modules_list = module::$modules;
  $active_modules = module::$active;

  module::$modules = array();
  module::$active = array();

  // Use a known random seed so that subsequent packaging runs will reuse the same random
  // numbers, keeping our install.sql file more stable.
  srand(0);
}

function reset_install($config) {
  // Reset the var path
  system("rm -rf " . VARPATH);
  rename (realpath("tmp/var"), VARPATH);

  $db = Database::instance();
  // Drop all tables
  foreach ($db->list_tables() as $table) {
    $db->query("DROP TABLE IF EXISTS `$table`");
  }

  // Lets restorep the database
  $conn = $config["connection"];
  do_system("mysql -u{$conn['user']} -p{$conn['pass']} {$conn['database']} " .
         " < tmp/dump.sql");

  // Clear any database caching
  $db->clear_cache();

  module::$modules = $modules_list;
  module::$active = $active_modules;
}

function do_system($command) {
  exec($command, $output, $status);
  if ($status) {
    throw new Exception("$command\nFailed to dump database\n" . implode("\n", $output));
  }
}

function kohana_bootstrap() {
  define('KOHANA_VERSION',  '2.3.3');
  define('KOHANA_CODENAME', 'aegolius');

  // Test of Kohana is running in Windows
  define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

  // Kohana benchmarks are prefixed to prevent collisions
  define('SYSTEM_BENCHMARK', 'system_benchmark');

  // Load benchmarking support
  require SYSPATH.'core/Benchmark'.EXT;

  // Start total_execution
  Benchmark::start(SYSTEM_BENCHMARK.'_total_execution');

  // Start kohana_loading
  Benchmark::start(SYSTEM_BENCHMARK.'_kohana_loading');

  // Load core files
  require SYSPATH.'core/utf8'.EXT;
  require SYSPATH.'core/Event'.EXT;
  require SYSPATH.'core/Kohana'.EXT;

  // Prepare the environment
  Kohana::setup();
  // End kohana_loading
  Benchmark::stop(SYSTEM_BENCHMARK.'_kohana_loading');

  // Prepare the system
  Event::run('system.ready');

  // Clean up and exit (this basically shuts down output buffering
  Event::run('system.shutdown');
}

function install() {
  gallery_installer::install(true);
  module::load_modules();

  foreach (array("user", "comment", "organize", "info", "rss",
                 "search", "slideshow", "tag") as $module_name) {
    module::install($module_name);
    module::activate($module_name);
  }
}

function dump_database() {
  // We now have a clean install with just the packages that we want.  Make sure that the
  // database is clean too.
  $db = Database::instance();
  $db->query("TRUNCATE {sessions}");
  $db->query("TRUNCATE {logs}");
  $db->query("DELETE FROM {vars} WHERE `module_name` = 'gallery' AND `name` = '_cache'");
  $db->update("users", array("password" => ""), array("id" => 1));
  $db->update("users", array("password" => ""), array("id" => 2));

  $dbconfig = Kohana::config('database.default');
  $conn = $dbconfig["connection"];
  $pass = $conn["pass"] ? "-p{$conn['pass']}" : "";
  $sql_file = DOCROOT . "installer/install.sql";
  if (!is_writable($sql_file)) {
    throw new Exception("$sql_file is not writeable");
    return;
  }
  do_system("mysqldump --compact --add-drop-table -h{$conn['host']} " .
            "-u{$conn['user']} $pass {$conn['database']} > $sql_file");

  // Post-process the sql file
  $buf = "";
  $root = ORM::factory("item", 1);
  $root_created_timestamp = $root->created;
  $root_updated_timestamp = $root->updated;
  foreach (file($sql_file) as $line) {
    // Prefix tables
    $line = preg_replace(
      "/(CREATE TABLE|IF EXISTS|INSERT INTO) `{$dbconfig['table_prefix']}(\w+)`/", "\\1 {\\2}",
      $line);

    // Normalize dates
    $line = preg_replace("/,$root_created_timestamp,/", ",UNIX_TIMESTAMP(),", $line);
    $line = preg_replace("/,$root_updated_timestamp,/", ",UNIX_TIMESTAMP(),", $line);
    $buf .= $line;
  }
  $fd = fopen($sql_file, "wb");
  fwrite($fd, $buf);
  fclose($fd);
}

function dump_var() {
  $objects = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(VARPATH),
    RecursiveIteratorIterator::SELF_FIRST);

  $var_file = DOCROOT . "installer/init_var.php";
  if (!is_writable($var_file)) {
    throw new Exception("$var_file is not writeable");
    return;
  }

  $paths = array();
  foreach($objects as $name => $file) {
    if ($file->getBasename() == "database.php") {
      continue;
    } else if (basename($file->getPath()) == "logs") {
      continue;
    }

    if ($file->isDir()) {
      $paths[] = "VARPATH . \"" . substr($name, strlen(VARPATH)) . "\"";
    } else {
      // @todo: serialize non-directories
      throw new Exception("Unknown file: $name");
    }
  }
  // Sort the paths so that the var file is stable
  sort($paths);

  $fd = fopen($var_file, "w");
  fwrite($fd, "<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n");
  fwrite($fd, "<?php\n");
  foreach ($paths as $path) {
    fwrite($fd, "!file_exists($path) && mkdir($path);\n");
  }
  fclose($fd);
}

// Bootstrap kohana so we can use it as we need it
kohana_bootstrap();

$config = Kohana::config("database.default");

try {
  // Empty the tmp directory, backup the database, and copy the var directory
  setup($config);
} catch (Exception $e) {
  print $e->getTrace();
  return;
}

try {
  // Install the standard modules
  install();

  // Dump the empty gallery3 database and format it for the installer
  dump_database();

  // Dump the var directory
  dump_var();
} catch (Exception $e) {
  print $e->getTrace();
}

try {
  // Reset the Gallery3 installation
  reset_install($config);
} catch (Exception $e) {
  print $e->getTrace();
}

system("rm -rf tmp");
?>