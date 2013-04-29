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
class Albums_Controller_Test extends Unittest_Testcase {
  public function setup() {
    parent::setup();
    $this->_save = array($_POST, $_SERVER);
  }

  public function teardown() {
    list($_POST, $_SERVER) = $this->_save;
    parent::teardown();
  }

  public function test_change_album() {
    $controller = new Controller_Albums();
    $album = Test::random_album();

    // Randomize to avoid conflicts.
    $new_name = "new_name_" . Test::random_string(6);

    $_POST["name"] = $new_name;
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["column"] = "weight";
    $_POST["direction"] = "ASC";
    $_POST["csrf"] = Access::csrf_token();
    $_POST["slug"] = "new-name";
    Access::allow(Identity::everybody(), "edit", Item::root());

    ob_start();
    $controller->action_update($album->id);
    $album->reload();
    $results = ob_get_contents();
    ob_end_clean();

    $this->assertEquals(json_encode(array("result" => "success")), $results);
    $this->assertEquals($new_name, $album->name);
    $this->assertEquals("new title", $album->title);
    $this->assertEquals("new description", $album->description);
  }

  public function test_change_album_no_csrf_fails() {
    $controller = new Controller_Albums();
    $album = Test::random_album();

    $_POST["name"] = "new name";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    Access::allow(Identity::everybody(), "edit", Item::root());

    try {
      $controller->action_update($album->id);
      $this->assertTrue(false, "This should fail");
    } catch (HTTP_Exception_403 $e) {
      // pass
    }
  }
}
