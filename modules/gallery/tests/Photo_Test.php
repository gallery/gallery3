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
class Photo_Test extends Unittest_TestCase {
  public function test_get_file_metadata() {
    $photo = Test::random_photo();
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($photo->file_path()));
  }

  /**
   * @expectedException Gallery_Exception
   */
  public function test_get_file_metadata_with_non_existent_file() {
    $metadata = Photo::get_file_metadata(MODPATH . "gallery/tests/this_does_not_exist");
  }

  public function test_get_file_metadata_with_no_extension() {
    copy(MODPATH . "gallery_unittest/assets/test.jpg", TMPPATH . "test_jpg_with_no_extension");
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"),
                        Photo::get_file_metadata(TMPPATH . "test_jpg_with_no_extension"));
    unlink(TMPPATH . "test_jpg_with_no_extension");
  }

  /**
   * @expectedException Gallery_Exception
   */
  public function test_get_file_metadata_with_illegal_extension() {
    $metadata = Photo::get_file_metadata(MODPATH . "gallery/tests/Photo_Test.php");
    $this->assertTrue(false, "Shouldn't get here");
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
    copy(MODPATH . "gallery/tests/Photo_Test.php", TMPPATH . "test_php_with_jpg_extension.jpg");
    try {
      $metadata = Photo::get_file_metadata(TMPPATH . "test_php_with_jpg_extension.jpg");
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
    unlink(TMPPATH . "test_php_with_jpg_extension.jpg");
  }

  public function test_get_file_iptc_data() {
    // This is what we'll embed in the file...
    $iptc = array(
      "025" => array("foo, bar ", "baz\0"),
      "120" => array("Hello", "World!", "wîth àçcéñts")
    );

    // ... and this is what we expect out.  Note: this test file is not UTF-8 encoded.
    $expected = array(
      "Keywords" => array("foo, bar ", "baz"),
      "Caption"  => array("Hello", "World!", utf8_encode("wîth àçcéñts"))
    );

    // Build the test file.
    $path = TMPPATH . "test_jpg_with_iptc.jpg";
    copy(MODPATH . "gallery_unittest/assets/test.jpg", $path);
    $data = "";
    foreach($iptc as $code => $values) {
      foreach ($values as $value) {
        $data .= $this->_iptc_make_tag(2, $code, $value);
      }
    }
    file_put_contents($path, iptcembed($data, $path));

    $this->assertEquals($expected, Photo::get_file_iptc($path));
  }

  // iptc_make_tag() function by Thies C. Arntzen
  // @see  http://php.net/manual/en/function.iptcembed.php
  protected function _iptc_make_tag($rec, $data, $value) {
    $length = strlen($value);
    $retval = chr(0x1C) . chr($rec) . chr($data);

    if($length < 0x8000) {
      $retval .= chr($length >> 8) .  chr($length & 0xFF);
    } else {
      $retval .= chr(0x80) .
                 chr(0x04) .
                 chr(($length >> 24) & 0xFF) .
                 chr(($length >> 16) & 0xFF) .
                 chr(($length >> 8) & 0xFF) .
                 chr($length & 0xFF);
    }

    return $retval . $value;
  }
}
