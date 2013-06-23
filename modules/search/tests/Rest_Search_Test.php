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
class Rest_Search_Test extends Unittest_TestCase {
  public function test_get_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $parent = Test::random_album();
    $not_parent = Test::random_album();

    $item0 = Test::random_album_unsaved();
    $item0->name = "{$name}0";
    $item0->save();
    $item1 = Test::random_album_unsaved($parent);
    $item1->name = "{$name}1";
    $item1->save();
    $item2 = Test::random_photo_unsaved($parent);
    $item2->name = "{$name}2";
    $item2->save();

    $parent_url = Rest::factory("Items", $parent->id)->url();
    $not_parent_url = Rest::factory("Items", $not_parent->id)->url();
    $rest0 = Rest::factory("Items", $item0->id);
    $rest1 = Rest::factory("Items", $item1->id);
    $rest2 = Rest::factory("Items", $item2->id);

    // Get with "q=$name" only - all three items.
    $members = Rest::factory("Search", null,
      array("q" => $name))->get_members();
    $this->assertSame(0, array_search($rest0, $members));
    $this->assertSame(1, array_search($rest1, $members));
    $this->assertSame(2, array_search($rest2, $members));
    $this->assertSame(3, count($members));

    // Get with "q=$name" and "type=photo" query params - only item2
    $members = Rest::factory("Search", null,
      array("q" => $name, "type" => array("photo")))->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, count($members));

    // Get with "q=$name" and "album=[parent]" query params - only item1 and item2.
    $members = Rest::factory("Search", null,
      array("q" => $name, "album" => $parent_url))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));
    $this->assertSame(2, count($members));

    // Get with "q=$name" and "album=[not_parent]" query params - no items.
    $members = Rest::factory("Search", null,
      array("q" => $name, "album" => $not_parent_url))->get_members();
    $this->assertSame(0, count($members));
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_get_members_with_non_album() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();
    $url = Rest::factory("Items", $item->id)->url();

    Rest::factory("Search", null, array("q" => "foo", "album" => $url))->get_members();
  }
}
