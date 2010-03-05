<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class Tag_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    try {
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");
    } catch (Exception $e) { }
  }

  public function get_test() {
    $tag = tag::add(item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = rest::url("tag", $tag);
    $this->assert_equal_array(
      array("url" => rest::url("tag", $tag),
            "entity" => $tag->as_array(),
            "relationships" => array(
              "items" => array(
                "url" => rest::url("tag_items", $tag),
                "members" => array(
                  rest::url("tag_item", $tag, item::root()))))),
      tag_rest::get($request));
  }

  public function get_with_invalid_url_test() {
    $request = new stdClass();
    $request->url = "bogus";
    try {
      tag_rest::get($request);
    } catch (Kohana_404_Exception $e) {
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function get_with_no_relationships_test() {
    $tag = test::random_tag();

    $request = new stdClass();
    $request->url = rest::url("tag", $tag);
    $this->assert_equal_array(
      array("url" => rest::url("tag", $tag),
            "entity" => $tag->as_array(),
            "relationships" => array(
              "items" => array(
                "url" => rest::url("tag_items", $tag),
                "members" => array()))),
      tag_rest::get($request));
  }

  public function post_test() {
    $tag = test::random_tag();

    // Create an editable item to be tagged
    $album = test::random_album();
    access::allow(identity::everybody(), "edit", $album);

    // Add the album to the tag
    $request = new stdClass();
    $request->url = rest::url("tag", $tag);
    $request->params = new stdClass();
    $request->params->url = rest::url("item", $album);
    $this->assert_equal_array(
      array("url" => rest::url("tag_item", $tag, $album)),
      tag_rest::post($request));
  }

  public function post_with_no_item_url_test() {
    $request = new stdClass();
    try {
      tag_rest::post($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal(400, $e->getCode());
      return;
    }

    $this->assert_true(false, "Shouldn't get here");
  }

  public function put_test() {
    $tag = test::random_tag();
    $request = new stdClass();
    $request->url = rest::url("tag", $tag);
    $request->params = new stdClass();
    $request->params->name = "new name";

    tag_rest::put($request);
    $this->assert_equal("new name", $tag->reload()->name);
  }

  public function delete_tag_test() {
    $tag = test::random_tag();
    $request = new stdClass();
    $request->url = rest::url("tag", $tag);
    tag_rest::delete($request);

    $this->assert_false($tag->reload()->loaded());
  }

  public function resolve_test() {
    $tag = test::random_tag();

    $this->assert_equal(
      $tag->as_array(),
      rest::resolve(rest::url("tag", $tag))->as_array());
  }
}
