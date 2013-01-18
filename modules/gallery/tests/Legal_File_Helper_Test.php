<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("foo.bar.baz.jpg"));
    $this->assert_equal("foo_bar_baz.jpg", legal_file::smash_extensions("...foo...bar..baz...jpg"));
    $this->assert_equal("/path/to/foo_bar.jpg", legal_file::smash_extensions("/path/to/foo.bar.jpg"));
    $this->assert_equal("/path/to.to/foo_bar.jpg", legal_file::smash_extensions("/path/to.to/foo.bar.jpg"));
    $this->assert_equal("foo_bar-12345678.jpg", legal_file::smash_extensions("foo.bar-12345678.jpg"));
  }
}