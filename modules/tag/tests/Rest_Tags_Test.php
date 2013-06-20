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
class Rest_Tags_Test extends Unittest_TestCase {
  public function test_get_response() {
    $name = Test::random_name();
    $item = Test::random_album();
    $tag = Tag::add($item, $name);

    $rest = Rest::factory("Tags", $tag->id);

    $expected = array(
      "url" => URL::abs_site("rest/tags/{$tag->id}"),
      "entity" => array(
        "id"      => $tag->id,
        "count"   => $tag->count,
        "name"    => $tag->name,
        "slug"    => $tag->slug,
        "web_url" => $tag->abs_url()),
      "relationships" => array(
        "items" => array(
          "url" => URL::abs_site("rest/tag_items/{$tag->id}"),
          "members" => array(
            0 => URL::abs_site("rest/items/{$item->id}")))));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_response_with_no_items() {
    $tag = Test::random_tag();
    $rest = Rest::factory("Tags", $tag->id);

    $actual = $rest->get_response();
    $this->assertSame(array(), $actual["relationships"]["items"]["members"]);
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_with_invalid_tag() {
    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $id = $tag->id;
    $tag->delete();

    Rest::factory("Tags", $id)->get_entity();
  }

  public function test_put_entity() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = "new$name";

    $rest = Rest::factory("Tags", $tag->id, $params);
    $rest->put_entity();

    $this->assertEquals("new$name", $tag->reload()->name);
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_entity_admin_only() {
    Identity::set_active_user(Test::random_user());

    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = "new$name";

    $rest = Rest::factory("Tags", $tag->id, $params);
    $rest->put_entity();
  }

  public function test_delete() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $rest = Rest::factory("Tags", $tag->id);
    $rest->delete();

    $this->assertFalse($tag->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_admin_only() {
    Identity::set_active_user(Test::random_user());

    $name = Test::random_name();
    $tag = Tag::add(Item::root(), $name);

    $rest = Rest::factory("Tags", $tag->id);
    $rest->delete();
  }

  public function test_get_members() {
    $item1 = Test::random_album();
    $item2 = Test::random_album();

    // tag2 has two items, tag1 has one.
    $name = Test::random_name();
    $tag1 = Tag::add($item1, "{$name}1");
    $tag2 = Tag::add($item1, "{$name}2");
    Tag::add($item2, "{$name}2");

    $rest1 = Rest::factory("Tags", $tag1->id);
    $rest2 = Rest::factory("Tags", $tag2->id);

    // Get with no query params - sorted by count, so tag2 first.
    $members = Rest::factory("Tags")->get_members();
    $this->assertTrue(array_search($rest2, $members) < array_search($rest1, $members));

    // Get with "order=name" query param - sorted by name, so tag1 first.
    $members = Rest::factory("Tags", null, array("order" => "name"))->get_members();
    $this->assertTrue(array_search($rest1, $members) < array_search($rest2, $members));

    // Get with "name" query param - sorted by name, so tag1 first, and only two members.
    $members = Rest::factory("Tags", null, array("name" => $name))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));
  }

  public function test_post_entity() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = $name;

    $rest = Rest::factory("Tags", null, $params);
    $rest->created = true;  // this is typically done in Controller_Rest::action_post()
    $rest->post_entity();

    $tag = ORM::factory("Tag")
      ->where("name", "=", $name)
      ->find();

    $this->assertTrue($rest->created);
    $this->assertTrue($tag->loaded());
  }

  public function test_post_entity_that_already_exists() {
    Identity::set_active_user(Identity::admin_user());

    $name = Test::random_name();
    $item = Test::random_album();
    Tag::add($item, $name);  // this creates the tag

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = $name;

    $rest = Rest::factory("Tags", null, $params);
    $rest->created = true;  // this is typically done in Controller_Rest::action_post()
    $rest->post_entity();

    $tag = ORM::factory("Tag")
      ->where("name", "=", $name)
      ->find();

    $this->assertFalse($rest->created);
    $this->assertTrue($tag->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_post_entity_fails_without_permissions() {
    // We have to remove edit permissions from everywhere
    Database::instance()->query(Database::UPDATE, "UPDATE {access_caches} SET edit_1=0");
    Identity::set_active_user(Identity::guest());

    $name = Test::random_name();
    $item = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = $name;

    $rest = Rest::factory("Tags", null, $params);
    $rest->post_entity();
  }
}
