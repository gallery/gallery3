<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class File_Structure_Test extends Unit_Test_Case {
  public function no_trailing_closing_php_tag_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    $incorrect = array();
    foreach ($dir as $file) {
      if (!preg_match("|\.html\.php$|", $file->getPathname())) {
        $this->assert_false(
          preg_match('/\?\>\s*$/', file_get_contents($file)),
          "{$file->getPathname()} ends in ?>");
      }
    }
  }

  public function view_files_correct_suffix_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (strpos($file, "views")) {
        $this->assert_true(
          preg_match("#/views/.*?(\.html|mrss)\.php$#",
                     strtr($file->getPathname(), DIRECTORY_SEPARATOR, '/')),
          "{$file->getPathname()} should end in .html.php or mrss.php");
      }
    }
  }

  public function no_windows_line_endings_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if (preg_match("/\.(php|css|html|js)$/", $file)) {
        foreach (file($file) as $line) {
          $this->assert_true(substr($line, -2) != "\r\n", "$file has windows style line endings");
        }
      }
    }
  }

  public function code_files_start_with_gallery_preamble_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));

    $expected = $this->_get_preamble(__FILE__);
    foreach ($dir as $file) {
      if (preg_match("/views/", strtr($file->getPathname(), DIRECTORY_SEPARATOR, '/'))) {
        // The preamble for views is a single line that prevents direct script access
        $lines = file($file->getPathname());
        $this->assert_equal(
          "<?php defined(\"SYSPATH\") or die(\"No direct script access.\") ?>\n",
          $lines[0],
          "in file: {$file->getPathname()}");
      } else if (preg_match("|\.php$|", $file->getPathname())) {
        $actual = $this->_get_preamble($file->getPathname());
        if (strtr($file->getPathName(), DIRECTORY_SEPARATOR, '/') == DOCROOT . "index.php") {
          // index.php allows direct access, so modify our expectations for the first line
          $index_expected = $expected;
          $index_expected[0] = "<?php";
          $this->assert_equal($index_expected, $actual, "in file: {$file->getPathname()}");
        } else {
          // We expect the full preamble in regular PHP files
          $actual = $this->_get_preamble($file->getPathname());
          $this->assert_equal($expected, $actual, "in file: {$file->getPathname()}");
        }
      }
    }
  }

  public function no_tabs_in_our_code_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    $incorrect = array();
    foreach ($dir as $file) {
      if (substr($file->getFilename(), -4) == ".php") {
        $this->assert_false(
          preg_match('/\t/', file_get_contents($file)),
          "{$file->getPathname()} has tabs in it");
      }
    }
  }

  private function _get_preamble($file) {
    $lines = file($file);
    $copy = array();
    for ($i = 0; $i < count($lines); $i++) {
      $copy[] = rtrim($lines[$i]);
      if (!strncmp($lines[$i], ' */', 3)) {
        return $copy;
      }
    }
    return $copy;
  }
}

class GalleryCodeFilterIterator extends FilterIterator {
  public function accept() {
    // Skip anything that we didn"t write
    $path_name = $this->getInnerIterator()->getPathName();
    $path_name = strtr($path_name, DIRECTORY_SEPARATOR, "/");
    return !(
      strpos($path_name, ".svn") ||
      strpos($path_name, "core/views/kohana_profiler.php") !== false ||
      strpos($path_name, DOCROOT . "test") !== false ||
      strpos($path_name, DOCROOT . "var") !== false ||
      strpos($path_name, MODPATH . "forge") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_error_page.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_unit_test.php") !== false ||
      strpos($path_name, MODPATH . "kodoc") !== false ||
      strpos($path_name, MODPATH . "mptt") !== false ||
      strpos($path_name, MODPATH . "unit_test") !== false ||
      strpos($path_name, MODPATH . "user/libraries/PasswordHash") !== false ||
      strpos($path_name, SYSPATH) !== false ||
      substr($path_name, -1, 1) == "~");
  }
}
