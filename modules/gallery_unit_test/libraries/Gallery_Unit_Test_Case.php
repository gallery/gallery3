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
class Gallery_Unit_Test_Case extends Unit_Test_Case {
  public function assert_equal_array($expected, $actual, $debug=null) {
    if ($expected !== $actual) {
      throw new Kohana_Unit_Test_Exception(
        sprintf("Expected (%s) %s but received (%s) %s\n Diff: %s",
                gettype($expected), var_export($expected, true),
                gettype($actual), var_export($actual, true),
                test::diff(var_export($expected, true), var_export($actual, true))),
        $debug);
    }
    return $this;
  }

  public function assert_array_equal_to_json($expected_array, $actual_json, $debug=null) {
    return $this->assert_equal_array($expected_array, json_decode($actual_json, true), $debug);
  }
}
