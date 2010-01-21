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
class Item_Helper_Test extends Gallery_Unit_Test_Case {

  public function viewable_test() {
    $album = test::random_album();
    $item = test::random_photo($album);
    $album->reload();
    identity::set_active_user(identity::guest());

    // We can see the item when permissions are granted
    access::allow(identity::everybody(), "view", $album);
    $this->assert_equal(
      1,
      ORM::factory("item")->viewable()->where("id", "=", $item->id)->count_all());

    // We can't see the item when permissions are denied
    access::deny(identity::everybody(), "view", $album);
    $this->assert_equal(
      0,
      ORM::factory("item")->viewable()->where("id", "=", $item->id)->count_all());
  }

  public function convert_filename_to_slug_test() {
    $this->assert_equal("foo", item::convert_filename_to_slug("{[foo]}"));
    $this->assert_equal("foo-bar", item::convert_filename_to_slug("{[foo!@#!$@#^$@($!(@bar]}"));
  }
}
