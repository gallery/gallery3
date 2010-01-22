<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
    $this->_save = array($_GET, $_POST, $_SERVER);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
  }

  public function get_test() {
    $tag = tag::add(item::root(), "tag1")->reload();

    $request->url = rest::url("tag", $tag);
    $this->assert_equal_array(
      array("resource" => $tag->as_array(),
            "members" => array(rest::url("gallery", item::root()))),
      tag_rest::get($request));
  }

  public function get_with_invalid_url_test() {
    $request->url = "bogus";
    try {
      tag_rest::get($request);
    } catch (Kohana_404_Exception $e) {
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function get_with_no_members_test() {
    $tag = test::random_tag();

    $request->url = rest::url("tag", $tag);
    $this->assert_equal_array(
      array("resource" => $tag->as_array(), "members" => array()),
      tag_rest::get($request));
  }

  public function post_test() {
    $tag = test::random_tag();

    // Create an editable item to be tagged
    $album = test::random_album();
    access::allow(identity::everybody(), "edit", $album);

    // Add the album to the tag
    $request->url = rest::url("tag", $tag);
    $request->params->url = rest::url("gallery", $album);
    $this->assert_equal_array(
      array("url" => rest::url("tag", $tag)),
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
    $request->url = rest::url("tag", $tag);
    $request->params->name = "new name";

    $this->assert_equal_array(
      array("url" => str_replace($tag->name, "new%20name", rest::url("tag", $tag))),
      tag_rest::put($request));
    $this->assert_equal("new name", $tag->reload()->name);
  }

  public function delete_tag_test() {
    $tag = test::random_tag();
    $request->url = rest::url("tag", $tag);
    tag_rest::delete($request);

    $this->assert_false($tag->reload()->loaded());
  }

  public function delete_item_from_tag_test() {
    $album = test::random_album();
    access::allow(identity::everybody(), "edit", $album);

    $tag = tag::add($album, "tag1");
    $this->assert_equal(1, $tag->items()->count());

    $request->url = rest::url("tag", $tag);
    $request->params->url = rest::url("gallery", $album);
    tag_rest::delete($request);

    $this->assert_equal(0, $tag->items()->count());
  }

  public function delete_item_from_tag_fails_without_permissions_test() {
    $album = test::random_album();
    $tag = tag::add($album, "tag1");
    $this->assert_equal(1, $tag->items()->count());

    access::deny(identity::everybody(), "edit", $album);

    $request->url = rest::url("tag", $tag);
    $request->params->url = rest::url("gallery", $album);

    try {
      tag_rest::delete($request);
    } catch (Exception $e) {
      $this->assert_equal(403, $e->getCode());
      return;
    }

    $this->assert_true(false, "Shouldn't get here");
  }

  public function resolve_test() {
    $tag = test::random_tag();

    $this->assert_equal(
      $tag->as_array(),
      rest::resolve(rest::url("tag", $tag))->as_array());
  }
}
