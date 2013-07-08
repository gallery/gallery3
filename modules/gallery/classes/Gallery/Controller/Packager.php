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
class Gallery_Controller_Packager extends Controller {
  public function action_package() {
    if (PHP_SAPI != "cli") {
      throw HTTP_Exception::factory(403);
    }

    $_SERVER["SERVER_NAME"] = "example.com";

    try {
      $this->_reset();                // empty and reinstall the standard modules
      $this->_dump_database();        // Dump the database
      $this->_dump_var();             // Dump the var directory
    } catch (Exception $e) {
      $this->response->body($e->getMessage() . "\n" . $e->getTraceAsString());
      return;
    }

    $this->response->body("Successfully wrote install.sql and init_var.php\n");
  }

  protected function _reset() {
    // Drop all tables
    foreach (Database::instance()->list_tables() as $table) {
      Database::instance()->query(Database::DROP, "DROP TABLE IF EXISTS {{$table}}");
    }
    ORM::reinitialize();

    // Clean out data
    System::unlink_dir(VARPATH . "uploads");
    System::unlink_dir(VARPATH . "albums");
    System::unlink_dir(VARPATH . "resizes");
    System::unlink_dir(VARPATH . "thumbs");
    System::unlink_dir(VARPATH . "modules");
    System::unlink_dir(VARPATH . "tmp");

    Module::$installed = array();
    Module::$active = array();

    // Use a known random seed so that subsequent packaging runs will reuse the same random
    // numbers, keeping our install.sql file more stable.
    srand(0);

    foreach (array("gallery", "purifier", "user", "comment", "organize", "info",
                   "rss", "search", "slideshow", "tag") as $module_name) {
      Module::install($module_name);
      Module::activate($module_name);
    }
  }

  protected function _dump_database() {
    // We now have a clean install with just the packages that we want.  Make sure that the
    // database is clean too.
    $i = 1;
    foreach (array("dashboard_sidebar", "dashboard_center", "site_sidebar") as $key) {
      $blocks = array();
      foreach (unserialize(Module::get_var("gallery", "blocks_{$key}")) as $rnd => $value) {
        $blocks[++$i] = $value;
      }
      Module::set_var("gallery", "blocks_{$key}", serialize($blocks));
    }

    Database::instance()->query(Database::TRUNCATE, "TRUNCATE {caches}");
    Database::instance()->query(Database::TRUNCATE, "TRUNCATE {sessions}");
    Database::instance()->query(Database::TRUNCATE, "TRUNCATE {logs}");
    DB::update("users")
      ->set(array("password" => ""))
      ->where("id", "in", array(1, 2))
      ->execute();

    $dbconfig = Kohana::$config->load('database.default');
    $conn = $dbconfig["connection"];
    $sql_file = DOCROOT . "installer/install.sql";
    if (!is_writable($sql_file)) {
      $this->response->body("$sql_file is not writeable");
      return;
    }
    $command = sprintf(
      "mysqldump --compact --skip-extended-insert --add-drop-table %s %s %s %s > $sql_file",
      escapeshellarg("-h{$conn['hostname']}"),
      escapeshellarg("-u{$conn['username']}"),
      $conn['password'] ? escapeshellarg("-p{$conn['password']}") : "",
      escapeshellarg($conn['database']));
    exec($command, $output, $status);
    if ($status) {
      $this->response->body("<pre>" .
                            "$command\n" .
                            "Failed to dump database\n" .
                            implode("\n", $output));
      return;
    }

    // Post-process the sql file
    $buf = "";
    $root = Item::root();
    $root_created_timestamp = $root->created;
    $root_updated_timestamp = $root->updated;
    $table_name = "";
    foreach (file($sql_file) as $line) {
      // Prefix tables
      $line = preg_replace(
        "/(CREATE TABLE|IF EXISTS|INSERT INTO) `{$dbconfig['table_prefix']}(\w+)`/", "\\1 {\\2}",
        $line);

      if (preg_match("/CREATE TABLE {(\w+)}/", $line, $matches)) {
        $table_name = $matches[1];
      }
      // Normalize dates
      $line = preg_replace("/,$root_created_timestamp,/", ",UNIX_TIMESTAMP(),", $line);
      $line = preg_replace("/,$root_updated_timestamp,/", ",UNIX_TIMESTAMP(),", $line);

      // Remove ENGINE= specifications execpt for search records, it always needs to be MyISAM
      if ($table_name != "search_records") {
        $line = preg_replace("/ENGINE=\S+ /", "", $line);
      }

      // Null out ids in the vars table since it's an auto_increment table and this will result in
      // more stable values so we'll have less churn in install.sql.
      $line = preg_replace(
        "/^INSERT INTO {vars} VALUES \(\d+/", "INSERT INTO {vars} VALUES (NULL", $line);

      $buf .= $line;
    }
    $fd = fopen($sql_file, "wb");
    fwrite($fd, $buf);
    fclose($fd);
  }

  protected function _dump_var() {
    $objects = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator(VARPATH),
      RecursiveIteratorIterator::SELF_FIRST);

    $var_file = DOCROOT . "installer/init_var.php";
    if (!is_writable($var_file)) {
      $this->response->body("$var_file is not writeable");
      return;
    }

    $paths = array();
    foreach ($objects as $name => $file) {
      $path = $file->getPath();
      $basename = $file->getBasename();
      if ($basename == "database.php" || $basename == "." || $basename == "..") {
        continue;
      } else if (in_array(basename($path), array("logs", "tmp")) && ($basename != ".htaccess")) {
        continue;
      } else if (strpos($name, Kohana::$cache_dir . "/HTML") === 0) {  // HTMLPurifier cache
        continue;
      }

      if ($file->isDir()) {
        $paths[] = "VARPATH . \"" . substr($name, strlen(VARPATH)) . "\"";
      } else {
        // @todo: serialize non-directories
        $files["VARPATH . \"" . substr($name, strlen(VARPATH)) . "\""] =
          base64_encode(file_get_contents($name));
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
    ksort($files);
    foreach ($files as $file => $contents) {
      fwrite($fd, "file_put_contents($file, base64_decode(\"$contents\"));\n");
    }
    fclose($fd);
  }
}