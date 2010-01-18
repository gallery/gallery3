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
class Gallery_Rest_Helper_Test extends Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER, $_FILES);
    $this->_saved_active_user = identity::active_user();
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER, $_FILES) = $this->_save;
    identity::set_active_user($this->_saved_active_user);
    if (!empty($this->_user)) {
      try {
        $this->_user->delete();
      } catch (Exception $e) { }
    }
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

  private function _create_album($parent=null) {
    $album_name = "rest_album_" . rand();
    if (empty($parent)) {
      $parent = ORM::factory("item", 1);
    }
    return album::create($parent, $album_name, $album_name, $album_name);
  }

  private function _create_image($parent=null) {
    $filename = MODPATH . "gallery/tests/test.jpg";
    $image_name = "rest_image_" . rand();
    if (empty($parent)) {
      $parent = ORM::factory("item", 1);
    }
    return photo::create($parent, $filename, "$image_name.jpg", $image_name);
  }

  public function gallery_rest_get_album_test() {
    $album = $this->_create_album();
    $child = $this->_create_album($album);
    $photo = $this->_create_image($child);
    $child->reload();
    $request = (object)array("arguments" => explode("/", $child->relative_url()));

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "resource" =>
                          array("type" => $child->type,
                                "name" => $child->name,
                                "path" => $child->relative_url(),
                                "parent_path" => $album->relative_url(),
                                "title" => $child->title,
                                "thumb_url" => $child->thumb_url(),
                                "thumb_size" => array("height" => $child->thumb_height,
                                                      "width" => $child->thumb_width),
                                "resize_url" => $child->resize_url(),
                                "resize_size" => array("height" => 0,
                                                       "width" => 0),
                                "url" => $child->file_url(),
                                "size" => array("height" => $child->height,
                                                "width" => $child->width),
                                "description" => $child->description,
                                "slug" => $child->slug,
                                "children" => array(array(
                                   "type" => "photo",
                                   "has_children" => false,
                                   "path" => $photo->relative_url(),
                                   "thumb_url" => $photo->thumb_url(),
                                   "thumb_dimensions" => array(
                                     "width" => (string)$photo->thumb_width,
                                     "height" => (string)$photo->thumb_height),
                                   "has_thumb" => true,
                                   "title" => $photo->title))))),
      gallery_rest::get($request));
  }

  public function gallery_rest_get_photo_test() {
    $child = $this->_create_album();
    $photo = $this->_create_image($child);
    $request = (object)array("arguments" => explode("/", $photo->relative_url()));

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "resource" =>
                        array("type" => $photo->type,
                              "name" => $photo->name,
                              "path" => $photo->relative_url(),
                              "parent_path" => $child->relative_url(),
                              "title" => $photo->title,
                              "thumb_url" => $photo->thumb_url(),
                              "thumb_size" => array("height" => (string)$photo->thumb_height,
                                                    "width" => (string)$photo->thumb_width),
                              "resize_url" => $photo->resize_url(),
                              "resize_size" => array("height" => $photo->resize_height,
                                                       "width" => $photo->resize_width),
                              "url" => $photo->file_url(),
                              "size" => array("height" => (string)$photo->height,
                                              "width" => (string)$photo->width),
                              "description" => $photo->description,
                              "slug" => $photo->slug))),
      gallery_rest::get($request));
  }

  public function gallery_rest_put_album_no_path_test() {
    $request = (object)array("description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    try {
      gallery_rest::put($request);
    } catch (Rest_Exception $e) {
      $this->assert_equal("Bad request", $e->getMessage());
      $this->assert_equal(400, $e->getCode());
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function gallery_rest_put_album_not_found_test() {
    $photo = $this->_create_image();
    $request = (object)array("arguments" => explode("/", $photo->relative_url() . rand()),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    try {
      gallery_rest::put($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function gallery_rest_put_album_no_edit_permission_test() {
    $child = $this->_create_album();
    $this->_create_user();
    $request = (object)array("arguments" => explode("/", $child->relative_url()),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    try {
      gallery_rest::put($request);
    } catch (Kohana_404_Exception $k404) {
    } catch (Exception $e) {
      $this->assert_false(true, $e->__toString());
    }
  }

  public function gallery_rest_put_album_rename_conflict_test() {
    $child = $this->_create_album();
    $sibling = $this->_create_image();
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $child);
    $request = (object)array("arguments" => explode("/", $child->relative_url()),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => $sibling->name);

    $this->assert_equal(
      json_encode(array("status" => "VALIDATE_ERROR",
                        "fields" => array("slug" => "Duplicate Internet address"))),
      gallery_rest::put($request));
  }

  public function gallery_rest_put_album_test() {
    $child = $this->_create_album();
    $sibling = $this->_create_image();
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $child);

    $new_name = "new_album_name" . rand();
    $request = (object)array("arguments" => explode("/", $child->relative_url()),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => $new_name);

    $this->assert_equal(json_encode(array("status" => "OK")), gallery_rest::put($request));
    $child->reload();
    $this->assert_equal("Updated description", $child->description);
    $this->assert_equal("Updated Title", $child->title);
    $this->assert_equal($new_name,  $child->name);
  }

  public function gallery_rest_put_photo_test() {
    $child = $this->_create_album();
    $photo = $this->_create_image($child);
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $child);

    $request = (object)array("arguments" => explode("/", $photo->relative_url()),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "OK")), gallery_rest::put($request));
    $photo->reload();
    $this->assert_equal("Updated description", $photo->description);
    $this->assert_equal("Updated Title", $photo->title);
    $this->assert_equal("new name",  $photo->name);
  }

  public function gallery_rest_delete_album_test() {
    $album = $this->_create_album();
    $child = $this->_create_album($album);
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $album);

    $request = (object)array("arguments" => explode("/", $child->relative_url()));

    $this->assert_equal(json_encode(array("status" => "OK",
                                          "resource" => array(
                                            "parent_path" => $album->relative_url()))),
                        gallery_rest::delete($request));
    $child->reload();
    $this->assert_false($child->loaded());
  }

  public function gallery_rest_delete_photo_test() {
    $album = $this->_create_album();
    $photo = $this->_create_image($album);
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $album);

    $request = (object)array("arguments" => explode("/", $photo->relative_url()));

    $this->assert_equal(json_encode(array("status" => "OK",
                                          "resource" => array(
                                            "parent_path" => $album->relative_url()))),
                        gallery_rest::delete($request));
    $photo->reload();
    $this->assert_false($photo->loaded());
  }

  public function gallery_rest_post_album_test() {
    $album = $this->_create_album();
    $this->_create_user();
    access::allow(identity::registered_users(), "edit", $album);

    $new_path = $album->relative_url() . "/new%20child";
    $request = (object)array("arguments" => explode("/", $new_path));

    $this->assert_equal(json_encode(array("status" => "OK", "path" => $new_path)),
                        gallery_rest::post($request));
    $album = ORM::factory("item")
      ->where("relative_url_cache", "=", $new_path)
      ->find();
    $this->assert_true($album->loaded());
    $this->assert_equal("new child", $album->slug);
  }
}
