<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class PhpCodeFilterIterator extends FilterIterator {
  public function accept() {
    $path_name = $this->getInnerIterator()->getPathName();
    return substr($path_name, -4) == ".php";
  }
}

class GalleryCodeFilterIterator extends FilterIterator {
  public function accept() {
    // Skip anything that we didn"t write
    $path_name = $this->getInnerIterator()->getPathName();
    return !(
      strpos($path_name, ".svn") ||
      strpos($path_name, DOCROOT . "test") !== false ||
      strpos($path_name, DOCROOT . "var") !== false ||
      strpos($path_name, MODPATH . "forge") !== false ||
      strpos($path_name, MODPATH . "gallery/views/kohana_error_page.php") !== false ||
      strpos($path_name, MODPATH . "gallery/views/kohana_profiler.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_error_page.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unit_test/views/kohana_unit_test_cli.php") !== false ||
      strpos($path_name, MODPATH . "unit_test") !== false ||
      strpos($path_name, MODPATH . "exif/lib") !== false ||
      strpos($path_name, MODPATH . "user/lib/PasswordHash") !== false ||
      strpos($path_name, DOCROOT . "lib/swfupload") !== false ||
      strpos($path_name, SYSPATH) !== false ||
      strpos($path_name, MODPATH . "gallery/libraries/HTMLPurifier") !== false ||
      substr($path_name, -1, 1) == "~");
  }
}
