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
class Items_Rest_Helper_Test extends Unittest_Testcase {
  public function test_get_url() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      Rest::url("item", $photo1),
      Rest::url("item", $album2)));
    $this->assertEquals(
      array(
        array("url" => Rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => Rest::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => Rest::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => Rest::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 Rest::url("item", $photo2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_album() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      Rest::url("item", $photo2),
      Rest::url("item", $album1)));
    $request->params->type = "album";
    $this->assertEquals(
      array(
         array("url" => Rest::url("item", $album1),
               "entity" => $album1->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => Rest::url("item_comments", $album1)),
                 "tags" => array(
                   "url" => Rest::url("item_tags", $album1),
                   "members" => array())),
               "members" => array(
                 Rest::url("item", $album2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_photo() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      Rest::url("item", $photo1),
      Rest::url("item", $album2)));
    $request->params->type = "photo";
    $this->assertEquals(
      array(
        array("url" => Rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $photo1),
                  "members" => array())))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_albums_photos() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      Rest::url("item", $photo1),
      Rest::url("item", $album2)));
    $request->params->type = "photo,album";
    $this->assertEquals(
      array(
        array("url" => Rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => Rest::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => Rest::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => Rest::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 Rest::url("item", $photo2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_ancestors() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $root = ORM::factory("Item", 1);
    $restful_root = array(
      "url" => Rest::url("item", $root),
      "entity" => $root->as_restful_array(),
      "relationships" => Rest::relationships("item", $root));
    $restful_root["members"] = array();
    foreach ($root->children->find_all() as $child) {
      $restful_root["members"][] = Rest::url("item", $child);
    }

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->ancestors_for = Rest::url("item", $photo2);
    $this->assertEquals(
      array(
        $restful_root,
        array("url" => Rest::url("item", $album1),
              "entity" => $album1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $album1)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $album1),
                  "members" => array())),
              "members" => array(
                Rest::url("item", $photo1),
                Rest::url("item", $album2)),
            ),
        array("url" => Rest::url("item", $album2),
              "entity" => $album2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $album2)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $album2),
                  "members" => array())),
              "members" => array(
                Rest::url("item", $photo2))),
        array("url" => Rest::url("item", $photo2),
              "entity" => $photo2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => Rest::url("item_comments", $photo2)),
                "tags" => array(
                  "url" => Rest::url("item_tags", $photo2),
                  "members" => array())))),
      Hook_Rest_Items::get($request));
  }
}
