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
class Gallery_Unit_Test_Controller extends Controller {
  function Index() {
    if (!TEST_MODE) {
      print Kohana::show_404();
    }

    // Jump through some hoops to satisfy the way that we check for the site_domain in
    // config.php.  We structure this such that the code in config will leave us with a
    // site_domain of "." (for historical reasons)
    // @todo: for tests, we should force the site_domain to something like example.com
    $_SERVER["SCRIPT_FILENAME"] = "index.php";
    $_SERVER["SCRIPT_NAME"] = "./index.php";

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
        Kohana::config_set('database.unit_test.benchmark', $default['benchmark']);
        Kohana::config_set('database.unit_test.persistent', $default['persistent']);
        Kohana::config_set('database.unit_test.connection.type', $conn['type']);
        Kohana::config_set('database.unit_test.connection.user', $conn['user']);
        Kohana::config_set('database.unit_test.connection.pass', $conn['pass']);
        Kohana::config_set('database.unit_test.connection.host', $conn['host']);
        Kohana::config_set('database.unit_test.connection.port', $conn['port']);
        Kohana::config_set('database.unit_test.connection.socket', $conn['socket']);
        Kohana::config_set('database.unit_test.connection.database', "{$conn['database']}_test");
        Kohana::config_set('database.unit_test.character_set', $default['character_set']);
        Kohana::config_set('database.unit_test.table_prefix', $default['table_prefix']);
        Kohana::config_set('database.unit_test.object', $default['object']);
        Kohana::config_set('database.unit_test.cache', $default['cache']);
        Kohana::config_set('database.unit_test.escape', $default['escape']);
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
        Database::$instances = array('default' => $db);
      } catch (Exception $e) {
        print "{$e->getMessage()}\n";
        return;
      }
    }

    try {
      // Clean out the database
      if ($tables = $db->list_tables()) {
        foreach ($db->list_tables() as $table) {
          $db->query("DROP TABLE $table");
        }
      }

      // Clean out the filesystem
      @system("rm -rf test/var");
      @mkdir('test/var/logs', 0777, true);

      $active_modules = module::$active;

      // Reset our caches
      module::$modules = array();
      module::$active = array();
      module::$var_cache = array();
      $db->clear_cache();

      // Rest the cascading class path
      Kohana::config_set("core", Kohana::config_load("core"));

      // Install the active modules
      // Force gallery and user to be installed first to resolve dependencies.
      gallery_installer::install(true);
      module::load_modules();

      module::install("user");
      module::activate("user");
      $modules = $paths =array();
      foreach ($active_modules as $module) {
        if (file_exists($path =  MODPATH . "{$module->name}/tests")) {
          $paths[] = $path;
        }
        if (in_array($module->name, array("gallery", "user"))) {
          continue;
        }
        module::install($module->name);
        module::activate($module->name);
      }

      Kohana::config_set('unit_test.paths', $paths);

      // Trigger late-binding install actions (defined in gallery_event::user_login)
      graphics::choose_default_toolkit();

      $filter = count($_SERVER["argv"]) > 2 ? $_SERVER["argv"][2] : null;
      print new Unit_Test($modules, $filter);
    } catch (Exception $e) {
      print "Exception: {$e->getMessage()}\n";
      print $e->getTraceAsString() . "\n";
    }
  }
}
