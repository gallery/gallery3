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

/**
 * This test case operates under the assumption that gallery_installer::install() is called by the
 * test controller before it starts.
 */
class Gallery_Installer_Test extends Gallery_Unit_Test_Case {
  public function install_creates_dirs_test() {
    $this->assert_true(file_exists(VARPATH . "albums"));
    $this->assert_true(file_exists(VARPATH . "resizes"));
  }

  public function install_registers_gallery_module_test() {
    $gallery = ORM::factory("module")->where("name", "=", "gallery")->find();
    $this->assert_equal("gallery", $gallery->name);
  }

  public function install_creates_root_item_test() {
    $max_right_ptr = ORM::factory("item")
      ->select(db::expr("MAX(`right_ptr`) AS `right_ptr`"))
      ->find()->right_ptr;
    $root = ORM::factory('item')->find(1);
    $this->assert_equal("Gallery", $root->title);
    $this->assert_equal(1, $root->left_ptr);
    $this->assert_equal($max_right_ptr, $root->right_ptr);
    $this->assert_equal(0, $root->parent_id);
    $this->assert_equal(1, $root->level);
  }
}
