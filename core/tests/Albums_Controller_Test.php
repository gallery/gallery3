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
class Albums_Controller_Test extends Unit_Test_Case {
  public function setup() {
    $this->_post = $_POST;
  }

  public function teardown() {
    $_POST = $this->_post;
  }

  public function change_album_test() {
    $controller = new Albums_Controller();
    $root = ORM::factory("item", 1);
    $album = album::create($root, "test", "test", "test");
    $orig_name = $album->name;

    $_POST["name"] = "new name";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["column"] = "weight";
    $_POST["direction"] = "ASC";
    $_POST["csrf"] = access::csrf_token();
    $_POST["_method"] = "put";
    access::allow(group::everybody(), "edit", $root);

    ob_start();
    $controller->_update($album);
    $results = ob_get_contents();
    ob_end_clean();

    $this->assert_equal(
      json_encode(array("result" => "success", "location" => "http://./index.php/test")),
      $results);
    $this->assert_equal("new title", $album->title);
    $this->assert_equal("new description", $album->description);

    // We don't change the name, yet.
    $this->assert_equal($orig_name, $album->name);
  }

  public function change_album_no_csrf_fails_test() {
    $controller = new Albums_Controller();
    $root = ORM::factory("item", 1);
    $album = album::create($root, "test", "test", "test");
    $_POST["name"] = "new name";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    access::allow(group::everybody(), "edit", $root);

    try {
      $controller->_update($album);
      $this->assert_true(false, "This should fail");
    } catch (Exception $e) {
      // pass
    }
  }
}
