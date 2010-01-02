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
    $this->_save = array($_POST, $_SERVER);
  }

  public function teardown() {
    list($_POST, $_SERVER) = $this->_save;
    if (isset($this->_album)) {
      $this->_album->delete();
    }
  }

  public function change_album_test() {
    $controller = new Albums_Controller();
    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, "test", "test", "test");
    $orig_name = $this->_album->name;

    // Randomize to avoid conflicts.
    $new_dirname = "new_name_" . rand();

    $_POST["dirname"] = $new_dirname;
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["column"] = "weight";
    $_POST["direction"] = "ASC";
    $_POST["csrf"] = access::csrf_token();
    $_POST["slug"] = "new-name";
    access::allow(identity::everybody(), "edit", $root);

    ob_start();
    $controller->update($this->_album->id);
    $this->_album->reload();
    $results = ob_get_contents();
    ob_end_clean();

    $this->assert_equal(
      json_encode(array("result" => "success")),
      $results);
    $this->assert_equal($new_dirname, $this->_album->name);
    $this->assert_equal("new title", $this->_album->title);
    $this->assert_equal("new description", $this->_album->description);
  }

  public function change_album_no_csrf_fails_test() {
    $controller = new Albums_Controller();
    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, "test", "test", "test");
    $_POST["name"] = "new name";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    access::allow(identity::everybody(), "edit", $root);

    try {
      $controller->_update($this->_album);
      $this->assert_true(false, "This should fail");
    } catch (Exception $e) {
      // pass
    }
  }
}
