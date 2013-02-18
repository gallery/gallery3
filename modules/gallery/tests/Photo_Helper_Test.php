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
class Photo_Helper_Test extends Gallery_Unit_Test_Case {
  public function get_file_metadata_test() {
    $photo = test::random_photo();
    $this->assert_equal(array(1024, 768, "image/jpeg", "jpg"),
                        photo::get_file_metadata($photo->file_path()));
  }

  public function get_file_metadata_with_non_existent_file_test() {
    try {
      $metadata = photo::get_file_metadata(MODPATH . "gallery/tests/this_does_not_exist");
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function get_file_metadata_with_no_extension_test() {
    copy(MODPATH . "gallery/tests/test.jpg", TMPPATH . "test_jpg_with_no_extension");
    $this->assert_equal(array(1024, 768, "image/jpeg", "jpg"),
                        photo::get_file_metadata(TMPPATH . "test_jpg_with_no_extension"));
    unlink(TMPPATH . "test_jpg_with_no_extension");
  }

  public function get_file_metadata_with_illegal_extension_test() {
    try {
      $metadata = photo::get_file_metadata(MODPATH . "gallery/tests/Photo_Helper_Test.php");
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function get_file_metadata_with_illegal_extension_but_valid_file_contents_test() {
    // This ensures that we correctly "re-type" files with invalid extensions if the contents
    // themselves are valid.  This is needed to ensure that issues similar to those corrected by
    // ticket #1855, where an image that looked valid (header said jpg) with a php extension was
    // previously accepted without changing its extension, do not arise and cause security issues.
    copy(MODPATH . "gallery/tests/test.jpg", TMPPATH . "test_jpg_with_php_extension.php");
    $this->assert_equal(array(1024, 768, "image/jpeg", "jpg"),
                        photo::get_file_metadata(TMPPATH . "test_jpg_with_php_extension.php"));
    unlink(TMPPATH . "test_jpg_with_php_extension.php");
  }

  public function get_file_metadata_with_valid_extension_but_illegal_file_contents_test() {
    copy(MODPATH . "gallery/tests/Photo_Helper_Test.php", TMPPATH . "test_php_with_jpg_extension.jpg");
    try {
      $metadata = photo::get_file_metadata(TMPPATH . "test_php_with_jpg_extension.jpg");
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
    unlink(TMPPATH . "test_php_with_jpg_extension.jpg");
  }
}
