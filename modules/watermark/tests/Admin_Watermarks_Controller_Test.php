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
class Admin_Watermarks_Controller_Test extends Unittest_TestCase {
  public function setup() {
    parent::setup();

    // Set current user as admin (required since we're testing an admin controller)
    Identity::set_active_user(Identity::admin_user());
    Session::instance()->set("active_auth_timestamp", time());
  }

  public function teardown() {
    // Remove any watermark that was present
    $response = Request::factory("admin/watermarks/delete")
      ->method(Request::POST)
      ->post(array(
          "csrf"      => Access::csrf_token()
        ))
      ->execute();

    // Set current user as guest
    Identity::set_active_user(Identity::guest());

    parent::teardown();
  }

  public function test_add_watermark() {
    // Source is a jpg file, watermark path has extension jpg
    $name = Test::random_name();
    $source_path = MODPATH . "gallery/assets/graphics/imagemagick.jpg";
    $watermark_path = TMPPATH . "$name.jpg";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $response = Request::factory("admin/watermarks/add")
      ->method(Request::POST)
      ->post(array(
          "data_file" => $watermark_path,
          "csrf"      => Access::csrf_token()
        ))
      ->make_ajax()
      ->execute();

    // Add should be successful
    $json = json_decode($response->body(), true);
    $this->assertEquals("success", $json["result"]);

    // Watermark should be same file as source
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
    $watermark_path = TMPPATH . "$name.php";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $response = Request::factory("admin/watermarks/add")
      ->method(Request::POST)
      ->post(array(
          "data_file" => $watermark_path,
          "csrf"      => Access::csrf_token()
        ))
      ->make_ajax()
      ->execute();

    // Add should *not* be successful
    $json = json_decode($response->body(), true);
    $this->assertEquals("error", $json["result"]);

    // Watermark should not exist
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.php"));
  }

  public function test_add_watermark_reject_legal_file_with_illegal_extension() {
    // Source is a jpg file, watermark path has extension php
    $name = Test::random_name();
    $source_path = MODPATH . "gallery/assets/graphics/imagemagick.jpg";
    $watermark_path = TMPPATH . "$name.php";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $response = Request::factory("admin/watermarks/add")
      ->method(Request::POST)
      ->post(array(
          "data_file" => $watermark_path,
          "csrf"      => Access::csrf_token()
        ))
      ->make_ajax()
      ->execute();

    // Add should *not* be successful
    $json = json_decode($response->body(), true);
    $this->assertEquals("error", $json["result"]);

    // Watermark should not exist
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.php"));
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.jpg"));
  }

  public function test_add_watermark_reject_illegal_file_with_legal_extension() {
    // Source is a php file, watermark path has extension jpg
    $name = Test::random_name();
    $source_path = MODPATH . "watermark/tests/Admin_Watermarks_Controller_Test.php";
    $watermark_path = TMPPATH . "$name.php";
    copy($source_path, $watermark_path);

    // Setup and run Controller_Admin_Watermarks::action_add
    $response = Request::factory("admin/watermarks/add")
      ->method(Request::POST)
      ->post(array(
          "data_file" => $watermark_path,
          "csrf"      => Access::csrf_token()
        ))
      ->make_ajax()
      ->execute();

    // Add should *not* be successful
    $json = json_decode($response->body(), true);
    $this->assertEquals("error", $json["result"]);

    // Watermark should not exist
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.php"));
    $this->assertFalse(file_exists(VARPATH . "modules/watermark/$name.jpg"));
  }
}
