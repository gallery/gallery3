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
include_once(MODPATH . "rest/tests/Rest_Mock.php");

class RestAPI_Test extends Unittest_TestCase {
  public function test_guest_key_is_empty() {
    Identity::set_active_user(Identity::guest());
    $this->assertSame(null, RestAPI::access_key());
  }

  public function test_allow_guest_access() {
    Module::set_var("rest", "allow_guest_access", true);
    RestAPI::set_active_user(null);
    $this->assertSame(Identity::guest()->id, Identity::active_user()->id);
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_disallow_guest_access() {
    Module::set_var("rest", "allow_guest_access", false);
    RestAPI::set_active_user(null);
  }

  public function test_get_and_use_key_for_registered_user() {
    // Get an user's key - should not be empty
    $user = Test::random_user();
    $key = RestAPI::access_key($user);
    $this->assertTrue(!empty($key));

    // Reset active user to guest, then set user with key
    Identity::set_active_user(Identity::guest());
    RestAPI::set_active_user($key);
    $this->assertEquals($user->id, Identity::active_user()->id);
  }

  public function test_resolve() {
    $rest = RestAPI::resolve(URL::abs_site("rest") . "/mock/123?hello=world&foo=bar");

    $this->assertEquals("Mock", $rest->type);
    $this->assertEquals(123, $rest->id);
    $this->assertEquals(array("hello" => "world", "foo" => "bar"), $rest->params);
  }

  public function test_resolve_bad_urls() {
    $urls = array(
      URL::abs_site("rest"),
      URL::abs_site("rest") . "/nonexistent_resource/1",
      URL::abs_site("not_rest") . "/mock/1",
      "http://www.badexample.com/gallery3/index.php/rest");

    foreach ($urls as $url) {
      $this->assertSame(null, RestAPI::resolve($url));
    }
  }

  public function test_approve_origin_with_all_setting() {
    Module::set_var("rest", "cors_embedding", "all");
    $this->assertSame("*", RestAPI::approve_origin("http://www.anysite.com"));
    $this->assertSame(false, RestAPI::approve_origin(""));  // always fails with empty origin
  }

  public function test_approve_origin_with_none_setting() {
    Module::set_var("rest", "cors_embedding", "none");
    Module::set_var("rest", "approved_domains", "foobar.com");
    $this->assertSame(false, RestAPI::approve_origin("foobar.com"));
  }

  public function test_approve_origin_with_list_setting() {
    Module::set_var("rest", "cors_embedding", "list");
    Module::set_var("rest", "approved_domains", "foobar.com");

    $passes = array("foobar.com", "my.foobar.com", "http://foobar.com", "https://www.foobar.com");
    foreach ($passes as $pass) {
      $this->assertSame($pass, RestAPI::approve_origin($pass));
    }

    $fails = array("bar.com", "foobar.org", "foobar.com/", "myfoobar.com");
    foreach ($fails as $fail) {
      $this->assertSame(false, RestAPI::approve_origin($fail));
    }
  }
}