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
class Items_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function get_url_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      rest::url("item", $photo1),
      rest::url("item", $album2)));
    $this->assert_equal_array(
      array(
        array("url" => rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => rest::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => rest::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => rest::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => rest::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 rest::url("item", $photo2)))),
      items_rest::get($request));
  }

  public function get_url_filter_album_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      rest::url("item", $photo2),
      rest::url("item", $album1)));
    $request->params->type = "album";
    $this->assert_equal_array(
      array(
         array("url" => rest::url("item", $album1),
               "entity" => $album1->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => rest::url("item_comments", $album1)),
                 "tags" => array(
                   "url" => rest::url("item_tags", $album1),
                   "members" => array())),
               "members" => array(
                 rest::url("item", $album2)))),
      items_rest::get($request));
  }

  public function get_url_filter_photo_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      rest::url("item", $photo1),
      rest::url("item", $album2)));
    $request->params->type = "photo";
    $this->assert_equal_array(
      array(
        array("url" => rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => rest::url("item_tags", $photo1),
                  "members" => array())))),
      items_rest::get($request));
  }

  public function get_url_filter_albums_photos_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      rest::url("item", $photo1),
      rest::url("item", $album2)));
    $request->params->type = "photo,album";
    $this->assert_equal_array(
      array(
        array("url" => rest::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => rest::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => rest::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => rest::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => rest::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 rest::url("item", $photo2)))),
      items_rest::get($request));
  }

  public function get_ancestors_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $root = ORM::factory("item", 1);
    $restful_root = array(
      "url" => rest::url("item", $root),
      "entity" => $root->as_restful_array(),
      "relationships" => rest::relationships("item", $root));
    $restful_root["members"] = array();
    foreach ($root->children() as $child) {
      $restful_root["members"][] = rest::url("item", $child);
    }

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->ancestors_for = rest::url("item", $photo2);
    $this->assert_equal_array(
      array(
        $restful_root,
        array("url" => rest::url("item", $album1),
              "entity" => $album1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $album1)),
                "tags" => array(
                  "url" => rest::url("item_tags", $album1),
                  "members" => array())),
              "members" => array(
                rest::url("item", $photo1),
                rest::url("item", $album2)),
            ),
        array("url" => rest::url("item", $album2),
              "entity" => $album2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $album2)),
                "tags" => array(
                  "url" => rest::url("item_tags", $album2),
                  "members" => array())),
              "members" => array(
                rest::url("item", $photo2))),
        array("url" => rest::url("item", $photo2),
              "entity" => $photo2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => rest::url("item_comments", $photo2)),
                "tags" => array(
                  "url" => rest::url("item_tags", $photo2),
                  "members" => array())))),
      items_rest::get($request));
  }
}
