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
class Gallery_Unit_Test_Controller extends Controller {
  function index() {
    if (!TEST_MODE) {
      throw new Kohana_404_Exception();
    }

    // Force strict behavior to flush out bugs early
    ini_set("display_errors", true);
    //error_reporting(E_ALL & ~E_DEPRECATED);
    error_reporting(-1);

    // Jump through some hoops to satisfy the way that we check for the site_domain in
    // config.php.  We structure this such that the code in config will leave us with a
    // site_domain of "." (for historical reasons)
    // @todo: for tests, we should force the site_domain to something like example.com
    $_SERVER["SCRIPT_FILENAME"] = "index.php";
    $_SERVER["SCRIPT_NAME"] = "./index.php";

    $config = Kohana_Config::instance();
    $original_config = DOCROOT . "var/database.php";
    $test_config = VARPATH . "database.php";
    if (!file_exists($original_config)) {
      print "Please copy kohana/config/database.php to $original_config.\n";
      return;
    } else {
      copy($original_config, $test_config);
      $db_config = Kohana::config('database');
      if (empty($db_config['unit_test'])) {
        $default = $db_config['default'];
        $conn = $default['connection'];
        $config->set('database.unit_test.benchmark', $default['benchmark']);
        $config->set('database.unit_test.persistent', $default['persistent']);
        $config->set('database.unit_test.connection.type', $conn['type']);
        $config->set('database.unit_test.connection.user', $conn['user']);
        $config->set('database.unit_test.connection.pass', $conn['pass']);
        $config->set('database.unit_test.connection.host', $conn['host']);
        $config->set('database.unit_test.connection.port', $conn['port']);
        $config->set('database.unit_test.connection.socket', $conn['socket']);
        $config->set('database.unit_test.connection.database', "{$conn['database']}_test");
        $config->set('database.unit_test.character_set', $default['character_set']);
        $config->set('database.unit_test.table_prefix', $default['table_prefix']);
        $config->set('database.unit_test.object', $default['object']);
        $config->set('database.unit_test.cache', $default['cache']);
        $config->set('database.unit_test.escape', $default['escape']);
        $db_config = Kohana::config('database');
      }

      if ($db_config['default']['connection']['database'] ==
          $db_config['unit_test']['connection']['database']) {
        print "Don't use the default database for your unit tests or you'll lose all your data.\n";
        return;
      }

      try {
        $db = Database::instance('unit_test');
        $db->connect();

        // Make this the default database for the rest of this run
        Database::set_default_instance($db);
      } catch (Exception $e) {
        print "{$e->getMessage()}\n";
        return;
      }
    }

    try {
      // Clean out the database
      if ($tables = $db->list_tables()) {
        foreach ($db->list_tables() as $table) {
          $db->query("DROP TABLE {{$table}}");
        }
      }

      // Clean out the filesystem.  Note that this cleans out test/var/database.php, but that's ok
      // because we technically don't need it anymore.  If this is confusing, we could always
      // arrange to preserve that one file.
      @system("rm -rf test/var");
      @mkdir('test/var/logs', 0777, true);

      $active_modules = module::$active;

      // Reset our caches
      module::$modules = array();
      module::$active = array();
      module::$var_cache = array();
      $db->clear_cache();

      // Rest the cascading class path
      $config->set("core", $config->load("core"));

      // Install the active modules
      // Force gallery and user to be installed first to resolve dependencies.
      module::install("gallery");
      module::load_modules();

      module::install("user");
      module::activate("user");
      $modules = $paths = array();
      foreach (module::available() as $module_name => $unused) {
        if (in_array($module_name, array("gallery", "user"))) {
          $paths[] = MODPATH . "{$module_name}/tests";
          continue;
        }
        if (file_exists($path = MODPATH . "{$module_name}/tests")) {
          $paths[] = $path;
          module::install($module_name);
          module::activate($module_name);
        }
      }

      $config->set('unit_test.paths', $paths);

      // Trigger late-binding install actions (defined in gallery_event::user_login)
      graphics::choose_default_toolkit();

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
