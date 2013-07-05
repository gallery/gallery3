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
class GalleryCodeFilterIterator extends FilterIterator {
  public function accept() {
    // Skip anything that we didn't write
    $path_name = $this->getInnerIterator()->getPathName();
    $file_name = $this->getInnerIterator()->getFileName();
    return !(
      $file_name == "." ||
      $file_name == ".." ||
      strpos($path_name, DOCROOT . ".git") !== false ||
      strpos($path_name, DOCROOT . "lib") !== false ||
      strpos($path_name, DOCROOT . "test") !== false ||
      strpos($path_name, DOCROOT . "themes/admin_wind/vendor/themeroller") !== false ||
      strpos($path_name, DOCROOT . "themes/wind/vendor/themeroller") !== false ||
      strpos($path_name, DOCROOT . "var") !== false ||
      strpos($path_name, MODPATH . "cache") !== false ||
      strpos($path_name, MODPATH . "database") !== false ||
      strpos($path_name, MODPATH . "exif/vendor") !== false ||
      strpos($path_name, MODPATH . "formo") !== false ||
      strpos($path_name, MODPATH . "gallery/vendor") !== false ||
      strpos($path_name, MODPATH . "gallery/views/gallery/kohana_profiler.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unittest/vendor") !== false ||
      strpos($path_name, MODPATH . "gallery_unittest/views/kohana_error_page.php") !== false ||
      strpos($path_name, MODPATH . "gallery_unittest/views/kohana_unittest_cli.php") !== false ||
      strpos($path_name, MODPATH . "image") !== false ||
      strpos($path_name, MODPATH . "organize/vendor") !== false ||
      strpos($path_name, MODPATH . "orm") !== false ||
      strpos($path_name, MODPATH . "purifier/vendor") !== false ||
      strpos($path_name, MODPATH . "unittest") !== false ||
      strpos($path_name, MODPATH . "user/vendor") !== false ||
      strpos($path_name, SYSPATH) !== false ||
      substr($path_name, -1, 1) == "~");
  }
}
