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
class Rest_Controller_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER);

    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = rest::access_key();
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
    identity::set_active_user(identity::admin_user());
  }

  public function login_test() {
    $user = test::random_user("password");

    // There's no access key at first
    $this->assert_false(
      ORM::factory("user_access_key")->where("user_id", "=", $user->id)->find()->loaded());

    $_POST["user"] = $user->name;
    $_POST["password"] = "password";

    $response = test::call_and_capture(array(new Rest_Controller(), "index"));
    $expected =
      ORM::factory("user_access_key")->where("user_id", "=", $user->id)->find()->access_key;

    // Now there is an access key, and it was returned
    $this->assert_equal(json_encode($expected), $response);
  }

  public function login_failed_test() {
    $user = test::random_user("password");

    try {
      $_POST["user"] = $user->name;
      $_POST["password"] = "WRONG PASSWORD";
      test::call_and_capture(array(new Rest_Controller(), "index"));
    } catch (Rest_Exception $e) {
      $this->assert_equal(403, $e->getCode());
      return;
    }

    $this->assert_true(false, "Shouldn't get here");
  }

  public function get_test() {
    unset($_SERVER["HTTP_X_GALLERY_REQUEST_KEY"]);

    $_SERVER["REQUEST_METHOD"] = "GET";
    $_GET["key"] = "value";

    try {
      test::call_and_capture(array(new Rest_Controller(), "mock"));
    } catch (Rest_Exception $e) {
      $this->assert_same(403, $e->getCode());
      return;
    }

    $this->assert_true(false, "Should be forbidden");
  }

  public function get_with_access_key_test() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_GET["key"] = "value";

    $this->assert_array_equal_to_json(
      array("params" => array("key" => "value"),
            "method" => "get",
            "access_key" => rest::access_key(),
            "url" => "http://./index.php/gallery_unit_test"),
      test::call_and_capture(array(new Rest_Controller(), "mock")));
  }

  public function post_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["key"] = "value";

    $this->assert_array_equal_to_json(
      array("params" => array("key" => "value"),
            "method" => "post",
            "access_key" => rest::access_key(),
            "url" => "http://./index.php/gallery_unit_test"),
      test::call_and_capture(array(new Rest_Controller(), "mock")));
  }

  public function put_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "put";
    $_POST["key"] = "value";

    $this->assert_array_equal_to_json(
      array("params" => array("key" => "value"),
            "method" => "put",
            "access_key" => rest::access_key(),
            "url" => "http://./index.php/gallery_unit_test"),
      test::call_and_capture(array(new Rest_Controller(), "mock")));
  }

  public function delete_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "delete";
    $_POST["key"] = "value";

    $this->assert_array_equal_to_json(
      array("params" => array("key" => "value"),
            "method" => "delete",
            "access_key" => rest::access_key(),
            "url" => "http://./index.php/gallery_unit_test"),
      test::call_and_capture(array(new Rest_Controller(), "mock")));
  }

  public function bogus_method_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "BOGUS";
    try {
      test::call_and_capture(array(new Rest_Controller(), "mock"));
    } catch (Exception $e) {
      $this->assert_equal(400, $e->getCode());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }
}

class mock_rest {
  static function get($request)    { return (array)$request; }
  static function post($request)   { return (array)$request; }
  static function put($request)    { return (array)$request; }
  static function delete($request) { return (array)$request; }
}