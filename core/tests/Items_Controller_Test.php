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
class Items_Controller_Test extends Unit_Test_Case {
  public function change_item_test() {
    $controller = new Items_Controller();
    $root = ORM::factory("item", 1);
    $album = album::create($root, "test", "test");
    $_POST["title"] = "new title";
    $_POST["description"] = "new description";

    $controller->_update($album);
    $this->assert_equal("new title", $album->title);
    $this->assert_equal("new description", $album->description);
  }

  public function change_item_test_with_return() {
    $controller = new Items_Controller();
    $root = ORM::factory("item", 1);
    $album = album::create($root, "test", "test");
    $_POST["title"] = "item_title";
    $_POST["description"] = "item_description";
    $_POST["__return"] = "item_description";

    $this->assert_equal("item_description", $controller->_post($album));
    $this->assert_equal("item_title", $album->title);
    $this->assert_equal("item_description", $album->description);
  }
}
