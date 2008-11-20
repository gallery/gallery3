<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class REST_Controller_Test extends Unit_Test_Case {
  public function setup() {
    $this->mock_controller = new Mock_RESTful_Controller("mock");
    $this->mock_not_loaded_controller = new Mock_RESTful_Controller("mock_not_loaded");
  }

  public function dispatch_index_test() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_POST["_method"] = "";
    $this->mock_controller->__call("index", "");
    $this->assert_equal("index", $this->mock_controller->method_called);
  }

  public function dispatch_show_test() {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_POST["_method"] = "";
    $this->mock_controller->__call("3", "");
    $this->assert_equal("show", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_update_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "PUT";
    $this->mock_controller->__call("3", "");
    $this->assert_equal("update", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_delete_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "DELETE";
    $this->mock_controller->__call("3", "");
    $this->assert_equal("delete", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function dispatch_create_test() {
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST["_method"] = "";
    $this->mock_not_loaded_controller->__call("", "");
    $this->assert_equal("create", $this->mock_not_loaded_controller->method_called);
    $this->assert_equal(
      "Mock_Not_Loaded_Model", get_class($this->mock_not_loaded_controller->resource));
  }

  public function dispatch_form_test_add() {
    $this->mock_controller->form_add("args");
    $this->assert_equal("form_add", $this->mock_controller->method_called);
    $this->assert_equal("args", $this->mock_controller->resource);
  }

  public function dispatch_form_test_edit() {
    $this->mock_controller->form_edit("1");
    $this->assert_equal("form_edit", $this->mock_controller->method_called);
    $this->assert_equal("Mock_Model", get_class($this->mock_controller->resource));
  }

  public function routes_test() {
    $this->assert_equal("mock/form_add/args", router::routed_uri("form/add/mock/args"));
    $this->assert_equal("mock/form_edit/args", router::routed_uri("form/edit/mock/args"));
    $this->assert_equal(null, router::routed_uri("rest/args"));
  }
}

class Mock_RESTful_Controller extends REST_Controller {
  public $method_called;
  public $resource;

  public function __construct($type) {
    $this->resource_type = $type;
    parent::__construct();
  }

  public function _index() {
    $this->method_called = "index";
  }

  public function _create($resource) {
    $this->method_called = "create";
    $this->resource = $resource;
  }

  public function _show($resource) {
    $this->method_called = "show";
    $this->resource = $resource;
  }

  public function _update($resource) {
    $this->method_called = "update";
    $this->resource = $resource;
  }

  public function _delete($resource) {
    $this->method_called = "delete";
    $this->resource = $resource;
  }

  public function _form_add($args) {
    $this->method_called = "form_add";
    $this->resource = $args;
  }

  public function _form_edit($resource) {
    $this->method_called = "form_edit";
    $this->resource = $resource;
  }
}

class Mock_Model {
  public $loaded = true;
}

class Mock_Not_Loaded_Model {
  public $loaded = false;
}
