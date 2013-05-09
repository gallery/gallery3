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
 * This test case operates under the assumption that Hook_GalleryInstaller::install() is called by the
 * test controller before it starts.
 */
class Gallery_Installer_Test extends Unittest_TestCase {
  public function test_install_creates_dirs() {
    $this->assertTrue(file_exists(VARPATH . "albums"));
    $this->assertTrue(file_exists(VARPATH . "resizes"));
  }

  public function test_install_registers_gallery_module() {
    $gallery = ORM::factory("Module")->where("name", "=", "gallery")->find();
    $this->assertEquals("gallery", $gallery->name);
  }

  public function test_install_creates_root_item() {
    $max_right_ptr = ORM::factory("Item")
      ->select(array(DB::expr("MAX(`right_ptr`)"), "right_ptr"))
      ->find()->right_ptr;
    $root = ORM::factory('Item')->find(1);
    $this->assertEquals("Gallery", $root->title);
    $this->assertEquals(1, $root->left_ptr);
    $this->assertEquals($max_right_ptr, $root->right_ptr);
    $this->assertEquals(0, $root->parent_id);
    $this->assertEquals(1, $root->level);
  }
}
