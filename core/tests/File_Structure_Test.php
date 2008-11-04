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

  public function view_files_end_in_html_dot_php_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if ($file->getFilename() == 'kohana_unit_test.php') {
        // Exception: this file must be named accordingly for the test framework
        continue;
      }
      $this->assert_false(
        preg_match("|/views/.*?(?<!\.html)\.php$|", $file->getPathname()),
        "{$file->getPathname()} should end in .html.php");
    }
  }

  public function code_files_start_with_gallery_preamble_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));

    $expected = $this->_get_preamble(__FILE__);
    foreach ($dir as $file) {
      if (preg_match("/views/", $file->getPathname())) {
        // The preamble for views is a single line that prevents direct script access
        $lines = file($file->getPathname());
        $this->assert_equal(
          "<? defined(\"SYSPATH\") or die(\"No direct script access.\"); ?>\n",
          $lines[0],
          "in file: {$file->getPathname()}");
      } else if (preg_match("|\.php$|", $file->getPathname())) {
        $actual = $this->_get_preamble($file->getPathname());
        if ($file->getPathName() == DOCROOT . "index.php") {
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
    // Skip anything that we didn't write
    $path_name = $this->getInnerIterator()->getPathName();
    return !(strstr($path_name, ".svn") ||
             substr($path_name, -1, 1) == "~" ||
             strstr($path_name, SYSPATH) !== false ||
             strstr($path_name, MODPATH . 'forge') !== false ||
             strstr($path_name, MODPATH . 'unit_test') !== false ||
             strstr($path_name, MODPATH . 'mptt') !== false ||
             strstr($path_name, MODPATH . 'kodoc') !== false ||
             strstr($path_name, DOCROOT . 'var') !== false ||
             strstr($path_name, DOCROOT . 'test') !== false);
  }
}
