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
    $this->assert_equal(3, count(legal_file::get_movie_types_by_extension()));
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

  public function split_filename_test() {
    $this->assert_same(array("foo", "png"), legal_file::split_filename("foo.png"));
  }

  public function split_filename_with_four_letter_extension_test() {
    $this->assert_same(array("foo", ".mpeg"), legal_file::split_filename("foo.mpeg", true));
  }

  public function split_filename_with_no_extension_test() {
    $this->assert_same(array("foo", ""), legal_file::split_filename("foo"));
  }

  public function split_filename_with_dot_extension_test() {
    $this->assert_same(array("foo", "."), legal_file::split_filename("foo.", true));
  }

  public function split_filename_with_dot_extension_removed_test() {
    $this->assert_same(array("foo", ""), legal_file::split_filename("foo.", false));
  }

  public function split_filename_path_containing_dots_test() {
    $this->assert_same(
      array("/website/foo.com/VID_20120513_105421", "mp4"),
      legal_file::split_filename("/website/foo.com/VID_20120513_105421.mp4"));
  }

  public function split_filename_path_containing_dots_and_no_extension_test() {
    $this->assert_same(
      array("/website/foo.com/VID_20120513_105421", ""),
      legal_file::split_filename("/website/foo.com/VID_20120513_105421", true));
  }

  public function split_filename_path_containing_dots_and_dot_extension_test() {
    $this->assert_same(
      array("/website/foo.com/VID_20120513_105421", "."),
      legal_file::split_filename("/website/foo.com/VID_20120513_105421.", true));
  }

  public function split_filename_path_containing_dots_and_non_standard_chars_test() {
    $this->assert_same(
      array("/j'écris@un#nom/bizarre(mais quand.même/ça_passe", "\$ÇÀ@€#_"),
      legal_file::split_filename("/j'écris@un#nom/bizarre(mais quand.même/ça_passe.\$ÇÀ@€#_"));
  }

  public function smash_extensions_test() {
    $this->assert_equal("foo_bar.jpg", legal_file::smash_extensions("foo.bar.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("foo.bar.baz.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("foo.bar.baz.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("...foo...bar..baz...jpg"));
    $this->assert_equal("/path/to/foo_bar.jpg", legal_file::smash_extensions("/path/to/foo.bar.jpg"));
    $this->assert_equal("/path/to.to/foo_bar.jpg", legal_file::smash_extensions("/path/to.to/foo.bar.jpg"));
    $this->assert_equal("foo_bar-12345678.jpg", legal_file::smash_extensions("foo.bar-12345678.jpg"));
  }
}