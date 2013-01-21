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
class Photos_Controller_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->_save = array($_POST, $_SERVER);
    $_SERVER["HTTP_REFERER"] = "HTTP_REFERER";
  }

  public function teardown() {
    list($_POST, $_SERVER) = $this->_save;
  }

  public function change_photo_test() {
    $controller = new Photos_Controller();
    $photo = test::random_photo();

    $_POST["name"] = "new name.jpg";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["slug"] = "new-slug";
    $_POST["csrf"] = access::csrf_token();
    access::allow(identity::everybody(), "edit", item::root());

    ob_start();
    $controller->update($photo->id);
    $photo->reload();
    $results = ob_get_contents();
    ob_end_clean();

    $this->assert_equal(json_encode(array("result" => "success")), $results);
    $this->assert_equal("new-slug", $photo->slug);
    $this->assert_equal("new title", $photo->title);
    $this->assert_equal("new description", $photo->description);
    $this->assert_equal("new name.jpg", $photo->name);
  }

  public function change_photo_no_csrf_fails_test() {
    $controller = new Photos_Controller();
    $photo = test::random_photo();

    $_POST["name"] = "new name.jpg";
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";
    $_POST["slug"] = "new slug";
    access::allow(identity::everybody(), "edit", item::root());

    try {
      $controller->update($photo);
      $this->assert_true(false, "This should fail");
    } catch (Exception $e) {
      // pass
      $this->assert_same("@todo FORBIDDEN", $e->getMessage());
    }
  }
}
