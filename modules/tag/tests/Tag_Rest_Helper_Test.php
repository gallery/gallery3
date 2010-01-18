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
    try {
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");
    } catch (Exception $e) { }
    $this->_save = array($_GET, $_POST, $_SERVER, $_FILES);
    $this->_saved_active_user = identity::active_user();
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER, $_FILES) = $this->_save;
    identity::set_active_user($this->_saved_active_user);

    try {
      if (!empty($this->_user)) {
        $this->_user->delete();
      }
    } catch (Exception $e) { }
  }

  private function _create_user() {
    if (empty($this->_user)) {
      $this->_user = identity::create_user("access_test" . rand(), "Access Test", "password");
      $key = ORM::factory("user_access_token");
      $key->access_key = md5($this->_user->name . rand());
      $key->user_id = $this->_user->id;
      $key->save();
      identity::set_active_user($this->_user);
    }
    return $this->_user;
  }

  private function _create_album($tags=array(), $parent=null) {
    $album_name = "tag_album_" . rand();
    if (empty($parent)) {
      $parent = ORM::factory("item", 1);
    }
    $album = album::create($parent, $album_name, $album_name, $album_name);
    foreach ($tags as $tag) {
      tag::add($album, $tag);
    }
    return $album;
  }

  private function _create_image($tags=array(), $parent=null) {
    $filename = MODPATH . "gallery/tests/test.jpg";
    $image_name = "tag_image_" . rand();
    if (empty($parent)) {
      $parent = ORM::factory("item", 1);
    }
    $photo = photo::create($parent, $filename, "$image_name.jpg", $image_name);
    foreach ($tags as $tag) {
      tag::add($photo, $tag);
    }
    return $photo;
  }

  public function tag_rest_get_all_test() {
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "C1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);
    $sibling = $this->_create_image(array("photos", "P3"), $album);

    $request = (object)array("arguments" => array(), "limit" => 2, "offset" => 1);

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array(array("name" => "albums", "count" => "2"),
                                        array("name" => "photos", "count" => "2")))),
      tag_rest::get($request));
  }

  public function tag_rest_get_tags_for_item_test() {
    $photo = $this->_create_image(array("photos", "P1", "T1"));

    $request = (object)array("arguments" => explode("/", $photo->relative_url()));

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array("photos", "P1", "T1"))),
      tag_rest::get($request));
  }

  public function tag_rest_get_items_test() {
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "A1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);
    $sibling = $this->_create_image(array("photos", "P3"), $album);
    $child->reload();
    $album->reload();

    $request = (object)array("arguments" => array("albums"));

    $resources = array();
    foreach (array($album, $child) as $resource) {
      $resources[] = array("type" => $resource->type,
                           "has_children" => $resource->children_count() > 0,
                           "path" => $resource->relative_url(),
                           "thumb_url" => $resource->thumb_url(),
                           "thumb_dimensions" => array("width" => $resource->thumb_width,
                                                       "height" => $resource->thumb_height),
                           "has_thumb" => $resource->has_thumb(),
                           "title" => $resource->title);

    }
    $this->assert_equal(json_encode(array("status" => "OK", "resources" => $resources)),
                        tag_rest::get($request));
  }

  public function tag_rest_add_tags_for_item_no_path_test() {
    $request = (object)array("arguments" => array("new,one"));

    try {
      tag_rest::post($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal("400 Bad request", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_add_tags_for_item_not_found_test() {
    $photo = $this->_create_image(array("photos", "P1", "T1"));
    $request = (object)array("path" => $photo->relative_url() . "b",
                             "arguments" => array("new,one"));
    try {
      tag_rest::post($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_add_tags_for_item_no_access_test() {
    $photo = $this->_create_image(array("photos", "P1", "T1"));
    $this->_create_user();
    $request = (object)array("path" => $photo->relative_url(),
                             "arguments" => array("new,one"));

    try {
      tag_rest::post($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_add_tags_for_item_test() {
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "A1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);
    $sibling = $this->_create_image(array("photos", "P3"), $album);
    access::allow(identity::registered_users(), "edit", $child);
    $this->_create_user();
    $request = (object)array("path" => $photo->relative_url(),
                             "arguments" => array("new,one"));

    $this->assert_equal(
      json_encode(array("status" => "OK")),
      tag_rest::post($request));
    $request = (object)array("arguments" => explode("/", $photo->relative_url()));
    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "tags" => array("photos", "P1", "T1", "new", "one"))),
      tag_rest::get($request));
  }

  public function tag_rest_update_tag_no_arguments_test() {
    $request = (object)array("arguments" => array());

    try {
      tag_rest::put($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal("400 Bad request", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function tag_rest_update_tag_one_arguments_test() {
    $request = (object)array("arguments" => array("photos"));
    try {
      tag_rest::put($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal("400 Bad request", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }

    $request = (object)array("arguments" => array(), "new_name" => "valid");
    try {
      tag_rest::put($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal("400 Bad request", $e->getMessage());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
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
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "A1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);
    $child->reload();
    $sibling = $this->_create_image(array("photos", "P3"), $album);
    $child->reload();
    $album->reload();

    $request = (object)array("arguments" => array("albums"), "new_name" => "new name");

    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::put($request));

    $request = (object)array("arguments" => array("new name"));
    $resources = array();
    foreach (array($album, $child) as $resource) {
      $resources[] = array("type" => $resource->type,
                           "has_children" => $resource->children_count() > 0,
                           "path" => $resource->relative_url(),
                           "thumb_url" => $resource->thumb_url(),
                           "thumb_dimensions" => array("width" => $resource->thumb_width,
                                                       "height" => $resource->thumb_height),
                           "has_thumb" => $resource->has_thumb(),
                           "title" => $resource->title);

    }
    $this->assert_equal(
      json_encode(array("status" => "OK", "resources" => $resources)),
      tag_rest::get($request));
  }

  public function tag_rest_delete_tag_test() {
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "A1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);

    $request = (object)array("arguments" => array("T1,P1"));
    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::delete($request));

    $request = (object)array("arguments" => array("T1,P1"));
    $this->assert_equal(json_encode(array("status" => "OK")),
                        tag_rest::get($request));
  }

  public function tag_rest_delete_tagc_from_item_test() {
    $album = $this->_create_album(array("albums", "A1", "T1"));
    $child = $this->_create_album(array("albums", "A1", "T1"), $album);
    $photo = $this->_create_image(array("photos", "P1", "T1"), $child);
    $request = (object)array("arguments" => array("T1,P1"),
                             $photo->relative_url());

    $this->assert_equal(json_encode(array("status" => "OK")), tag_rest::delete($request));

    $request = (object)array("arguments" => explode("/", $photo->relative_url()));
    $this->assert_equal(json_encode(array("status" => "OK", "tags" => array("photos"))),
                        tag_rest::get($request));
  }
}
