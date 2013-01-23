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
require_once(MODPATH . "gallery/tests/Gallery_Filters.php");

class No_Direct_ORM_Access_Test extends Gallery_Unit_Test_Case {
  public function no_access_to_users_table_test() {
    $dir = new UserModuleFilterIterator(
      new PhpCodeFilterIterator(
        new GalleryCodeFilterIterator(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(DOCROOT)))));
    $errors = array();
    foreach ($dir as $file) {
      //if (basename(dirname($file)) == "helpers") {
      $file_as_string = file_get_contents($file);
      if (preg_match("/ORM::factory\\(\"user\"/", $file_as_string)) {
        foreach (explode("\n", $file_as_string) as $l => $line) {
          if (preg_match('/ORM::factory\\(\"user\"/', $line)) {
            $errors[] = "$file($l) => $line";
          }
        }
      }
      $file_as_string = null;
    }
    if ($errors) {
      $this->assert_false(true, "Direct access to the users table found:\n" . join("\n", $errors));
    }
  }

  public function no_access_to_groups_table_test() {
    $dir = new UserModuleFilterIterator(
      new PhpCodeFilterIterator(
        new GalleryCodeFilterIterator(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(DOCROOT)))));
    $errors = array();
    foreach ($dir as $file) {
      $file_as_string = file_get_contents($file);
      if (preg_match("/ORM::factory\\(\"group\"/", $file_as_string)) {
        foreach (explode("\n", $file_as_string) as $l => $line) {
          if (preg_match('/ORM::factory\\(\"group\"/', $line)) {
            $errors[] = "$file($l) => $line";
          }
        }
      }
      $file_as_string = null;
    }
    if ($errors) {
      $this->assert_false(true, "Direct access to the groups table found:\n" . join("\n", $errors));
    }
  }

}

class UserModuleFilterIterator extends FilterIterator {
  public function accept() {
    $path_name = $this->getInnerIterator()->getPathName();
    return strpos($path_name, "/modules/user") === false;
  }
}
