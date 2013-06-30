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
class Rest_ItemTags_Test extends Unittest_TestCase {
  public function test_get_response() {
    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add($item, "{$name}2");

    $rest = Rest::factory("ItemTags", $item->id);

    $expected = array(
      "url" => URL::abs_site("rest/item_tags/{$item->id}"),
      "entity" => array(
        "names" => "{$tag1->name},{$tag2->name}"),
      "members" => array(
        0 => URL::abs_site("rest/tags/{$tag1->id}"),
        1 => URL::abs_site("rest/tags/{$tag2->id}")),
      "members_info" => array(
        "count" => 2,
        "num" => 100,
        "start" => 0));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_members() {
    $item = Test::random_album();

    // tag2 has two items, tag1 has one.
    $name = Test::random_name();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add($item, "{$name}2");
    Tag::add(Item::root(), "{$name}2");

    $rest1 = Rest::factory("Tags", $tag1->id);
    $rest2 = Rest::factory("Tags", $tag2->id);

    // Get with no query params - sorted by count, so tag2 first.
    $members = Rest::factory("ItemTags", $item->id)->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, array_search($rest1, $members));

    // Get with "order=name" query param - sorted by name, so tag1 first.
    $members = Rest::factory("ItemTags", $item->id, array("order" => "name"))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));

    // Get with "name" query param - use tag1's name, so tag1 only.
    $members = Rest::factory("ItemTags", $item->id, array("name" => "{$name}1"))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(false, array_search($rest2, $members));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_with_invalid_item() {
    $item = Test::random_album();
    $id = $item->id;
    $item->delete();

    Rest::factory("ItemTags", $id)->get_members();
  }

  public function test_put_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    $params = array();
    $params["members"] = array(0 => Rest::factory("Tags", $tag2->id));

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->put_members();

    // PUT replaces member list - item has tag2, but no longer tag1.
    $this->assertFalse($item->has("tags", $tag1));
    $this->assertTrue($item->has("tags", $tag2));
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_members_requires_edit_permission() {
    Identity::set_active_user(Test::random_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    // Give all registered users view but not edit permissions.
    Access::allow(Identity::everybody(), "view", $item);
    Access::deny(Identity::everybody(), "edit", $item);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Tags", $tag2->id));

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->put_members();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_wrong_member_types() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add($item, "{$name}1");

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", Item::root()->id)); // should be "Tags"

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->put_members();
  }

  public function test_put_entity() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->names = $tag2->name;

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->put_entity();

    // PUT replaces member list - item has tag2, but no longer tag1.
    $this->assertFalse($item->has("tags", $tag1));
    $this->assertTrue($item->has("tags", $tag2));
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_entity_with_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->names = $tag2->name;
    $params["members"] = array(0 => Rest::factory("Tags", $tag2->id));

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->put_entity();
  }

  public function test_post_members() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    $params = array();
    $params["members"] = array(0 => Rest::factory("Tags", $tag2->id));

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->post_members();

    // POST adds to member list - item has tag2 and tag1.
    $this->assertTrue($item->has("tags", $tag1));
    $this->assertTrue($item->has("tags", $tag2));
  }

  public function test_post_entity() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    $tag1 = Tag::add($item, "{$name}1");
    $tag2 = Tag::add(Item::root()->id, "{$name}2");

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->names = $tag2->name;

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->post_entity();

    // POST adds to member list - item has tag2 and tag1.
    $this->assertTrue($item->has("tags", $tag1));
    $this->assertTrue($item->has("tags", $tag2));
  }

  public function test_delete() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    Tag::add($item, "{$name}1");
    Tag::add($item, "{$name}2");

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->delete();

    $this->assertSame(0, $item->count_relations("tags"));
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_requires_edit_permissions() {
    Identity::set_active_user(Test::random_user());

    $name = Test::random_name();
    $item = Test::random_album();
    Tag::add($item, "{$name}1");
    Tag::add($item, "{$name}2");

    // Give all registered users view but not edit permissions.
    Access::allow(Identity::everybody(), "view", $item);
    Access::deny(Identity::everybody(), "edit", $item);

    $rest = Rest::factory("ItemTags", $item->id, $params);
    $rest->delete();
  }
}
