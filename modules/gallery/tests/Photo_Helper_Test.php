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
class Photo_Helper_Test extends Unittest_TestCase {
  public function test_get_file_metadata() {
    $photo = Test::random_photo();
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($photo->file_path()));
  }

  public function test_get_file_metadata_with_non_existent_file() {
    try {
      $metadata = Photo::get_file_metadata(MODPATH . "gallery/tests/this_does_not_exist");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function test_get_file_metadata_with_no_extension() {
    copy(MODPATH . "gallery_unittest/assets/test.jpg", TMPPATH . "test_jpg_with_no_extension");
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"),
                        Photo::get_file_metadata(TMPPATH . "test_jpg_with_no_extension"));
    unlink(TMPPATH . "test_jpg_with_no_extension");
  }

  public function test_get_file_metadata_with_illegal_extension() {
    try {
      $metadata = Photo::get_file_metadata(MODPATH . "gallery/tests/Photo_Helper_Test.php");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function test_get_file_metadata_with_illegal_extension_but_valid_file_contents() {
    // This ensures that we correctly "re-type" files with invalid extensions if the contents
    // themselves are valid.  This is needed to ensure that issues similar to those corrected by
    // ticket #1855, where an image that looked valid (header said jpg) with a php extension was
    // previously accepted without changing its extension, do not arise and cause security issues.
    copy(MODPATH . "gallery_unittest/assets/test.jpg", TMPPATH . "test_jpg_with_php_extension.php");
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"),
                        Photo::get_file_metadata(TMPPATH . "test_jpg_with_php_extension.php"));
    unlink(TMPPATH . "test_jpg_with_php_extension.php");
  }

  public function test_get_file_metadata_with_valid_extension_but_illegal_file_contents() {
    copy(MODPATH . "gallery/tests/Photo_Helper_Test.php", TMPPATH . "test_php_with_jpg_extension.jpg");
    try {
      $metadata = Photo::get_file_metadata(TMPPATH . "test_php_with_jpg_extension.jpg");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
    unlink(TMPPATH . "test_php_with_jpg_extension.jpg");
  }
}
