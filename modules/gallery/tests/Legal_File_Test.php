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
class Legal_File_Helper_Test extends Unittest_TestCase {
  public function test_get_photo_types_by_extension() {
    $this->assertEquals("image/jpeg", LegalFile::get_photo_types_by_extension("jpg")); // regular
    $this->assertEquals("image/jpeg", LegalFile::get_photo_types_by_extension("JPG")); // all caps
    $this->assertEquals("image/png", LegalFile::get_photo_types_by_extension("Png"));  // some caps
    $this->assertEquals(null, LegalFile::get_photo_types_by_extension("php"));      // invalid
    $this->assertEquals(null, LegalFile::get_photo_types_by_extension("php.jpg"));  // invalid w/ .

    // No extension returns full array
    $this->assertEquals(4, count(LegalFile::get_photo_types_by_extension()));
  }

  public function test_get_movie_types_by_extension() {
    $this->assertEquals("video/x-flv", LegalFile::get_movie_types_by_extension("flv")); // regular
    $this->assertEquals("video/x-flv", LegalFile::get_movie_types_by_extension("FLV")); // all caps
    $this->assertEquals("video/mp4", LegalFile::get_movie_types_by_extension("Mp4"));  // some caps
    $this->assertEquals(null, LegalFile::get_movie_types_by_extension("php"));     // invalid
    $this->assertEquals(null, LegalFile::get_movie_types_by_extension("php.flv")); // invalid w/ .

    // No extension returns full array
    $this->assertEquals(5, count(LegalFile::get_movie_types_by_extension()));
  }

  public function test_get_types_by_extension() {
    $this->assertEquals("image/jpeg", LegalFile::get_types_by_extension("jpg"));  // photo
    $this->assertEquals("video/x-flv", LegalFile::get_types_by_extension("FLV")); // movie
    $this->assertEquals(null, LegalFile::get_types_by_extension("php"));          // invalid
    $this->assertEquals(null, LegalFile::get_types_by_extension("php.flv"));      // invalid w/ .

    // No extension returns full array
    $this->assertEquals(9, count(LegalFile::get_types_by_extension()));
  }

  public function test_get_photo_extensions() {
    $this->assertEquals(true, LegalFile::get_photo_extensions("jpg"));      // regular
    $this->assertEquals(true, LegalFile::get_photo_extensions("JPG"));      // all caps
    $this->assertEquals(true, LegalFile::get_photo_extensions("Png"));      // some caps
    $this->assertEquals(false, LegalFile::get_photo_extensions("php"));     // invalid
    $this->assertEquals(false, LegalFile::get_photo_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assertEquals(4, count(LegalFile::get_photo_extensions()));
  }

  public function test_get_movie_extensions() {
    $this->assertEquals(true, LegalFile::get_movie_extensions("flv"));      // regular
    $this->assertEquals(true, LegalFile::get_movie_extensions("FLV"));      // all caps
    $this->assertEquals(true, LegalFile::get_movie_extensions("Mp4"));      // some caps
    $this->assertEquals(false, LegalFile::get_movie_extensions("php"));     // invalid
    $this->assertEquals(false, LegalFile::get_movie_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assertEquals(5, count(LegalFile::get_movie_extensions()));
  }

  public function test_get_extensions() {
    $this->assertEquals(true, LegalFile::get_extensions("jpg"));      // photo
    $this->assertEquals(true, LegalFile::get_extensions("FLV"));      // movie
    $this->assertEquals(false, LegalFile::get_extensions("php"));     // invalid
    $this->assertEquals(false, LegalFile::get_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assertEquals(9, count(LegalFile::get_extensions()));
  }

  public function test_get_filters() {
    // All 9 extensions both uppercase and lowercase
    $this->assertEquals(18, count(LegalFile::get_filters()));
  }

  public function test_get_photo_types() {
    // Note that this is one *less* than photo extensions since jpeg and jpg have the same mime.
    $this->assertEquals(3, count(LegalFile::get_photo_types()));
  }

  public function test_get_movie_types() {
    // Note that this is one *more* than movie extensions since video/flv is added.
    $this->assertEquals(6, count(LegalFile::get_movie_types()));
  }

  public function test_change_extension() {
    $this->assertEquals("foo.jpg", LegalFile::change_extension("foo.png", "jpg"));
  }

  public function test_change_four_letter_extension() {
    $this->assertEquals("foo.flv", LegalFile::change_extension("foo.mpeg", "flv"));
  }

  public function test_change_extension_with_no_extension() {
    $this->assertEquals("foo.flv", LegalFile::change_extension("foo", "flv"));
  }

  public function test_change_extension_path_containing_dots() {
    $this->assertEquals(
      "/website/foo.com/VID_20120513_105421.jpg",
      LegalFile::change_extension("/website/foo.com/VID_20120513_105421.mp4", "jpg"));
  }

  public function test_change_extension_path_containing_dots_and_no_extension() {
    $this->assertEquals(
      "/website/foo.com/VID_20120513_105421.jpg",
      LegalFile::change_extension("/website/foo.com/VID_20120513_105421", "jpg"));
  }

  public function test_change_extension_path_containing_dots_and_dot_extension() {
    $this->assertEquals(
      "/website/foo.com/VID_20120513_105421.jpg",
      LegalFile::change_extension("/website/foo.com/VID_20120513_105421.", "jpg"));
  }

  public function test_change_extension_path_containing_dots_and_non_standard_chars() {
    $this->assertEquals(
      "/j'écris@un#nom/bizarre(mais quand.même/ça_passe.jpg",
      LegalFile::change_extension("/j'écris@un#nom/bizarre(mais quand.même/ça_passe.\$ÇÀ@€#_", "jpg"));
  }

  public function test_smash_extensions() {
    $this->assertEquals("foo_bar.jpg", LegalFile::smash_extensions("foo.bar.jpg"));
    $this->assertEquals("foo_bar_baz.jpg", LegalFile::smash_extensions("foo.bar.baz.jpg"));
    $this->assertEquals("foo_bar_baz.jpg", LegalFile::smash_extensions("...foo...bar..baz...jpg"));
    $this->assertEquals("/path/to/foo_bar.jpg", LegalFile::smash_extensions("/path/to/foo.bar.jpg"));
    $this->assertEquals("/path/to.to/foo_bar.jpg", LegalFile::smash_extensions("/path/to.to/foo.bar.jpg"));
    $this->assertEquals("foo_bar-12345678.jpg", LegalFile::smash_extensions("foo.bar-12345678.jpg"));
  }

  public function test_smash_extensions_pass_thru_names_without_extensions() {
    $this->assertEquals("foo", LegalFile::smash_extensions("foo"));
    $this->assertEquals("foo.", LegalFile::smash_extensions("foo."));
    $this->assertEquals(".foo", LegalFile::smash_extensions(".foo"));
    $this->assertEquals(".", LegalFile::smash_extensions("."));
    $this->assertEquals("", LegalFile::smash_extensions(""));
    $this->assertEquals(null, LegalFile::smash_extensions(null));
  }

  public function test_sanitize_filename_with_no_rename() {
    $this->assertEquals("foo.jpeg", LegalFile::sanitize_filename("foo.jpeg", "jpg", "photo"));
    $this->assertEquals("foo.jpg", LegalFile::sanitize_filename("foo.jpg", "jpeg", "photo"));
    $this->assertEquals("foo.MP4", LegalFile::sanitize_filename("foo.MP4", "mp4", "movie"));
    $this->assertEquals("foo.mp4", LegalFile::sanitize_filename("foo.mp4", "MP4", "movie"));
  }

  public function test_sanitize_filename_with_corrected_extension() {
    $this->assertEquals("foo.jpg", LegalFile::sanitize_filename("foo.png", "jpg", "photo"));
    $this->assertEquals("foo.MP4", LegalFile::sanitize_filename("foo.jpg", "MP4", "movie"));
    $this->assertEquals("foo.jpg", LegalFile::sanitize_filename("foo.php", "jpg", "photo"));
  }

  public function test_sanitize_filename_with_non_standard_chars_and_dots() {
    $this->assertEquals("foo.jpg", LegalFile::sanitize_filename("foo", "jpg", "photo"));
    $this->assertEquals("foo.mp4", LegalFile::sanitize_filename("foo.", "mp4", "movie"));
    $this->assertEquals("foo.jpeg", LegalFile::sanitize_filename(".foo.jpeg", "jpg", "photo"));
    $this->assertEquals("foo_2013_02_10.jpeg",
      LegalFile::sanitize_filename("foo.2013/02/10.jpeg", "jpg", "photo"));
    $this->assertEquals("foo_bar_baz.jpg",
      LegalFile::sanitize_filename("...foo...bar..baz...png", "jpg", "photo"));
    $this->assertEquals("j'écris@un#nom_bizarre(mais quand_même_ça_passe.jpg",
      LegalFile::sanitize_filename("/j'écris@un#nom/bizarre(mais quand.même/ça_passe.\$ÇÀ@€#_", "jpg", "photo"));
  }

  public function test_sanitize_filename_with_no_base_name() {
    $this->assertEquals("photo.jpg", LegalFile::sanitize_filename(".png", "jpg", "photo"));
    $this->assertEquals("movie.mp4", LegalFile::sanitize_filename("__..__", "mp4", "movie"));
    $this->assertEquals("photo.jpg", LegalFile::sanitize_filename(".", "jpg", "photo"));
    $this->assertEquals("movie.mp4", LegalFile::sanitize_filename(null, "mp4", "movie"));
  }

  // Consider using @dataProvider here
  public function test_sanitize_filename_with_invalid_arguments() {
    foreach (array("flv" => "photo", "jpg" => "movie", "php" => "photo",
                   null => "movie", "jpg" => "album", "jpg" => null) as $extension => $type) {
      try {
        LegalFile::sanitize_filename("foo.jpg", $extension, $type);
        $this->assertTrue(false, "Shouldn't get here");
      } catch (Exception $e) {
        // pass
      }
    }
  }

  public function test_sanitize_dirname_with_no_rename() {
    $this->assertEquals("foo", LegalFile::sanitize_dirname("foo"));
    $this->assertEquals("foo.bar", LegalFile::sanitize_dirname("foo.bar"));
    $this->assertEquals(".foo.bar...baz", LegalFile::sanitize_dirname(".foo.bar...baz"));
    $this->assertEquals("foo bar  spaces", LegalFile::sanitize_dirname("foo bar  spaces"));
    $this->assertEquals("j'écris@un#nom_bizarre(mais quand_même_ça_passe \$ÇÀ@€",
      LegalFile::sanitize_dirname("j'écris@un#nom_bizarre(mais quand_même_ça_passe \$ÇÀ@€"));
  }

  public function test_sanitize_filename_with_corrections() {
    $this->assertEquals("foo_bar", LegalFile::sanitize_dirname("/foo/bar/"));
    $this->assertEquals("foo_bar", LegalFile::sanitize_dirname("\\foo\\bar\\"));
    $this->assertEquals(".foo..bar", LegalFile::sanitize_dirname(".foo..bar."));
    $this->assertEquals("foo_bar", LegalFile::sanitize_dirname("_foo__bar_"));
    $this->assertEquals("album", LegalFile::sanitize_dirname("_"));
    $this->assertEquals("album", LegalFile::sanitize_dirname(null));
  }
}