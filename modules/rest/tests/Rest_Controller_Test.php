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

class Rest_Controller_Test extends Unittest_TestCase {
  public $save_headers;
  public $save_view;
  public $save_view_type;

  public function setup() {
    parent::setup();

    // Start with permissive settings, then tests restrict as needed.
    Module::set_var("rest", "allow_guest_access", true);
    Module::set_var("rest", "allow_jsonp_output", true);
    Module::set_var("rest", "cors_embedding", "all");
    Module::set_var("rest", "approved_domains", "");

    // This stuff is changed by RestAPI::init(), which is called in Controller_Rest::execute().
    $this->save_headers   = Response::$default_config["_headers"];
    $this->save_view      = Kohana_Exception::$error_view;
    $this->save_view_type = Kohana_Exception::$error_view_content_type;
  }

  public function teardown() {
    // De-init REST...
    Response::$default_config["_headers"]      = $this->save_headers;
    Kohana_Exception::$error_view              = $this->save_view;
    Kohana_Exception::$error_view_content_type = $this->save_view_type;

    parent::teardown();
  }

  public function test_login() {
    $user = Test::random_user("foobar");

    $response = Request::factory("rest")
      ->method(HTTP_Request::POST)
      ->post(array("user" => $user->name, "password" => "foobar"))
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertEquals('"' . RestAPI::access_key($user) . '"', $response->body());
    $this->assertEquals($user->id, Identity::active_user()->id);
  }

  public function test_login_failed() {
    $user = Test::random_user("foobar");

    $response = Request::factory("rest")
      ->method(HTTP_Request::POST)
      ->post(array("user" => $user->name, "password" => "NOT_foobar"))
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_login_failed_with_bad_field_names() {
    $user = Test::random_user("foobar");

    $response = Request::factory("rest")
      ->method(HTTP_Request::POST)
      ->post(array("NOT_user" => $user->name, "password" => "foobar"))
      ->execute();

    $this->assertEquals(403, $response->status());

    $response = Request::factory("rest")
      ->method(HTTP_Request::POST)
      ->post(array("user" => $user->name, "NOT_password" => "foobar"))
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_no_login_with_guests() {
    $user = Identity::guest();

    $response = Request::factory("rest")
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertEquals('""', $response->body());
    $this->assertEquals($user->id, Identity::active_user()->id);

    Module::set_var("rest", "allow_guest_access", false);
    $response = Request::factory("rest")
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_bogus_method() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::TRACE)
      ->execute();

    $this->assertEquals(405, $response->status());

    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::POST)
      ->headers("X-Gallery-Request-Method", HTTP_Request::TRACE)
      ->execute();

    $this->assertEquals(405, $response->status());
  }

  public function test_common_headers() {
    // Should appear if we have a normal response...
    $response = Request::factory("rest/mock")
      ->execute();

    $this->assertEquals(array("GET", "POST", "PUT", "DELETE", "OPTIONS"), $response->headers("Allow"));
    $this->assertEquals("3.1", $response->headers("X-Gallery-Api-Version"));
    $this->assertEquals("SAMEORIGIN", $response->headers("X-Frame-Options"));  // not REST-specific

    // ... or an error response.
    Module::set_var("rest", "allow_guest_access", false);
    $response = Request::factory("rest/mock")
      ->execute();

    $this->assertEquals(array("GET", "POST", "PUT", "DELETE", "OPTIONS"), $response->headers("Allow"));
    $this->assertEquals("3.1", $response->headers("X-Gallery-Api-Version"));
    $this->assertEquals("SAMEORIGIN", $response->headers("X-Frame-Options"));  // not REST-specific
  }

  public function test_cors_preflight_options() {
    Module::set_var("rest", "cors_embedding", "list");
    Module::set_var("rest", "approved_domains", "foobar.com");

    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Origin", "foobar.com")
      ->headers("Access-Control-Request-Method", HTTP_Request::GET)
      ->headers("Access-Control-Request-Headers", "X-Requested-With")
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertEquals("foobar.com",
      $response->headers("Access-Control-Allow-Origin"));
    $this->assertEquals(array("GET", "POST", "PUT", "DELETE", "OPTIONS"),
      $response->headers("Access-Control-Allow-Methods"));
    $this->assertEquals(array("X-Gallery-Request-Key", "X-Gallery-Request-Method", "X-Requested-With"),
      $response->headers("Access-Control-Allow-Headers"));
    $this->assertEquals(array("X-Gallery-Api-Version", "Allow"),
      $response->headers("Access-Control-Expose-Headers"));
    $this->assertEquals(604800,
      $response->headers("Access-Control-Max-Age"));
  }

  public function test_cors_preflight_options_fail_without_origin() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Access-Control-Request-Method", HTTP_Request::GET)
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_cors_preflight_options_fail_without_method() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Origin", "foobar.com")
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_cors_preflight_options_pass_without_headers() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Origin", "foobar.com")
      ->headers("Access-Control-Request-Method", HTTP_Request::GET)
      ->execute();

    $this->assertEquals(200, $response->status());
  }

  public function test_cors_preflight_options_fail_with_bad_headers() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Origin", "foobar.com")
      ->headers("Access-Control-Request-Method", HTTP_Request::GET)
      ->headers("Access-Control-Request-Headers", "X-Bad-Header")
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_cors_preflight_options_fail_with_bad_method() {
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::OPTIONS)
      ->headers("Origin", "foobar.com")
      ->headers("Access-Control-Request-Method", HTTP_Request::TRACE)
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_cors_origin_check() {
    Module::set_var("rest", "cors_embedding", "list");
    Module::set_var("rest", "approved_domains", "foobar.com");

    // No request origin --> no response header
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::GET)
      ->execute();
    $this->assertEquals(200, $response->status());
    $this->assertEquals(null, $response->headers("Access-Control-Allow-Origin"));


    // Allowed request origin --> origin as response header
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::GET)
      ->headers("Origin", "foobar.com")
      ->execute();
    $this->assertEquals(200, $response->status());
    $this->assertEquals("foobar.com", $response->headers("Access-Control-Allow-Origin"));

    // Disallowed request origin --> no response header
    $response = Request::factory("rest/mock")
      ->method(HTTP_Request::GET)
      ->headers("Origin", "NOTfoobar.com")
      ->execute();
    $this->assertEquals(200, $response->status());
    $this->assertEquals(null, $response->headers("Access-Control-Allow-Origin"));
  }

  public function test_output_html() {
    $response = Request::factory("rest/mock")
      ->query("output", "html")
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertEquals("<pre>", substr($response->body(), 0, 5));
    $this->assertEquals("</pre>", substr($response->body(), -6));
  }

  public function test_output_jsonp() {
    $response = Request::factory("rest/mock")
      ->query("output", "jsonp")
      ->query("callback", "foo")
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertEquals("foo(", substr($response->body(), 0, 4));
    $this->assertEquals(")", substr($response->body(), -1));
  }

  public function test_output_jsonp_requires_callback() {
    $response = Request::factory("rest/mock")
      ->query("output", "jsonp")
      ->execute();

    $this->assertEquals(400, $response->status());
  }

  public function test_output_jsonp_blocked_with_keys() {
    $response = Request::factory("rest/mock")
      ->query("output", "jsonp")
      ->query("callback", "foo")
      ->headers("X-Gallery-Request-Key", RestAPI::access_key(Identity::admin_user()))
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_output_jsonp_blocked_by_config() {
    Module::set_var("rest", "allow_jsonp_output", false);

    $response = Request::factory("rest/mock")
      ->query("output", "jsonp")
      ->query("callback", "foo")
      ->execute();

    $this->assertEquals(403, $response->status());
  }

  public function test_get_object() {
    $response = Request::factory("rest/mock/1")
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array(
      "url" => URL::abs_site("rest") . "/mock/1",
      "entity" => array(
        "id" => 1,
        "foo" => "bar"
      ));

    $this->assertEquals(200, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_get_collection() {
    $response = Request::factory("rest/mock")
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array(
      "url" => URL::abs_site("rest") . "/mock",
      "members" => array(
        0 => URL::abs_site("rest") . "/mock/1",
        1 => URL::abs_site("rest") . "/mock/2",
        2 => URL::abs_site("rest") . "/mock/3"
      ),
      "members_info" => array(
        "count" => 3,
        "num" => 100,
        "start" => 0
      ));

    $this->assertEquals(200, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_post_new_object() {
    $url = URL::abs_site("rest") . "/mock/123";
    $entity = array("id" => 123, "foo" => "baz");

    $response = Request::factory("rest/mock") // URL has no id
      ->method(HTTP_Request::POST)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("url" => $url);

    $this->assertEquals(201, $response->status());
    $this->assertEquals($url, $response->headers("Location"));
    $this->assertEquals($expected, $actual);
  }

  public function test_post_existing_object() {
    $url = URL::abs_site("rest") . "/mock/123";
    $entity = array("id" => 123, "foo" => "baz");

    $response = Request::factory("rest/mock/123") // URL has same id
      ->method(HTTP_Request::POST)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("url" => $url);

    $this->assertEquals(200, $response->status());
    $this->assertEquals(null, $response->headers("Location"));
    $this->assertEquals($expected, $actual);
  }

  public function test_post_with_bad_members() {
    $entity = array("id" => 123, "foo" => "baz");
    $members = array(0 => URL::abs_site("rest") . "/nonexistent_resource/123");

    $response = Request::factory("rest/mock") // URL has no id
      ->method(HTTP_Request::POST)
      ->post("entity", json_encode($entity))
      ->post("members", json_encode($members))
      ->execute();

    $this->assertEquals(400, $response->status());
  }

  public function test_post_with_bad_relationships() {
    $entity = array("id" => 123, "foo" => "baz");
    $relationships = array("foo" => array("members" =>
      array(0 => URL::abs_site("rest") . "/nonexistent_resource/123")));

    $response = Request::factory("rest/mock") // URL has no id
      ->method(HTTP_Request::POST)
      ->post("entity", json_encode($entity))
      ->post("relationships", json_encode($relationships))
      ->execute();

    $this->assertEquals(400, $response->status());
  }

  public function test_put_with_orm_exception() {
    $entity = array("exception" => "orm");

    $response = Request::factory("rest/mock/123")
      ->method(HTTP_Request::PUT)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("errors" => array("type" => array(0 => "read_only", 1 => null)));

    $this->assertEquals(400, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_put_with_gallery_exception() {
    $entity = array("exception" => "gallery");

    $response = Request::factory("rest/mock/123")
      ->method(HTTP_Request::PUT)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("errors" => array("other" => "mock exception"));

    $this->assertEquals(500, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_put_with_rest_exception() {
    $entity = array("exception" => "rest");

    $response = Request::factory("rest/mock/123")
      ->method(HTTP_Request::PUT)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("errors" => array("mock" => "exception"));

    $this->assertEquals(400, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_put_with_http_exception() {
    $entity = array("exception" => "http");

    $response = Request::factory("rest/mock/123")
      ->method(HTTP_Request::PUT)
      ->post("entity", json_encode($entity))
      ->execute();

    $actual = json_decode($response->body(), true);  // assoc array
    $expected = array("errors" => array("other" => "mock exception"));

    $this->assertEquals(400, $response->status());
    $this->assertEquals($expected, $actual);
  }

  public function test_post_with_relationship_entity() {
    // Note: this test doesn't use our Rest_Mock class, as it's hard to test
    // relationships when there's no DB storage of the mock objects.

    $parent = Test::random_album();
    $name = Test::random_name();
    $tag = Test::random_tag();

    $entity = array("type" => "album", "name" => $name);
    $relationships = array("tags" => array("entity" => array("names" => $tag->name)));

    $response = Request::factory("rest/items/{$parent->id}")
      ->method(HTTP_Request::POST)
      ->headers("X-Gallery-Request-Key", RestAPI::access_key(Identity::admin_user()))
      ->post("entity", json_encode($entity))
      ->post("relationships", json_encode($relationships))
      ->execute();

    $item = ORM::factory("Item")
      ->where("parent_id", "=", $parent->id)
      ->where("type", "=", "album")
      ->where("name", "=", $name)
      ->find();

    $this->assertEquals(201, $response->status());
    $this->assertTrue($item->loaded());
    $this->assertTrue($tag->has("items", $item));
  }

  public function test_put_with_relationship_members() {
    // Note: this test doesn't use our Rest_Mock class, as it's hard to test
    // relationships when there's no DB storage of the mock objects.

    $item = Test::random_album();
    $tag = Test::random_tag();

    $relationships = array("tags" => array("members" => Rest::factory("Tags", $tag->id)->url()));

    $response = Request::factory("rest/items/{$item->id}")
      ->method(HTTP_Request::PUT)
      ->headers("X-Gallery-Request-Key", RestAPI::access_key(Identity::admin_user()))
      ->post("relationships", json_encode($relationships))
      ->execute();

    $this->assertEquals(200, $response->status());
    $this->assertTrue($tag->has("items", $item));
  }
}
