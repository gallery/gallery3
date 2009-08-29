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
class Item_Helper_Test extends Unit_Test_Case {

  public function viewable_test() {
    $root = ORM::factory("item", 1);
    $album = album::create($root, rand(), rand(), rand());
    $item = self::_create_random_item($album);
    user::set_active(user::guest());

    // We can see the item when permissions are granted
    access::allow(group::everybody(), "view", $album);
    $this->assert_equal(
      1,
      ORM::factory("item")->viewable()->where("id", $item->id)->count_all());

    // We can't see the item when permissions are denied
    access::deny(group::everybody(), "view", $album);
    $this->assert_equal(
      0,
      ORM::factory("item")->viewable()->where("id", $item->id)->count_all());
  }


  private static function _create_random_item($album) {
    // Set all required fields (values are irrelevant)
    $item = ORM::factory("item");
    $item->name = rand();
    $item->type = "photo";
    return $item->add_to_parent($album);
  }
}
