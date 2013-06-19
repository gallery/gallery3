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

class Rest_Test extends Unittest_TestCase {
  // Note: most of the Rest class functionality is tested indirectly by the Controller_Rest tests.
  // There are just a couple things here we'd like to test more directly...
  public $query;

  public function setup() {
    parent::setup();
    $this->query = Request::current()->query();
  }

  public function teardown() {
    Request::current()->query($this->query);
    parent::teardown();
  }

  public function test_factory_and_construct() {
    $rest = Rest::factory("Mock", 1, array("hello" => "world"));

    $this->assertEquals("Mock", $rest->type);
    $this->assertEquals(1, $rest->id);
    $this->assertEquals(array("hello" => "world"), $rest->params);
  }

  public function test_url_with_sticky_params() {
    $rest = Rest::factory("Mock", 1, array("hello" => "world"));

    // Test with no query params
    $expected = array("hello" => "world");
    $this->assertEquals(URL::abs_site("rest/mock/1") . URL::query($expected, false), $rest->url());

    // Add query params
    $params = array(
      "not_sticky" => "foo",
      "hello"      => "goodbye",
      "access_key" => "abc",
      "num"        => 100,
      "output"     => "html");

    Request::current()->query($params);

    // Test with query params
    $expected = array(
      "hello"      => "world",
      "access_key" => "abc",
      "num"        => 100,
      "output"     => "html");

    $this->assertEquals(URL::abs_site("rest/mock/1") . URL::query($expected, false), $rest->url());
  }
}