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
class GalleryUnittest_Controller_GalleryUnittest extends Controller {
  public function action_index() {
    if (!TEST_MODE) {
      throw HTTP_Exception::factory(404);
    }

    // Disable output buffering
    if (($ob_len = ob_get_length()) !== false) {
      // flush_end on an empty buffer causes headers to be sent. Only flush if needed.
      if ($ob_len > 0) {
        ob_end_flush();
      } else {
        ob_end_clean();
      }
    }

    // Force strict behavior to flush out bugs early.  Even in PHP <5.4, -1 includes E_STRICT.
    ini_set("display_errors", true);
    error_reporting(-1);

    // Jump through some hoops to satisfy the way that we check for the site_domain in
    // config.php.  We structure this such that the code in config will leave us with a
    // site_domain of "." (for historical reasons)
    // @todo: for tests, we should force the site_domain to something like example.com
    $_SERVER["SCRIPT_FILENAME"] = "index.php";
    $_SERVER["SCRIPT_NAME"] = "./index.php";
    $_SERVER["SERVER_NAME"] = "localhost";

    // Switch over to our test database instance
    Database::$instances["default"]->disconnect();
    $db_config = Kohana::$config->load("database");
    $db_config["default"]["connection"]["database"] .= "_test";
    $db_config["default"]["table_prefix"] = "g3_";  // Database_Test tests how prefixes are handled.
    $db = Database::instance();
    ORM::reinitialize();
    Session::instance()->reconnect_db();

    try {
      // Clean out the database
      if ($tables = $db->list_tables()) {
        foreach ($db->list_tables() as $table) {
          $db->query(Database::DROP, "DROP TABLE `$table`");
        }
      }

      // Clean out the filesystem.  Note that this cleans out test/var/database.php, but that's ok
      // because we technically don't need it anymore.  If this is confusing, we could always
      // arrange to preserve that one file.
      @system("rm -rf test/var");
      @mkdir("test/var/logs", 0777, true);
      @mkdir("test/var/cache", 0777, true);

      // Reset our caches
      Module::$installed = array();
      Module::$active = array();
      Module::$var_cache = array();

      // Install the active modules
      Module::install("gallery");
      Module::activate("gallery");
      Module::install("user");
      Module::activate("user");
      foreach (Module::available() as $module_name => $unused) {
        if (file_exists(MODPATH . "{$module_name}/tests") &&
            !in_array($module_name, array("gallery", "user"))) {
          Module::install($module_name);
          Module::activate($module_name);
        }
      }

      // Re-initialize the active modules.  This clears the routes, then re-runs gallery_ready.
      Route::clear_all();
      Module::event("gallery_ready");

      // Trigger late-binding install actions (defined in Hook_GalleryEvent::user_login)
      Graphics::choose_default_toolkit();

      // Rework the cli arguments to look like a traditional phpunit execution
      // i.e. "index.php test [switches]" --> "phpunit [switches] [Unittest_Tests path]"
      array_splice($_SERVER["argv"], 0, 2, "phpunit");
      $_SERVER["argv"][] = MODPATH . "unittest/classes/Unittest/Tests.php";

      // Look for PHPUnit in a bunch of reasonable places
      foreach (array("phar://" . DOCROOT . "bin/phpunit.phar",
                     "/usr/local/php/PHPUnit/Autoload.php",
                     "/usr/share/php/PHPUnit/Autoload.php") as $path) {
        @include $path;
      }

      if (!function_exists("phpunit_autoload")) {
        print "PHPUnit not found, aborting.  Download and use a standalone version of PHPUnit: \n";
        print "  $ cd gallery3/bin\n";
        print "  $ wget http://pear.phpunit.de/get/phpunit.phar\n";
        exit(1);
      }

      PHPUnit_TextUI_Command::main();
    } catch (ORM_Validation_Exception $e) {
      $errors = "";
      foreach ($e->errors() as $field => $msgs) {
        foreach ($msgs as $msg) {
          $errors .= "$field: $msg\n";
        }
      }
      print("Exception: {$e->getMessage()}\n" .
            $e->getFile() . ":" . $e->getLine() . "\n" .
            $e->getTraceAsString() . "\n" .
            $errors);
    } catch (Exception $e) {
      print("Exception: {$e->getMessage()}\n" .
            $e->getFile() . ":" . $e->getLine() . "\n" .
            $e->getTraceAsString() . "\n");
    }

    // @todo: we need to exit here with a failure count so that Travis knows that
    // the build was successful (or not!)
  }
}
