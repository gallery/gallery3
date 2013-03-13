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
class Legal_File_Helper_Test extends Gallery_Unit_Test_Case {
  public function get_photo_types_by_extension_test() {
    $this->assert_equal("image/jpeg", legal_file::get_photo_types_by_extension("jpg")); // regular
    $this->assert_equal("image/jpeg", legal_file::get_photo_types_by_extension("JPG")); // all caps
    $this->assert_equal("image/png", legal_file::get_photo_types_by_extension("Png"));  // some caps
    $this->assert_equal(null, legal_file::get_photo_types_by_extension("php"));      // invalid
    $this->assert_equal(null, legal_file::get_photo_types_by_extension("php.jpg"));  // invalid w/ .

    // No extension returns full array
    $this->assert_equal(4, count(legal_file::get_photo_types_by_extension()));
  }

  public function get_movie_types_by_extension_test() {
    $this->assert_equal("video/x-flv", legal_file::get_movie_types_by_extension("flv")); // regular
    $this->assert_equal("video/x-flv", legal_file::get_movie_types_by_extension("FLV")); // all caps
    $this->assert_equal("video/mp4", legal_file::get_movie_types_by_extension("Mp4"));  // some caps
    $this->assert_equal(null, legal_file::get_movie_types_by_extension("php"));     // invalid
    $this->assert_equal(null, legal_file::get_movie_types_by_extension("php.flv")); // invalid w/ .

    // No extension returns full array
    $this->assert_equal(5, count(legal_file::get_movie_types_by_extension()));
  }

  public function get_types_by_extension_test() {
    $this->assert_equal("image/jpeg", legal_file::get_types_by_extension("jpg"));  // photo
    $this->assert_equal("video/x-flv", legal_file::get_types_by_extension("FLV")); // movie
    $this->assert_equal(null, legal_file::get_types_by_extension("php"));          // invalid
    $this->assert_equal(null, legal_file::get_types_by_extension("php.flv"));      // invalid w/ .

    // No extension returns full array
    $this->assert_equal(9, count(legal_file::get_types_by_extension()));
  }

  public function get_photo_extensions_test() {
    $this->assert_equal(true, legal_file::get_photo_extensions("jpg"));      // regular
    $this->assert_equal(true, legal_file::get_photo_extensions("JPG"));      // all caps
    $this->assert_equal(true, legal_file::get_photo_extensions("Png"));      // some caps
    $this->assert_equal(false, legal_file::get_photo_extensions("php"));     // invalid
    $this->assert_equal(false, legal_file::get_photo_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assert_equal(4, count(legal_file::get_photo_extensions()));
  }

  public function get_movie_extensions_test() {
    $this->assert_equal(true, legal_file::get_movie_extensions("flv"));      // regular
    $this->assert_equal(true, legal_file::get_movie_extensions("FLV"));      // all caps
    $this->assert_equal(true, legal_file::get_movie_extensions("Mp4"));      // some caps
    $this->assert_equal(false, legal_file::get_movie_extensions("php"));     // invalid
    $this->assert_equal(false, legal_file::get_movie_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assert_equal(5, count(legal_file::get_movie_extensions()));
  }

  public function get_extensions_test() {
    $this->assert_equal(true, legal_file::get_extensions("jpg"));      // photo
    $this->assert_equal(true, legal_file::get_extensions("FLV"));      // movie
    $this->assert_equal(false, legal_file::get_extensions("php"));     // invalid
    $this->assert_equal(false, legal_file::get_extensions("php.jpg")); // invalid w/ .

    // No extension returns full array
    $this->assert_equal(9, count(legal_file::get_extensions()));
  }

  public function get_filters_test() {
    // All 9 extensions both uppercase and lowercase
    $this->assert_equal(18, count(legal_file::get_filters()));
  }

  public function get_photo_types_test() {
    // Note that this is one *less* than photo extensions since jpeg and jpg have the same mime.
    $this->assert_equal(3, count(legal_file::get_photo_types()));
  }

  public function get_movie_types_test() {
    // Note that this is one *more* than movie extensions since video/flv is added.
    $this->assert_equal(6, count(legal_file::get_movie_types()));
  }

  public function change_extension_test() {
    $this->assert_equal("foo.jpg", legal_file::change_extension("foo.png", "jpg"));
  }

  public function change_four_letter_extension_test() {
    $this->assert_equal("foo.flv", legal_file::change_extension("foo.mpeg", "flv"));
  }

  public function change_extension_with_no_extension_test() {
    $this->assert_equal("foo.flv", legal_file::change_extension("foo", "flv"));
  }

  public function change_extension_path_containing_dots_test() {
    $this->assert_equal(
      "/website/foo.com/VID_20120513_105421.jpg",
      legal_file::change_extension("/website/foo.com/VID_20120513_105421.mp4", "jpg"));
  }

  public function change_extension_path_containing_dots_and_no_extension_test() {
    $this->assert_equal(
      "/website/foo.com/VID_20120513_105421.jpg",
      legal_file::change_extension("/website/foo.com/VID_20120513_105421", "jpg"));
  }

  public function change_extension_path_containing_dots_and_dot_extension_test() {
    $this->assert_equal(
      "/website/foo.com/VID_20120513_105421.jpg",
      legal_file::change_extension("/website/foo.com/VID_20120513_105421.", "jpg"));
  }

  public function change_extension_path_containing_dots_and_non_standard_chars_test() {
    $this->assert_equal(
      "/j'écris@un#nom/bizarre(mais quand.même/ça_passe.jpg",
      legal_file::change_extension("/j'écris@un#nom/bizarre(mais quand.même/ça_passe.\$ÇÀ@€#_", "jpg"));
  }

  public function smash_extensions_test() {
    $this->assert_equal("foo_bar.jpg", legal_file::smash_extensions("foo.bar.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("foo.bar.baz.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("...foo...bar..baz...jpg"));
    $this->assert_equal("/path/to/foo_bar.jpg", legal_file::smash_extensions("/path/to/foo.bar.jpg"));
    $this->assert_equal("/path/to.to/foo_bar.jpg", legal_file::smash_extensions("/path/to.to/foo.bar.jpg"));
    $this->assert_equal("foo_bar-12345678.jpg", legal_file::smash_extensions("foo.bar-12345678.jpg"));
  }

  public function smash_extensions_pass_thru_names_without_extensions_test() {
    $this->assert_equal("foo", legal_file::smash_extensions("foo"));
    $this->assert_equal("foo.", legal_file::smash_extensions("foo."));
    $this->assert_equal(".foo", legal_file::smash_extensions(".foo"));
    $this->assert_equal(".", legal_file::smash_extensions("."));
    $this->assert_equal("", legal_file::smash_extensions(""));
    $this->assert_equal(null, legal_file::smash_extensions(null));
  }

  public function sanitize_filename_with_no_rename_test() {
    $this->assert_equal("foo.jpeg", legal_file::sanitize_filename("foo.jpeg", "jpg", "photo"));
    $this->assert_equal("foo.jpg", legal_file::sanitize_filename("foo.jpg", "jpeg", "photo"));
    $this->assert_equal("foo.MP4", legal_file::sanitize_filename("foo.MP4", "mp4", "movie"));
    $this->assert_equal("foo.mp4", legal_file::sanitize_filename("foo.mp4", "MP4", "movie"));
  }

  public function sanitize_filename_with_corrected_extension_test() {
    $this->assert_equal("foo.jpg", legal_file::sanitize_filename("foo.png", "jpg", "photo"));
    $this->assert_equal("foo.MP4", legal_file::sanitize_filename("foo.jpg", "MP4", "movie"));
    $this->assert_equal("foo.jpg", legal_file::sanitize_filename("foo.php", "jpg", "photo"));
  }

  public function sanitize_filename_with_non_standard_chars_and_dots_test() {
    $this->assert_equal("foo.jpg", legal_file::sanitize_filename("foo", "jpg", "photo"));
    $this->assert_equal("foo.mp4", legal_file::sanitize_filename("foo.", "mp4", "movie"));
    $this->assert_equal("foo.jpeg", legal_file::sanitize_filename(".foo.jpeg", "jpg", "photo"));
    $this->assert_equal("foo_2013_02_10.jpeg",
      legal_file::sanitize_filename("foo.2013/02/10.jpeg", "jpg", "photo"));
    $this->assert_equal("foo_bar_baz.jpg",
      legal_file::sanitize_filename("...foo...bar..baz...png", "jpg", "photo"));
    $this->assert_equal("j'écris@un#nom_bizarre(mais quand_même_ça_passe.jpg",
      legal_file::sanitize_filename("/j'écris@un#nom/bizarre(mais quand.même/ça_passe.\$ÇÀ@€#_", "jpg", "photo"));
  }

  public function sanitize_filename_with_no_base_name_test() {
    $this->assert_equal("photo.jpg", legal_file::sanitize_filename(".png", "jpg", "photo"));
    $this->assert_equal("movie.mp4", legal_file::sanitize_filename("__..__", "mp4", "movie"));
    $this->assert_equal("photo.jpg", legal_file::sanitize_filename(".", "jpg", "photo"));
    $this->assert_equal("movie.mp4", legal_file::sanitize_filename(null, "mp4", "movie"));
  }

  public function sanitize_filename_with_invalid_arguments_test() {
    foreach (array("flv" => "photo", "jpg" => "movie", "php" => "photo",
                   null => "movie", "jpg" => "album", "jpg" => null) as $extension => $type) {
      try {
        legal_file::sanitize_filename("foo.jpg", $extension, $type);
        $this->assert_true(false, "Shouldn't get here");
      } catch (Exception $e) {
        // pass
      }
    }
  }

  public function sanitize_dirname_with_no_rename_test() {
    $this->assert_equal("foo", legal_file::sanitize_dirname("foo"));
    $this->assert_equal("foo.bar", legal_file::sanitize_dirname("foo.bar"));
    $this->assert_equal(".foo.bar...baz", legal_file::sanitize_dirname(".foo.bar...baz"));
    $this->assert_equal("foo bar  spaces", legal_file::sanitize_dirname("foo bar  spaces"));
    $this->assert_equal("j'écris@un#nom_bizarre(mais quand_même_ça_passe \$ÇÀ@€",
      legal_file::sanitize_dirname("j'écris@un#nom_bizarre(mais quand_même_ça_passe \$ÇÀ@€"));
  }

  public function sanitize_filename_with_corrections_test() {
    $this->assert_equal("foo_bar", legal_file::sanitize_dirname("/foo/bar/"));
    $this->assert_equal("foo_bar", legal_file::sanitize_dirname("\\foo\\bar\\"));
    $this->assert_equal(".foo..bar", legal_file::sanitize_dirname(".foo..bar."));
    $this->assert_equal("foo_bar", legal_file::sanitize_dirname("_foo__bar_"));
    $this->assert_equal("album", legal_file::sanitize_dirname("_"));
    $this->assert_equal("album", legal_file::sanitize_dirname(null));
  }
}