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
class Rest_UserComments_Test extends Unittest_TestCase {
  public function test_get_response() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();
    $user = Test::random_user();

    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);
    $comment3 = Test::random_comment($item, Identity::admin_user()); // will not appear in list

    $rest = Rest::factory("UserComments", $user->id);

    $expected = array(
      "url" => URL::abs_site("rest/user_comments/{$user->id}"),
      "members" => array(
        0 => URL::abs_site("rest/comments/{$comment2->id}"),    // comment2 is newer than comment1
        1 => URL::abs_site("rest/comments/{$comment1->id}")));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_members() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();
    $user = Test::random_user();

    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);
    $comment3 = Test::random_comment($item, Identity::admin_user()); // will not appear in list

    $rest1 = Rest::factory("Comments", $comment1->id);
    $rest2 = Rest::factory("Comments", $comment2->id);

    // Get with no query params - comment2 first, comment1 second, possibly other comments follow.
    $members = Rest::factory("UserComments", $user->id)->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, array_search($rest1, $members));
    $this->assertSame(2, count($members));

    // Get with "start=1" query param - comment1 first.
    $members = Rest::factory("UserComments", $user->id, array("start" => 1))->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, count($members));

    // Get with "num=1" query param - only comment2.
    $members = Rest::factory("UserComments", $user->id, array("num" => 1))->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, count($members));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_with_invalid_user() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $id = $user->id;
    $user->delete();

    Rest::factory("UserComments", $id)->get_members();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_members_without_view_access() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $item = Test::random_photo();
    $comment = Test::random_comment($item, $user);

    Access::allow(Identity::everybody(), "view", Item::root());
    Module::set_var("gallery", "show_user_profiles_to", "registered_users");
    Identity::set_active_user(Identity::guest());

    Rest::factory("UserComments", $user->id)->get_members();
  }

  public function test_put_members() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $item = Test::random_photo();
    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Comments", $comment2->id));

    $rest = Rest::factory("UserComments", $user->id, $params);
    $rest->put_members();

    // PUT replaces member list - user has comment2, but no longer comment1.
    $this->assertFalse($comment1->reload()->loaded());
    $this->assertTrue($comment2->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_members_admin_only() {
    Access::allow(Identity::everybody(), "view", Item::root());  // ensure it's not an access problem
    Module::set_var("gallery", "show_user_profiles_to", "everybody");  // ditto

    $user = Test::random_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Comments", $comment2->id));

    $rest = Rest::factory("UserComments", $user->id, $params);
    $rest->put_members();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_wrong_member_types() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $item = Test::random_photo();
    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);

    $params = array();
    $params["members"] = array(0 => Rest::factory("Items", $comment2->id)); // should be "Comments"

    $rest = Rest::factory("UserComments", $user->id, $params);
    $rest->put_members();
  }

  public function test_delete() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $item = Test::random_photo();
    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);

    $rest = Rest::factory("UserComments", $user->id, $params);
    $rest->delete();

    // DELETE removes all members - item has neither comment2 nor comment1.
    $this->assertFalse($comment1->reload()->loaded());
    $this->assertFalse($comment2->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_admin_only() {
    Access::allow(Identity::everybody(), "view", Item::root());  // ensure it's not an access problem
    Module::set_var("gallery", "show_user_profiles_to", "everybody");  // ditto

    $user = Test::random_user();
    Identity::set_active_user($user);
    $item = Test::random_photo();
    $comment1 = Test::random_comment($item, $user);
    $comment2 = Test::random_comment($item, $user);

    $rest = Rest::factory("UserComments", $user->id, $params);
    $rest->delete();
  }
}
