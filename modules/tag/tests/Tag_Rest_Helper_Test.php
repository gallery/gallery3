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
class Tag_Rest_Helper_Test extends Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER, $_FILES);
    $this->_saved_active_user = identity::active_user();

    $this->_user = identity::create_user("access_test", "Access Test", "password");
    $key = ORM::factory("user_access_token");
    $this->_access_key = $key->access_key = md5($this->_user->name . rand());
    $key->user_id = $this->_user->id;
    $key->save();

    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, "album", "Test Album", rand());
    tag::add($this->_album, "albums");
    tag::add($this->_album, "A1");
    tag::add($this->_album, "T1");
    $this->_child = album::create($this->_album, "child", "Test Child Album", rand());
    tag::add($this->_child, "albums");
    tag::add($this->_child, "C1");
    tag::add($this->_child, "T1");

    $filename = MODPATH . "gallery/tests/test.jpg";
    $rand = rand();
    $this->_photo = photo::create($this->_child, $filename, "$rand.jpg", $rand);
    tag::add($this->_photo, "photos");
    tag::add($this->_photo, "P1");
    tag::add($this->_photo, "T1");

    $filename = MODPATH . "gallery/tests/test.jpg";
    $rand = rand();
    $this->_sibling = photo::create($this->_album, $filename, "$rand.jpg", $rand);
    tag::add($this->_sibling, "photos");
    tag::add($this->_sibling, "P3");
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER, $_FILES) = $this->_save;
    identity::set_active_user($this->_saved_active_user);

    try {
      if (!empty($this->_user)) {
        $this->_user->delete();
      }
      if (!empty($this->_album)) {
        $this->_album->delete();
      }
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");

    } catch (Exception $e) { }
  }

  public function tag_rest_get_all_test() {
    $request = (object)array("arguments" => array(), "limit" => 2, "offset" => 1);

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array(array("name" => "albums", "count" => 2),
                                        array("name" => "photos", "count" => 2)))),
      tag_rest::get($request));
  }

  public function tag_rest_get_tags_for_item_test() {
    $request = (object)array("arguments" => explode("/", $this->_photo->relative_url()));

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array("photos", "P1", "T1"))),
      tag_rest::get($request));
  }

  public function tag_rest_get_items_test() {
    $request = (object)array("arguments" => array("albums"));

    $resources = array();
    foreach (array($this->_album, $this->_child) as $resource) {
      $resources[] = array("type" => $resource->type,
                           "has_children" => $resource->children_count() > 0,
                           "path" => $resource->relative_url(),
                           "thumb_url" => $resource->thumb_url(),
                           "thumb_dimensions" => array(
                             "width" => $resource->thumb_width,
                             "height" => $resource->thumb_height),
                           "has_thumb" => $resource->has_thumb(),
                           "title" => $resource->title);

    }
    $this->assert_equal(json_encode(array("status" => "OK", "resources" => $resources)),
                        tag_rest::get($request));
  }

  public function tag_rest_add_tags_for_item_no_path_test() {
    $request = (object)array("arguments" => array("new,one"));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => "Invalid request")),
      tag_rest::post($request));
  }

  public function tag_rest_add_tags_for_item_not_found_test() {
    $request = (object)array("path" => $this->_photo->relative_url() . "b",
                             "arguments" => array("new,one"));
    try {
      tag_rest::post($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_add_tags_for_item_no_access_test() {
    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_photo->relative_url(),
                             "arguments" => array("new,one"));

    try {
      tag_rest::post($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_add_tags_for_item_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);
    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_photo->relative_url(),
                             "arguments" => array("new,one"));

    $this->assert_equal(
      json_encode(array("status" => "OK")),
      tag_rest::post($request));
    $request = (object)array("arguments" => explode("/", $this->_photo->relative_url()));
    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array("photos", "P1", "T1", "new", "one"))),
      tag_rest::get($request));
  }

  public function tag_rest_update_tag_no_arguments_test() {
    $request = (object)array("arguments" => array());

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => "Invalid request")),
      tag_rest::put($request));
  }

  public function tag_rest_update_tag_one_arguments_test() {
    $request = (object)array("arguments" => array("photos"));

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => "Invalid request")),
      tag_rest::put($request));

    $request = (object)array("arguments" => array(), "new_name" => "valid");

    $this->assert_equal(
      json_encode(array("status" => "ERROR", "message" => "Invalid request")),
      tag_rest::put($request));
  }

  public function tag_rest_update_tags_not_found_test() {
    $request = (object)array("arguments" => array("not"), "new_name" => "found");

    try {
      tag_rest::put($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_update_tags_test() {
    $request = (object)array("arguments" => array("albums"), "new_name" => "new name");

    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::put($request));

    $request = (object)array("arguments" => array("new name"));
    $resources = array();
    foreach (array($this->_album, $this->_child) as $resource) {
      $resources[] = array("type" => $resource->type,
                           "has_children" => $resource->children_count() > 0,
                           "path" => $resource->relative_url(),
                           "thumb_url" => $resource->thumb_url(),
                           "thumb_dimensions" => array(
                             "width" => $resource->thumb_width,
                             "height" => $resource->thumb_height),
                           "has_thumb" => $resource->has_thumb(),
                           "title" => $resource->title);

    }
    $this->assert_equal(
      json_encode(array("status" => "OK", "resources" => $resources)),
      tag_rest::get($request));
  }

  public function tag_rest_delete_tag_test() {
    $request = (object)array("arguments" => array("T1,P1"));

    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::delete($request));

    $request = (object)array("arguments" => array("T1,P1"));
    $this->assert_equal(json_encode(array("status" => "OK")),
                        tag_rest::get($request));
  }

  public function tag_rest_delete_tagc_from_item_test() {
    $request = (object)array("arguments" => array("T1,P1"),
                             $this->_photo->relative_url());

    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::delete($request));

    $request = (object)array("arguments" => explode("/", $this->_photo->relative_url()));
    $this->assert_equal(json_encode(array("status" => "OK", "tags" => array("photos"))),
                        tag_rest::get($request));
  }
}
