<?php
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
    foreach ($dir as $file) {
      if (preg_match('/(\?\>\s*)$/', file_get_contents($file), $matches)) {
	$this->assert_true(false, "$file ends in a trailing ?>");
      }
    }
  }

  public function view_files_end_in_html_dot_php_test() {
    $dir = new GalleryCodeFilterIterator(
      new RecursiveIteratorIterator(new RecursiveDirectoryIterator(DOCROOT)));
    foreach ($dir as $file) {
      if ($file->getFilename() == 'kohana_unit_test.php') {
	// Exception, this file must be named accordingly for the test framework
	continue;
      }
      if (preg_match("|/views\b|", $file->getPath())) {
	$this->assert_equal(".html.php", substr($file->getPathname(), -9), $file->getPathname());
      }
    }
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
	     strstr($path_name, DOCROOT . 'var') !== false ||
	     strstr($path_name, DOCROOT . 'test') !== false);
  }
}
