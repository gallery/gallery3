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
class Rest_Controller_Test extends Unittest_TestCase {
  public function setup() {
    parent::setup();
    $this->_save = array($_GET, $_POST, $_SERVER);

    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = Rest::access_key();
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_login() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $user = Test::random_user("password");

    // There's no access key at first
    $this->assertFalse(
      ORM::factory("UserAccessKey")->where("user_id", "=", $user->id)->find()->loaded());

    $_POST["user"] = $user->name;
    $_POST["password"] = "password";

    $response = Test::call_and_capture(array(new Controller_Rest(), "index"));
    $expected =
      ORM::factory("UserAccessKey")->where("user_id", "=", $user->id)->find()->access_key;

    // Now there is an access key, and it was returned
    $this->assertEquals(json_encode($expected), $response);
  }

  /**
   * @expectedException     Rest_Exception
   * @expectedExceptionCode 403
   */
  public function test_login_failed() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $user = Test::random_user("password");

    $_POST["user"] = $user->name;
    $_POST["password"] = "WRONG PASSWORD";
    Test::call_and_capture(array(new Controller_Rest(), "index"));
  }

  /**
   * @expectedException     Rest_Exception
   * @expectedExceptionCode 403
   */
  public function test_get() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    unset($_SERVER["HTTP_X_GALLERY_REQUEST_KEY"]);

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::GET;
    $_GET["key"] = "value";

    Test::call_and_capture(array(new Controller_Rest(), "mock"));
  }

  public function test_get_with_access_key() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::GET;
    $_GET["key"] = "value";

    $this->assertEquals(
      array("params" => array("key" => "value"),
            "method" => "get",
            "access_key" => Rest::access_key(),
            "url" => "http://./index.php/gallery_unittest"),
      json_decode(
        Test::call_and_capture(array(new Controller_Rest(), "mock")),
        true));
  }

  public function test_post() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::POST;
    $_POST["key"] = "value";

    $this->assertEquals(
      array("params" => array("key" => "value"),
            "method" => "post",
            "access_key" => Rest::access_key(),
            "url" => "http://./index.php/gallery_unittest"),
      json_decode(
        Test::call_and_capture(array(new Controller_Rest(), "mock")),
        true));
  }

  public function test_put() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::POST;
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "put";
    $_POST["key"] = "value";

    $this->assertEquals(
      array("params" => array("key" => "value"),
            "method" => "put",
            "access_key" => Rest::access_key(),
            "url" => "http://./index.php/gallery_unittest"),
      json_decode(
        Test::call_and_capture(array(new Controller_Rest(), "mock")),
        true));
  }

  public function test_delete() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::POST;
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "delete";
    $_POST["key"] = "value";

    $this->assertEquals(
      array("params" => array("key" => "value"),
            "method" => "delete",
            "access_key" => Rest::access_key(),
            "url" => "http://./index.php/gallery_unittest"),
      json_decode(
        Test::call_and_capture(array(new Controller_Rest(), "mock")),
        true));
  }

  /**
   * @expectedException     Rest_Exception
   * @expectedExceptionCode 400
   */
  public function test_bogus_method() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $_SERVER["REQUEST_METHOD"] = HTTP_Request::POST;
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "BOGUS";
    Test::call_and_capture(array(new Controller_Rest(), "mock"));
  }
}

class mock_rest {
  static function get($request)    { return (array)$request; }
  static function post($request)   { return (array)$request; }
  static function put($request)    { return (array)$request; }
  static function delete($request) { return (array)$request; }
}