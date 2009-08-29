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
  private $_group;
  private $_album;
  private $_item;
  //private $_user;

  public function teardown() {
    try {
      $this->_group->delete();
    } catch (Exception $e) { }

    try {
      $this->_album->delete();
    } catch (Exception $e) { }

    //try {
    //  $this->_user->delete();
    //} catch (Exception $e) { }
  }

  public function setup() {
  }

  public function viewable_item_test() {
    $this->_group = group::create("access_test");
    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, rand(), "visible_test");
    $this->_user = user::create("visible_test", "Visible Test", "");
    $this->_user->add($this->_group);
    $this->_item = self::_create_random_item($this->_album);
    comment::create($this->_item, $this->_user, "This is a comment");
    access::deny(group::everybody(), "view", $this->_album);
    $active = user::active();

    $items = ORM::factory("item")
      ->where("id", $this->_album->id)
      ->find_all();
    print Database::instance()->last_query() . "\n";
    $items = ORM::factory("item")
      ->where("id", $this->_album->id)
      ->viewable()
      ->find_all();
    print Database::instance()->last_query() . "\n";
  }


  //public function viewable_one_restrictions_test() {
  //  $item = self::create_random_item();
  //  $this->assert_true(!empty($item->created));
  //  $this->assert_true(!empty($item->updated));
  //}
  //public function viewable_multiple_restrictions_test() {
  //  $item = self::create_random_item();
  //  $this->assert_true(!empty($item->created));
  //  $this->assert_true(!empty($item->updated));
  //}

  private static function _create_random_item($album) {
    $item = ORM::factory("item");
    /* Set all required fields (values are irrelevant) */
    $item->name = rand();
    $item->type = "photo";
    return $item->add_to_parent($album);
  }
}
