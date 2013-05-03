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
class Photos_Controller_Test extends Unittest_TestCase {
  public function setup() {
    parent::setup();
    $this->_save = array($_POST, $_SERVER);
    $_SERVER["HTTP_REFERER"] = "HTTP_REFERER";
  }

  public function teardown() {
    list($_POST, $_SERVER) = $this->_save;
    parent::teardown();
  }

  public function test_change_photo() {
    $controller = new Controller_Photos();
    $photo = Test::random_photo();

    $_POST["name"] = "new name.jpg";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["slug"] = "new-slug";
    $_POST["csrf"] = Access::csrf_token();
    Access::allow(Identity::everybody(), "edit", Item::root());

    ob_start();
    $controller->action_update($photo->id);
    $photo->reload();
    $results = ob_get_contents();
    ob_end_clean();

    $this->assertEquals(json_encode(array("result" => "success")), $results);
    $this->assertEquals("new-slug", $photo->slug);
    $this->assertEquals("new title", $photo->title);
    $this->assertEquals("new description", $photo->description);
    $this->assertEquals("new name.jpg", $photo->name);
  }

  public function test_change_photo_no_csrf_fails() {
    $controller = new Controller_Photos();
    $photo = Test::random_photo();

    $_POST["name"] = "new name.jpg";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["slug"] = "new slug";
    Access::allow(Identity::everybody(), "edit", Item::root());

    try {
      $controller->action_update($photo);
      $this->assertTrue(false, "This should fail");
    } catch (HTTP_Exception_403 $e) {
      // pass
    }
  }
}
