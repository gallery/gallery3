<?php
/*
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

    // Find all tests, excluding sample tests that come with the unit_test module.
    $paths = array(APPPATH . "tests");
    foreach (glob(MODPATH . "*/tests") as $path) {
      if ($path != MODPATH . "unit_test/tests") {
	$paths[] = $path;
      }
    }
    Kohana::config_set('unit_test.paths', $paths);

    print new Unit_Test();
  }
}
