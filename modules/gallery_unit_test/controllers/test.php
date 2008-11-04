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
class Test_Controller extends Controller {
  function Index() {
    if (!defined('TEST_MODE')) {
      print Kohana::show_404();
    }

    $original_config = DOCROOT . "var/database.php";
    $test_config = VARPATH . "database.php";
    if (!file_exists($original_config)) {
      print "Please create $original and create a 'unit_test' database configuration.\n";
    } else {
      copy($original_config, $test_config);
      $db_config = Kohana::config('database');
      if (empty($db_config['unit_test'])) {
        print "Please create create a 'unit_test' database configuration in $db_config.\n";
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

    // We probably don't want to uninstall and reinstall the core every time, but let's start off
    // this way.
    core_installer::uninstall();
    core_installer::install();

    print new Unit_Test();
  }
}
