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
  }

  private function _create_user() {
    if (empty($this->_user)) {
      $this->_user = identity::create_user("access_test" . rand(), "Access Test", "password");
      $this->_key = ORM::factory("user_access_token");
      $this->_key->access_key = md5($this->_user->name . rand());
      $this->_key->user_id = $this->_user->id;
      $this->_key->save();
      identity::set_active_user($this->_user);
    }
    return array($this->_key->access_key, $this->_user);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
    if (!empty($this->_user)) {
      try {
        $this->_user->delete();
      } catch (Exception $e) { }
    }
  }

  private function _create_image($parent=null) {
    $filename = MODPATH . "gallery/tests/test.jpg";
    $image_name = "image_" . rand();
    if (empty($parent)) {
      $parent = ORM::factory("item", 1);
    }
    return photo::create($parent, $filename, "$image_name.jpg", $image_name);
  }

  public function rest_access_key_exists_test() {
    list ($access_key, $user) = $this->_create_user();
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_GET["user"] = $user->name;;
    $_GET["password"] = "password";

    $this->assert_equal(
      json_encode(array("status" => "OK", "token" => $access_key)),
      $this->_call_controller());
  }

  public function rest_access_key_generated_test() {
    list ($access_key, $user) = $this->_create_user();
    ORM::factory("user_access_token")
      ->where("access_key", $access_key)
      ->delete();
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_GET["user"] = $user->name;
    $_GET["password"] = "password";

    $results = json_decode($this->_call_controller());

    $this->assert_equal("OK", $results->status);
    $this->assert_false(empty($results->token));
  }

  public function rest_access_key_no_parameters_test() {
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

  public function rest_access_key_user_not_found_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["request"] = json_encode(array("user" => "access_test2", "password" => "password"));

    try {
      $this->_call_controller();
    } catch (Rest_Exception $e) {
      $this->assert_equal(403, $e->getCode());
      $this->assert_equal("Forbidden", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function rest_access_key_invalid_password_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";

    try {
      $this->_call_controller();
    } catch (Rest_Exception $e) {
      $this->assert_equal(403, $e->getCode());
      $this->assert_equal("Forbidden", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function rest_get_resource_no_request_key_test() {
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

  public function rest_get_resource_invalid_key_test() {
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

  public function rest_get_resource_no_user_for_key_test() {
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

  public function rest_get_resource_no_handler_test() {
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

  public function rest_get_resource_test() {
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

  private function _call_controller($method="access_key", $arg=null) {
    $controller = new Rest_Controller();

    ob_start();
    call_user_func_array(array($controller, $method), $arg);
    $results = ob_get_contents();
    ob_end_clean();

    return $results;
  }
}

class rest_rest {
  static $request = null;

  static function get($request) {
    self::$request = $request;
    $item = ORM::factory("item")
      ->where("relative_url_cache", "=", implode("/", $request->arguments))
      ->find();
    $response["path"] = $item->relative_url();
    $response["title"] = $item->title;
    $response["thumb_url"] = $item->thumb_url();
    $response["description"] = $item->description;
    $response["internet_address"] = $item->slug;
    return rest::reply(array($item->type => $response));
  }

}
