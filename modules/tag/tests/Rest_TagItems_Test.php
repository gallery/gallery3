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
class Rest_TagItems_Test extends Unittest_TestCase {
  // Note: this does not explicitly test Rest_TagItems::delete(), because all it does is call
  // Rest_Tags::delete() which is already tested in Rest_Tags_Test.

  public function test_get_response() {
    $name = Test::random_name();
    $item1 = Test::random_album();
    $item2 = Test::random_album();
    $tag = Tag::add($item1, $name);
    Tag::add($item2, $name);

    $rest = Rest::factory("TagItems", $tag->id);

    $expected = array(
      "url" => URL::abs_site("rest/tag_items/{$tag->id}"),
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
    $name = Test::random_name();
    $item1 = Test::random_album();
    $item2 = Test::random_photo();
    $tag = Tag::add($item1, $name);
    Tag::add($item2, $name);

    $rest1 = Rest::factory("Items", $item1->id);
    $rest2 = Rest::factory("Items", $item2->id);

    // Get with no query params
    $members = Rest::factory("TagItems", $tag->id)->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));

    // Get with "type=album" query param - only item1
    $members = Rest::factory("TagItems", $tag->id, array("type" => array("album")))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(false, array_search($rest2, $members));

    // Get with "name" query param - only item2 (use its name)
    $members = Rest::factory("TagItems", $tag->id, array("name" => $item2->name))->get_members();
    $this->assertSame(false, array_search($rest1, $members));
    $this->assertSame(0, array_search($rest2, $members));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_with_invalid_tag() {
    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $id = $tag->id;
    $tag->delete();

    Rest::factory("TagItems", $id)->get_members();
  }

  public function test_put_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), $name);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item->id));

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->put_members();

    // PUT replaces member list - tag has $item, but no longer has root.
    $this->assertFalse($tag->has("items", Item::root()));
    $this->assertTrue($tag->has("items", $item));
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_members_admin_only() {
    Identity::set_active_user(Test::random_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), $name);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item->id));

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->put_members();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_wrong_member_types() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), "1$name");
    $tag2 = Tag::add(Item::root(), "2$name");

    $params = array();
    $params["members"] = array(0 => Rest::factory("Tags", $tag2->id)); // should be "Items"

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->put_members();
  }

  public function test_post_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), $name);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item->id));

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->post_members();

    // POST adds to member list - tag has both $item and root.
    $this->assertTrue($tag->has("items", Item::root()));
    $this->assertTrue($tag->has("items", $item));
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_post_members_without_view_access() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), $name);

    Access::allow(Identity::registered_users(), "view", Item::root());
    Access::deny(Identity::registered_users(), "view", $item);
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "view", $item);
    Identity::set_active_user(Test::random_user());

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item->id));

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->post_members();
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_post_members_without_edit_access() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add(Item::root(), $name);

    Access::allow(Identity::registered_users(), "view", Item::root());
    Access::allow(Identity::registered_users(), "edit", Item::root());
    Access::allow(Identity::registered_users(), "view", $item);
    Access::deny(Identity::registered_users(), "edit", $item);
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "view", $item);
    Access::deny(Identity::everybody(), "edit", $item);
    Identity::set_active_user(Test::random_user());

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $item->id));

    $rest = Rest::factory("TagItems", $tag->id, $params);
    $rest->post_members();
  }
}
