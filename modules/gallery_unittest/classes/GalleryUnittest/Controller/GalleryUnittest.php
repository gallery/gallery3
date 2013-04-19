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

    $config = Kohana::$config;
    $original_config = DOCROOT . "var/database.php";

    // Switch over to our test database instance
    Database::$instances["default"]->disconnect();
    $db_config = Kohana::$config->load("database");
    $db_config["default"]["connection"]["database"] .= "_test";
    $db = Database::instance();
    ORM::reinitialize();

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
      @mkdir('test/var/logs', 0777, true);

      $active_modules = Module::$active;

      // Reset our caches
      Module::$modules = array();
      Module::$active = array();
      Module::$var_cache = array();

      // @todo do we need to do this in K3?
      // $db->clear_cache();

      // @todo: do we need this in K3?
      // Rest the cascading class path
      // $config->set("core", $config->load("core"));

      // Install the active modules
      // Force gallery and user to be installed first to resolve dependencies.

      Module::install("gallery");
      Module::load_modules();

      Module::install("user");
      Module::activate("user");
      $modules = $paths = array();
      foreach (Module::available() as $module_name => $unused) {
        if (in_array($module_name, array("gallery", "user"))) {
          $paths[] = MODPATH . "{$module_name}/tests";
          continue;
        }
        if (file_exists($path = MODPATH . "{$module_name}/tests")) {
          $paths[] = $path;
          Module::install($module_name);
          Module::activate($module_name);
        }
      }

      $config->set('unit_test.paths', $paths);

      // Trigger late-binding install actions (defined in Hook_GalleryEvent::user_login)
      Graphics::choose_default_toolkit();

      $filter = count($_SERVER["argv"]) > 2 ? $_SERVER["argv"][2] : null;
      $unit_test = new Unit_Test($modules, $filter);
      print $unit_test;
    } catch (ORM_Validation_Exception $e) {
      print "Validation Exception: {$e->getMessage()}\n";
      print $e->getTraceAsString() . "\n";
      foreach ($e->validation->errors() as $field => $msg) {
        print "$field: $msg\n";
      }
    } catch (Exception $e) {
      print "Exception: {$e->getMessage()}\n";
      print $e->getTraceAsString() . "\n";
    }

    if (!isset($unit_test)) {
      // If an exception is thrown, it's possible that $unit_test was never set.
      $failed = 1;
    } else {
      $failed = 0;
      foreach ($unit_test->stats as $class => $stats) {
        $failed += ($stats["failed"] + $stats["errors"]);
      }
    }
    if (PHP_SAPI == 'cli') {
      exit($failed);
    }
  }
}
