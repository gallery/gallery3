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
class Gallery_Unit_Test_Controller extends Controller {
  function Index() {
    if (!defined('TEST_MODE')) {
      print Kohana::show_404();
    }

    $original_config = DOCROOT . "var/database.php";
    $test_config = VARPATH . "database.php";
    if (!file_exists($original_config)) {
      print "Please copy kohana/config/database.php to $original_config.\n";
      print "Then, add a \$config['unit_test'] database configuration in $original_config.\n";
      return;
    } else {
      copy($original_config, $test_config);
      $db_config = Kohana::config('database');
      if (empty($db_config['unit_test'])) {
        print "Please create a \$config['unit_test'] database configuration in $test_config.\n";
        return;
      }

      if ($db_config['default']['connection']['database'] ==
          $db_config['unit_test']['connection']['database']) {
        print "Don't use the default database for your unit tests or you'll lose all your data.\n";
        print "Try using '{$db_config['default']['connection']['database']}_test' in $test_config\n";
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

    // Find all tests, excluding sample tests that come with the unit_test module.
    $paths = array(APPPATH . "tests");
    foreach (glob(MODPATH . "*/tests") as $path) {
      if ($path != MODPATH . "unit_test/tests") {
        $paths[] = $path;
      }
    }
    Kohana::config_set('unit_test.paths', $paths);

    // Clean out the database
    foreach ($db->list_tables() as $table) {
      $db->query("DROP TABLE $table");
    }

    // Clean out the filesystem
    @system("rm -rf test/var");
    @system('mkdir -p test/var/logs');

    // Install all modules
    core_installer::install();
    $modules = array();
    foreach (glob(MODPATH . "*/helpers/*_installer.php") as $file) {
      $module_name = basename(dirname(dirname($file)));
      if ($module_name == "core") {
        continue;
      }

      require_once(DOCROOT . "modules/${module_name}/helpers/${module_name}_installer.php");

      $test_dir = MODPATH . "$module_name/tests";
      if (file_exists($test_dir)) {
        $modules[] = $test_dir;
      }

      $installer_class = "{$module_name}_installer";
      if (method_exists($installer_class, "install")) {
        call_user_func_array(array($installer_class, "install"), array());
      }
    }

    $filter = count($_SERVER["argv"]) > 2 ? $_SERVER["argv"][2] : null;
    print new Unit_Test($modules, $filter);
  }
}
