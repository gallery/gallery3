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

class Rest_Batch_Test extends Unittest_TestCase {
  public function test_get_response() {
    $urls = array(
      Rest::factory("Mock", null, array("expand_members" => true))->url(),
      Rest::factory("Mock", null)->url(),
      Rest::factory("Mock", 123, array("expand_members" => true))->url(),       // 400 by Rest_Mock
      Rest::factory("Mock", 123)->url(),
      Rest::factory("Data", Item::root()->id, array("size" => "thumb"))->url(), // 400 by Rest_Batch
      URL::abs_site("not_rest")                                                 // 400 by Rest_Batch
    );

    $rest = Rest::factory("Batch", null, array("urls" => implode(",", $urls)));

    $expected = array(
      0 => array(
        0 => array(
          "url" => URL::abs_site("rest") . "/mock/1",
          "entity" => array(
            "id" => 1,
            "foo" => "bar"
          )),
        1 => array(
          "url" => URL::abs_site("rest") . "/mock/2",
          "entity" => array(
            "id" => 2,
            "foo" => "bar"
          )),
        2 => array(
          "url" => URL::abs_site("rest") . "/mock/3",
          "entity" => array(
            "id" => 3,
            "foo" => "bar"
          ))),
      1 => array(
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
        )),
      2 => array(
        "error" => 400),
      3 => array(
        "url" => URL::abs_site("rest") . "/mock/123",
        "entity" => array(
          "id" => 123,
          "foo" => "bar"
        )),
      4 => array(
        "error" => 400),
      5 => array(
        "error" => 400)
      );

    $this->assertEquals($expected, $rest->get_response());
  }
}
