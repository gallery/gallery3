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
class System_Helper_Test extends Gallery_Unit_Test_Case {
  public function temp_filename_test() {
    $filename = System::temp_filename("file", "ext");
    $this->assert_true(file_exists($filename), "File not created");
    unlink($filename);
    $this->assert_pattern($filename, "|/file.*\\.ext$|");
  }

  public function remove_album_test() {
    $dirname = (VARPATH . "albums/testdir");
    mkdir($dirname, 0777, true);

    $filename = tempnam($dirname, "file");
    touch($filename);

    System::unlink_dir($dirname);
    $this->assert_boolean(!file_exists($filename), "File not deleted");
    $this->assert_boolean(!file_exists($dirname), "Directory not deleted");
  }
}
