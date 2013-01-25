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
class Movie_Helper_Test extends Gallery_Unit_Test_Case {
  public function seconds_to_hhmmssdd_test() {
    $times = array("00:00:00.50" => 0.5,
                   "00:00:06.00" => 6,
                   "00:00:59.99" => 59.999,
                   "00:01:00.00" => 60.001,
                   "00:07:00.00" => 7 * 60,
                   "00:45:19.00" => 45 * 60 + 19,
                   "03:45:19.00" => 3 * 3600 + 45 * 60 + 19,
                   "126:45:19.00" => 126 * 3600 + 45 * 60 + 19);
    foreach ($times as $hhmmssdd => $seconds) {
      $this->assert_equal($hhmmssdd, movie::seconds_to_hhmmssdd($seconds));
    }
  }

  public function hhmmssdd_to_seconds_test() {
    $times = array("0:00:00.01" => 0.01,
                   "00:00:00.50" => 0.5,
                   "00:00:06.00" => 6,
                   "00:00:59.99" => 59.99,
                   "00:01:00.00" => 60.00,
                   "00:07:00.00" => 7 * 60,
                   "00:45:19.00" => 45 * 60 + 19,
                   "03:45:19.00" => 3 * 3600 + 45 * 60 + 19,
                   "126:45:19.00" => 126 * 3600 + 45 * 60 + 19);
    foreach ($times as $hhmmssdd => $seconds) {
      $this->assert_equal($seconds, movie::hhmmssdd_to_seconds($hhmmssdd));
    }
  }

  public function get_file_metadata_test() {
    $movie = test::random_movie();
    $this->assert_equal(array(360, 288, "video/x-flv", "flv", 6.00),
                        movie::get_file_metadata($movie->file_path()));
  }

  public function get_file_metadata_with_non_existent_file_test() {
    try {
      $metadata = movie::get_file_metadata(MODPATH . "gallery/tests/this_does_not_exist");
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function get_file_metadata_with_no_extension_test() {
    copy(MODPATH . "gallery/tests/test.flv", TMPPATH . "test_flv_with_no_extension");
    $this->assert_equal(array(360, 288, null, null, 6.00),
                        movie::get_file_metadata(TMPPATH . "test_flv_with_no_extension"));
  }

  public function get_file_metadata_with_illegal_extension_test() {
    $this->assert_equal(array(0, 0, null, null, 0),
                        movie::get_file_metadata(MODPATH . "gallery/tests/Movie_Helper_Test.php"));
  }

  public function get_file_metadata_with_illegal_extension_but_valid_file_contents_test() {
    copy(MODPATH . "gallery/tests/test.flv", TMPPATH . "test_flv_with_php_extension.php");
    $this->assert_equal(array(360, 288, null, null, 6.00),
                        movie::get_file_metadata(TMPPATH . "test_flv_with_php_extension.php"));
  }
}
