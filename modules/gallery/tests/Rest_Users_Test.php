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
class Rest_Users_Test extends Unittest_TestCase {
  public function test_get_response() {
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $user = Test::random_user();
    Identity::set_active_user($user);
    $item = Test::random_album();

    $rest = Rest::factory("Users", $user->id);

    $expected = array(
      "url" => URL::abs_site("rest/users/{$user->id}"),
      "entity" => array(
        "id"        => $user->id,
        "name"      => $user->name,
        "full_name" => $user->full_name,
        "email"     => $user->email,
        "url"       => $user->url,
        "locale"    => null),
      "relationships" => array(
        "comments" => array(
          "url" => URL::abs_site("rest/user_comments/{$user->id}"),
          "members" => array(),
          "members_info" => array(
            "count" => 0,
            "num" => 100,
            "start" => 0)),
        "items" => array(
          "url" => URL::abs_site("rest/user_items/{$user->id}"),
          "members" => array(
            0 => URL::abs_site("rest/items/{$item->id}")),
          "members_info" => array(
            "count" => 1,
            "num" => 100,
            "start" => 0))));

    $this->assertEquals($expected, $rest->get_response());
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_with_invalid_user() {
    Identity::set_active_user(Identity::admin_user());

    $user = Test::random_user();
    $id = $user->id;
    $user->delete();

    Rest::factory("Users", $id)->get_entity();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_without_view_access() {
    Module::set_var("gallery", "show_user_profiles_to", "registered_users");
    Identity::set_active_user(Identity::guest());

    Rest::factory("Users", Identity::admin_user()->id)->get_entity();
  }

  public function test_get_entity_with_show_param() {
    Identity::set_active_user(Identity::admin_user());

    $rest = Rest::factory("Users", null, array("show" => "self"));
    $rest->get_response();  // since "show" parsed in get_response()
    $this->assertEquals(Identity::admin_user()->id, $rest->id);

    $rest = Rest::factory("Users", null, array("show" => "guest"));
    $rest->get_response();  // since "show" parsed in get_response()
    $this->assertEquals(Identity::guest()->id, $rest->id);
  }
}
