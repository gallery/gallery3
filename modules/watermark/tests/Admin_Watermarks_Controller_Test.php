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
class Admin_Watermarks_Controller_Test extends Unittest_Testcase {
  public function setup() {
    parent::setup();
    $this->_save = array($_POST, $_SERVER);
    $_SERVER["HTTP_REFERER"] = "HTTP_REFERER";
  }

  public function teardown() {
    list($_POST, $_SERVER) = $this->_save;
    parent::teardown();
  }

  public function test_add_watermark() {
    // Source is a jpg file, watermark path has extension jpg
    $name = Test::random_name();
    $source_path = MODPATH . "gallery/assets/graphics/imagemagick.jpg";
    $watermark_path = TMPPATH . "uploadfile-123-{$name}.jpg";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $controller = new Controller_Admin_Watermarks();
    $_POST["file"] = $watermark_path;
    $_POST["csrf"] = Access::csrf_token();
    ob_start();
    $controller->action_add();
    $results = ob_get_clean();

    // Add should be successful
    $this->assertEquals(json_encode(array("result" => "success",
                                          "location" => URL::site("admin/watermarks"))), $results);
    $this->assertEquals(file_get_contents($source_path),
                        file_get_contents(VARPATH . "modules/watermark/$name.jpg"));
    $this->assertEquals("$name.jpg", Module::get_var("watermark", "name"));
    $this->assertEquals(114, Module::get_var("watermark", "width"));
    $this->assertEquals(118, Module::get_var("watermark", "height"));
    $this->assertEquals("image/jpeg", Module::get_var("watermark", "mime_type"));
  }

  public function test_add_watermark_reject_illegal_file() {
    // Source is a php file, watermark path has extension php
    $name = Test::random_name();
    $source_path = MODPATH . "watermark/tests/Admin_Watermarks_Controller_Test.php";
    $watermark_path = TMPPATH . "uploadfile-123-{$name}.php";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $controller = new Controller_Admin_Watermarks();
    $_POST["file"] = $watermark_path;
    $_POST["csrf"] = Access::csrf_token();
    ob_start();
    $controller->action_add();
    $results = ob_get_clean();

    // Delete all files marked using System::delete_later (from Hook_GalleryEvent::gallery_shutdown)
    System::delete_marked_files();

    // Add should *not* be successful, and watermark should be deleted
    $this->assertEquals("", $results);
    $this->assertFalse(file_exists($watermark_path));
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.php"));
  }

  public function test_add_watermark_rename_legal_file_with_illegal_extension() {
    // Source is a jpg file, watermark path has extension php
    $name = Test::random_name();
    $source_path = MODPATH . "gallery/assets/graphics/imagemagick.jpg";
    $watermark_path = TMPPATH . "uploadfile-123-{$name}.php";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $controller = new Controller_Admin_Watermarks();
    $_POST["file"] = $watermark_path;
    $_POST["csrf"] = Access::csrf_token();
    ob_start();
    $controller->action_add();
    $results = ob_get_clean();

    // Add should be successful with file renamed as jpg
    $this->assertEquals(json_encode(array("result" => "success",
                                          "location" => URL::site("admin/watermarks"))), $results);
    $this->assertEquals(file_get_contents($source_path),
                        file_get_contents(VARPATH . "modules/watermark/$name.jpg"));
    $this->assertEquals("$name.jpg", Module::get_var("watermark", "name"));
    $this->assertEquals(114, Module::get_var("watermark", "width"));
    $this->assertEquals(118, Module::get_var("watermark", "height"));
    $this->assertEquals("image/jpeg", Module::get_var("watermark", "mime_type"));
  }

  public function test_add_watermark_reject_illegal_file_with_legal_extension() {
    // Source is a php file, watermark path has extension jpg
    $name = Test::random_name();
    $source_path = MODPATH . "watermark/tests/Admin_Watermarks_Controller_Test.php";
    $watermark_path = TMPPATH . "uploadfile-123-{$name}.jpg";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $controller = new Controller_Admin_Watermarks();
    $_POST["file"] = $watermark_path;
    $_POST["csrf"] = Access::csrf_token();
    ob_start();
    $controller->action_add();
    $results = ob_get_clean();

    // Delete all files marked using System::delete_later (from Hook_GalleryEvent::gallery_shutdown)
    System::delete_marked_files();

    // Add should *not* be successful, and watermark should be deleted
    $this->assertEquals("", $results);
    $this->assertFalse(file_exists($watermark_path));
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.php"));
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.jpg"));
  }
}
