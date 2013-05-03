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
class Movie_Helper_Test extends Unittest_TestCase {
  public function test_seconds_to_hhmmssdd() {
    $times = array("00:00:00.50" => 0.5,
                   "00:00:06.00" => 6,
                   "00:00:59.99" => 59.999,
                   "00:01:00.00" => 60.001,
                   "00:07:00.00" => 7 * 60,
                   "00:45:19.00" => 45 * 60 + 19,
                   "03:45:19.00" => 3 * 3600 + 45 * 60 + 19,
                   "126:45:19.00" => 126 * 3600 + 45 * 60 + 19);
    foreach ($times as $hhmmssdd => $seconds) {
      $this->assertEquals($hhmmssdd, Movie::seconds_to_hhmmssdd($seconds));
    }
  }

  public function test_hhmmssdd_to_seconds() {
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
      $this->assertEquals($seconds, Movie::hhmmssdd_to_seconds($hhmmssdd));
    }
  }

  public function test_get_file_metadata() {
    $movie = Test::random_movie();
    $this->assertEquals(array(360, 288, "video/x-flv", "flv", 6.00),
                        Movie::get_file_metadata($movie->file_path()));
  }

  public function test_get_file_metadata_with_non_existent_file() {
    try {
      $metadata = Movie::get_file_metadata(MODPATH . "gallery/tests/this_does_not_exist");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function test_get_file_metadata_with_no_extension() {
    copy(MODPATH . "gallery_unittest/assets/test.flv", TMPPATH . "test_flv_with_no_extension");
    // Since mime type and extension are based solely on the filename, this is considered invalid.
    try {
      $metadata = Movie::get_file_metadata(TMPPATH . "test_flv_with_no_extension");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
    unlink(TMPPATH . "test_flv_with_no_extension");
  }

  public function test_get_file_metadata_with_illegal_extension() {
    try {
      $metadata = Movie::get_file_metadata(MODPATH . "gallery/tests/Movie_Helper_Test.php");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function test_get_file_metadata_with_illegal_extension_but_valid_file_contents() {
    copy(MODPATH . "gallery_unittest/assets/test.flv", TMPPATH . "test_flv_with_php_extension.php");
    // Since mime type and extension are based solely on the filename, this is considered invalid.
    try {
      $metadata = Movie::get_file_metadata(TMPPATH . "test_flv_with_php_extension.php");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
    unlink(TMPPATH . "test_flv_with_php_extension.php");
  }

  public function test_get_file_metadata_with_valid_extension_but_illegal_file_contents() {
    copy(MODPATH . "gallery/tests/Photo_Helper_Test.php", TMPPATH . "test_php_with_flv_extension.flv");
    // Since mime type and extension are based solely on the filename, this is considered valid.
    // Of course, FFmpeg cannot extract width, height, or duration from the file.  Note that this
    // isn't a really a security problem, since the filename doesn't have a php extension and
    // therefore will never be executed.
    $this->assertEquals(array(0, 0, "video/x-flv", "flv", 0),
                        Movie::get_file_metadata(TMPPATH . "test_php_with_flv_extension.flv"));
    unlink(TMPPATH . "test_php_with_flv_extension.flv");
  }
}
