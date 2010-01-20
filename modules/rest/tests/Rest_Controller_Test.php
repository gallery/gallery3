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
class Rest_Controller_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
  }

  public function login_test() {
    $user = test::random_user("password");

    // There's no access key at first
    $this->assert_false(
      ORM::factory("user_access_token")->where("user_id", "=", $user->id)->find()->loaded());

    $_POST["user"] = $user->name;
    $_POST["password"] = "password";

    $response = test::call_and_capture(array(new Rest_Controller(), "index"));
    $expected =
      ORM::factory("user_access_token")->where("user_id", "=", $user->id)->find()->access_key;

    // Now there is an access key, and it was returned
    $this->assert_equal(json_encode($expected), $response);
  }

  public function login_failed_test() {
    $user = test::random_user("password");
    $_POST["user"] = $user->name;
    $_POST["password"] = "WRONG PASSWORD";

    // @todo check the http response code
    $this->assert_equal(null, test::call_and_capture(array(new Rest_Controller(), "index")));
  }

  public function rest_get_resource_no_request_key_test_() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $photo = $this->_create_image();

    $this->assert_equal(
      json_encode(array("status" => "OK", "message" => (string)t("Processed"),
                        "photo" => array("path" => $photo->relative_url(),
                                        "title" => $photo->title,
                                        "thumb_url" => $photo->thumb_url(),
                                        "description" => $photo->description,
                                        "internet_address" => $photo->slug))),
      $this->_call_controller("rest", explode("/", $photo->relative_url())));
  }

  public function rest_get_resource_invalid_key_test_() {
    list ($access_key, $user) = $this->_create_user();
    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = md5($access_key); // screw up the access key;
    $_SERVER["REQUEST_METHOD"] = "GET";

    try {
      $this->_call_controller();
    } catch (Rest_Exception $e) {
      $this->assert_equal(403, $e->getCode());
      $this->assert_equal("Forbidden", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function rest_get_resource_no_user_for_key_test_() {
    list ($access_key, $user) = $this->_create_user();
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = $access_key;

    $user->delete();

    $photo = $this->_create_image();

    try {
      $this->_call_controller("rest", explode("/", $photo->relative_url()));
    } catch (Rest_Exception $e) {
      $this->assert_equal(403, $e->getCode());
      $this->assert_equal("Forbidden", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function rest_get_resource_no_handler_test_() {
    list ($access_key, $user) = $this->_create_user();
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = $access_key;
    $_SERVER["HTTP_X_GALLERY_REQUEST_METHOD"] = "PUT";
    $photo = $this->_create_image();

    try {
      $this->_call_controller("rest", explode("/", $photo->relative_url()));
    } catch (Rest_Exception $e) {
      $this->assert_equal(501, $e->getCode());
      $this->assert_equal("Not Implemented", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function rest_get_resource_test_() {
    list ($access_key, $user) = $this->_create_user();
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER["HTTP_X_GALLERY_REQUEST_KEY"] = $access_key;

    $photo = $this->_create_image();
    $this->assert_equal(
      json_encode(array("status" => "OK", "message" => (string)t("Processed"),
                        "photo" => array("path" => $photo->relative_url(),
                                        "title" => $photo->title,
                                        "thumb_url" => $photo->thumb_url(),
                                        "description" => $photo->description,
                                        "internet_address" => $photo->slug))),
      $this->_call_controller("rest", explode("/", $photo->relative_url())));
  }
}
