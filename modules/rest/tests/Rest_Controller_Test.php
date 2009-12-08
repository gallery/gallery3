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
class Rest_Controller_Test extends Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER);
    $this->_user = identity::create_user("access_test", "Access Test", "password");
    $key = ORM::factory("user_access_token");
    $this->_access_key = $key->access_key = md5($this->_user->name . rand());
    $key->user_id = $this->_user->id;
    $key->save();

    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, "album", "Test Album", rand());
    $this->_child = album::create($this->_album, "child", "Test Child Album", rand());

    $filename = MODPATH . "gallery/tests/test.jpg";
    $rand = rand();
    $this->_photo = photo::create($this->_child, $filename, "$rand.jpg", $rand);
    $this->_path = $this->_photo->relative_path();
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;

    try {
      if (!empty($this->_user)) {
        $this->_user->delete();
      }
      if (!empty($this->_album)) {
        $this->_album->delete();
      }
    } catch (Exception $e) { }
  }

  public function rest_access_key_exists_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("user" => "access_test", "password" => "password"));

    $this->assert_equal(
      json_encode(array("status" => "OK", "token" => $this->_access_key)),
      $this->_call_controller());
  }

  public function rest_access_key_generated_test() {
    ORM::factory("user_access_token")
      ->where("access_key", $this->_access_key)
      ->delete();
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("user" => "access_test", "password" => "password"));

    $results = json_decode($this->_call_controller());

    $this->assert_equal("OK", $results->status);
    $this->assert_false(empty($results->token));
  }

  public function rest_access_key_no_parameters_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller());
  }

  public function rest_access_key_user_not_found_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("user" => "access_test2", "password" => "password"));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller());
  }

  public function rest_access_key_invalid_password_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("user" => "access_test", "password" => "invalid"));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller());
  }

  public function rest_get_resource_no_request_key_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";

    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller("rest"));
  }

  public function rest_get_resource_no_request_content_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";

    $_SERVER["REQUEST_METHOD"] = "POST";
    $_GET["request_key"] = $this->_access_key;

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Invalid request"))),
      $this->_call_controller("rest"));
  }

  public function rest_get_resource_invalid_key_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";

    $_SERVER["REQUEST_METHOD"] = "POST";
    $_GET["request_key"] = md5($this->_access_key); // screw up the access key
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller());
  }

  public function rest_get_resource_no_user_for_key_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";
    $_SERVER["REQUEST_METHOD"] = "POST";

    $_GET["request_key"] = $this->_access_key;
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->_user->delete();
    unset($this->_user);

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Authorization failed"))),
      $this->_call_controller("rest"));
  }

  public function rest_get_resource_no_resource_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";
    $_SERVER["REQUEST_METHOD"] = "POST";

    $_GET["request_key"] = $this->_access_key;
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Invalid request"))),
      $this->_call_controller("rest"));
  }

  public function rest_get_resource_no_handler_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";
    $_SERVER["REQUEST_METHOD"] = "POST";

    $_GET["request_key"] = $this->_access_key;
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => (string)t("Service not implemented"))),
      $this->_call_controller("rest", "album"));
  }

  public function rest_get_resource_test() {
    $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] = "GET";
    $_SERVER["REQUEST_METHOD"] = "POST";

    $_GET["request_key"] = $this->_access_key;
    $_POST["request"] = json_encode(array("path" => $this->_path));

    $this->assert_equal(
      json_encode(array("status" => "OK", "message" => (string)t("Processed"),
                        "item" => array("path" => $this->_photo->relative_path_cache,
                                        "title" => $this->_photo->title,
                                        "thumb_url" => $this->_photo->thumb_url(),
                                        "description" => $this->_photo->description,
                                        "internet_address" => $this->_photo->slug,
                                        "type" => $this->_photo->type))),
      $this->_call_controller("rest", "photo"));
  }

  private function _call_controller($method="access_key", $arg=null) {
    $controller = new Rest_Controller();

    ob_start();
    call_user_func(array($controller, $method), $arg);
    $results = ob_get_contents();
    ob_end_clean();

    return $results;
  }
}

class rest_rest {
  static $request = null;

  static function get_photo($request) {
    self::$request = $request;
    $item = ORM::factory("item")
      ->where("relative_path_cache", $request->path)
      ->find();
    $response["path"] = $item->relative_path_cache;
    $response["title"] = $item->title;
    $response["thumb_url"] = $item->thumb_url();
    $response["description"] = $item->description;
    $response["internet_address"] = $item->slug;
    $response["type"] = $item->type;
    return rest::success(array("item" => $response), t("Processed"));
  }

}
