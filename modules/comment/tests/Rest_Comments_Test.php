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
class Rest_Comments_Test extends Unittest_TestCase {
  public function test_get_response() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $rest = Rest::factory("Comments", $comment->id);

    $expected = array(
      "url" => URL::abs_site("rest/comments/{$comment->id}"),
      "entity" => array(
        "id"      => $comment->id,
        "author"  => URL::abs_site("rest/users/{$user->id}"),
        "item"    => URL::abs_site("rest/items/{$item->id}"),
        "text"    => $comment->text,
        "created" => $comment->created,
        "state"   => $comment->state,
        "updated" => $comment->updated));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_response_for_guest_comment() {
    Identity::set_active_user(Identity::admin_user());
    $user = Identity::guest();
    $item = Test::random_photo();
    $comment = Test::random_comment($item, $user);

    $rest = Rest::factory("Comments", $comment->id);

    $expected = array(
      "url" => URL::abs_site("rest/comments/{$comment->id}"),
      "entity" => array(
        "id"          => $comment->id,
        "author"      => URL::abs_site("rest/users/{$user->id}"),
        "item"        => URL::abs_site("rest/items/{$item->id}"),
        "guest_name"  => $comment->guest_name,
        "guest_email" => $comment->guest_email,
        "guest_url"   => $comment->guest_url,
        "text"    => $comment->text,
        "created" => $comment->created,
        "state"   => $comment->state,
        "updated" => $comment->updated));

    $this->assertEquals($expected, $rest->get_response());
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_with_invalid_comment() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $id = $comment->id;
    $comment->delete();

    Rest::factory("Comments", $id)->get_entity();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_without_view_access() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    Access::deny(Identity::everybody(), "view", Item::root());
    Identity::set_active_user(Identity::guest());
    Rest::factory("Comments", $comment->id)->get_entity();
  }

  public function test_get_entity_with_blocked_user_profile() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Module::set_var("gallery", "show_user_profiles_to", "registered_users");

    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $rest = Rest::factory("Comments", $comment->id);

    Identity::set_active_user(Identity::admin_user());
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "author"));

    Identity::set_active_user(Identity::guest());
    $this->assertEmpty(Arr::get($rest->get_entity(), "author"));
  }

  public function test_put_entity() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->text = "Hello World!";

    $rest = Rest::factory("Comments", $comment->id, $params);
    $rest->put_entity();

    $this->assertEquals("Hello World!", $comment->reload()->text);
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_entity_admin_only() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->text = "hello foo";

    Identity::set_active_user(Test::random_user());
    $rest = Rest::factory("Comments", $comment->id, $params);
    $rest->put_entity();
  }

  public function test_delete() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    $rest = Rest::factory("Comments", $comment->id);
    $rest->delete();

    $this->assertFalse($comment->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_admin_only() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment = Test::random_comment($item);

    Identity::set_active_user(Test::random_user());
    $rest = Rest::factory("Comments", $comment->id);
    $rest->delete();
  }

  public function test_get_members() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();

    // We sleep to make sure the comment "created" times are different.
    sleep(1);
    $comment1 = Test::random_comment($item);
    sleep(1);
    $comment2 = Test::random_comment($item);

    $rest1 = Rest::factory("Comments", $comment1->id);
    $rest2 = Rest::factory("Comments", $comment2->id);

    // Get with no query params - comment2 first, comment1 second, possibly other comments follow.
    $members = Rest::factory("Comments")->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, array_search($rest1, $members));

    // Get with "start=1" query param - comment1 first.
    $members = Rest::factory("Comments", null, array("start" => 1))->get_members();
    $this->assertSame(0, array_search($rest1, $members));

    // Get with "num=1" query param - only comment2.
    $members = Rest::factory("Comments", null, array("num" => 1))->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, count($members));
  }

  public function test_post_entity() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->item = Rest::factory("Items", $item->id)->url();
    $params["entity"]->text = "Hello world!";

    $rest = Rest::factory("Comments", null, $params);
    $rest->post_entity();

    $comment = ORM::factory("Comment", $rest->id);
    $this->assertTrue($comment->loaded());
    $this->assertEquals("Hello world!", $comment->text);
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_post_entity_fails_without_permission() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();

    // User is guest and has view access, but cannot comment.
    Identity::set_active_user(Identity::guest());
    Access::allow(Identity::everybody(), "view", Item::root());
    Module::set_var("comment", "access_permissions", "registered_users");

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->item = Rest::factory("Items", $item->id)->url();
    $params["entity"]->text = "Hello world!";

    $rest = Rest::factory("Comments", null, $params);
    $rest->post_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_post_entity_fails_without_item() {
    Identity::set_active_user(Identity::admin_user());

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->text = "Hello world!";

    $rest = Rest::factory("Comments", null, $params);
    $rest->post_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_post_entity_fails_with_invalid_item() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->item = Rest::factory("Comments", $item->id)->url();  // should be "Items"
    $params["entity"]->text = "Hello world!";

    $rest = Rest::factory("Comments", null, $params);
    $rest->post_entity();
  }
}
