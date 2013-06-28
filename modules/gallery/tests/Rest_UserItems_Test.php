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
class Rest_UserItems_Test extends Unittest_TestCase {
  public function test_get_response() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $rest = Rest::factory("UserItems", $user->id);

    $expected = array(
      "url" => URL::abs_site("rest/user_items/{$user->id}"),
      "members" => array(
        0 => URL::abs_site("rest/items/{$item1->id}"),
        1 => URL::abs_site("rest/items/{$item2->id}")),
      "members_info" => array(
        "count" => 2,
        "num" => 100,
        "start" => 0));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_members() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $rest1 = Rest::factory("Items", $item1->id);
    $rest2 = Rest::factory("Items", $item2->id);

    // Get with no query params
    $members = Rest::factory("UserItems", $user->id)->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));

    // Get with "type=album" query param - only item1
    $members = Rest::factory("UserItems", $user->id, array("type" => array("album")))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(false, array_search($rest2, $members));

    // Get with "name" query param - only item2 (use its name)
    $members = Rest::factory("UserItems", $user->id, array("name" => $item2->name))->get_members();
    $this->assertSame(false, array_search($rest1, $members));
    $this->assertSame(0, array_search($rest2, $members));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_with_invalid_user() {
    Identity::set_active_user(Identity::admin_user());
    $user = Test::random_user();

    $id = $user->id;
    $user->delete();

    Rest::factory("UserItems", $id)->get_members();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_without_view_access() {
    Module::set_var("gallery", "show_user_profiles_to", "registered_users");
    Identity::set_active_user(Identity::guest());

    Rest::factory("UserItems", Identity::admin_user()->id)->get_members();
  }

  public function test_put_members() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item1->id));

    Identity::set_active_user(Identity::admin_user());
    $rest = Rest::factory("UserItems", $user->id, $params);
    $rest->put_members();

    // PUT replaces member list - item1 still exists, but item2 has been deleted.
    $this->assertTrue($item1->reload()->loaded());
    $this->assertFalse($item2->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_members_admin_only() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item1->id));

    $rest = Rest::factory("UserItems", $user->id, $params);
    $rest->put_members();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_wrong_member_types() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Users", $item1->id));  // should be "Items"

    Identity::set_active_user(Identity::admin_user());
    $rest = Rest::factory("UserItems", $user->id, $params);
    $rest->put_members();
  }

  public function test_delete() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    Identity::set_active_user(Identity::admin_user());
    $rest = Rest::factory("UserItems", $user->id, $params);
    $rest->delete();

    // DELETE clears member list - item1 and item2 have been deleted.
    $this->assertFalse($item1->reload()->loaded());
    $this->assertFalse($item2->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_admin_only() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item1 = Test::random_album();
    $item2 = Test::random_photo($item1);

    $rest = Rest::factory("UserItems", $user->id);
    $rest->delete();
  }
}
