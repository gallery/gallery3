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

    $this->_user = identity::create_user("access_test", "Access Test", "password");
    $key = ORM::factory("user_access_token");
    $this->_access_key = $key->access_key = md5($this->_user->name . rand());
    $key->user_id = $this->_user->id;
    $key->save();

    $root = ORM::factory("item", 1);
    $this->_album = album::create($root, "album", "Test Album", rand());
    $this->_child = album::create($this->_album, "child", "Test Child Album", rand());

    $filename = MODPATH . "gallery/tests/test.jpg";
    $rand = rand();
    $this->_photo = photo::create($this->_child, $filename, "$rand.jpg", $rand);

    $filename = MODPATH . "gallery/tests/test.jpg";
    $rand = rand();
    $this->_sibling = photo::create($this->_album, $filename, "$rand.jpg", $rand);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER, $_FILES) = $this->_save;
    identity::set_active_user($this->_saved_active_user);

    try {
      if (!empty($this->_user)) {
        $this->_user->delete();
      }
      if (!empty($this->_album)) {
        //$this->_album->delete();
      }
    } catch (Exception $e) { }
  }

  public function gallery_rest_get_album_test() {
    $request = (object)array("path" => $this->_child->relative_url());

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "resource" =>
                          array("type" => $this->_child->type,
                                "name" => $this->_child->name,
                                "path" => $this->_child->relative_url(),
                                "parent_path" => $this->_album->relative_url(),
                                "title" => $this->_child->title,
                                "thumb_url" => $this->_child->thumb_url(),
                                "thumb_size" => array("height" => $this->_child->thumb_height,
                                                      "width" => $this->_child->thumb_width),
                                "resize_url" => $this->_child->resize_url(),
                                "resize_size" => array("height" => 0,
                                                       "width" => 0),
                                "url" => $this->_child->file_url(),
                                "size" => array("height" => $this->_child->height,
                                                "width" => $this->_child->width),
                                "description" => $this->_child->description,
                                "slug" => $this->_child->slug,
                                "children" => array(array(
                                   "type" => "photo",
                                   "has_children" => false,
                                   "path" => $this->_photo->relative_url(),
                                   "thumb_url" => $this->_photo->thumb_url(),
                                   "thumb_dimensions" => array(
                                     "width" => $this->_photo->thumb_width,
                                     "height" => $this->_photo->thumb_height),
                                   "has_thumb" => true,
                                   "title" => $this->_photo->title))))),
      gallery_rest::get($request));
  }

  public function gallery_rest_get_photo_test() {
    $request = (object)array("path" => $this->_photo->relative_url());

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "resource" =>
                        array("type" => $this->_photo->type,
                              "name" => $this->_photo->name,
                              "path" => $this->_photo->relative_url(),
                              "parent_path" => $this->_child->relative_url(),
                              "title" => $this->_photo->title,
                              "thumb_url" => $this->_photo->thumb_url(),
                              "thumb_size" => array("height" => $this->_photo->thumb_height,
                                                    "width" => $this->_photo->thumb_width),
                              "resize_url" => $this->_photo->resize_url(),
                              "resize_size" => array("height" => $this->_photo->resize_height,
                                                       "width" => $this->_photo->resize_width),
                              "url" => $this->_photo->file_url(),
                              "size" => array("height" => $this->_photo->height,
                                              "width" => $this->_photo->width),
                              "description" => $this->_photo->description,
                              "slug" => $this->_photo->slug))),
      gallery_rest::get($request));
  }

  public function gallery_rest_put_album_no_path_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);

    identity::set_active_user($this->_user);
    $request = (object)array("description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "ERROR", "message" => "Invalid request")),
                        gallery_rest::put($request));
  }

  public function gallery_rest_put_album_not_found_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);

    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_child->relative_url() . rand(),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "ERROR", "message" => "Resource not found")),
                        gallery_rest::put($request));
  }

  public function gallery_rest_put_album_no_edit_permission_test() {
    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_child->relative_url(),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "ERROR", "message" => "Resource not found")),
                        gallery_rest::put($request));
  }

  public function gallery_rest_put_album_rename_conflict_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);
    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_child->relative_url(),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => $this->_sibling->name);

    $this->assert_equal(
      json_encode(array("status" => "VALIDATE_ERROR",
                        "fields" => array("slug" => "Duplicate Internet Address"))),
      gallery_rest::put($request));
  }

  public function gallery_rest_put_album_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);

    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_child->relative_url(),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "OK")), gallery_rest::put($request));
    $this->_child->reload();
    $this->assert_equal("Updated description", $this->_child->description);
    $this->assert_equal("Updated Title", $this->_child->title);
    $this->assert_equal("new name",  $this->_child->name);
  }

  public function gallery_rest_put_photo_test() {
    access::allow(identity::registered_users(), "edit", $this->_child);

    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_photo->relative_url(),
                             "description" => "Updated description",
                             "title" => "Updated Title",
                             "name" => "new name");

    $this->assert_equal(json_encode(array("status" => "OK")), gallery_rest::put($request));
    $this->_photo->reload();
    $this->assert_equal("Updated description", $this->_photo->description);
    $this->assert_equal("Updated Title", $this->_photo->title);
    $this->assert_equal("new name",  $this->_photo->name);
  }

  public function gallery_rest_delete_album_test() {
    access::allow(identity::registered_users(), "edit", $this->_album);

    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_child->relative_url());

    $this->assert_equal(json_encode(array("status" => "OK",
                                          "resource" => array(
                                            "parent_path" => $this->_album->relative_url()))),
                        gallery_rest::delete($request));
    $this->_child->reload();
    $this->assert_false($this->_child->loaded);
  }

  public function gallery_rest_delete_photo_test() {
    access::allow(identity::registered_users(), "edit", $this->_album);

    identity::set_active_user($this->_user);
    $request = (object)array("path" => $this->_sibling->relative_url());

    $this->assert_equal(json_encode(array("status" => "OK",
                                          "resource" => array(
                                            "parent_path" => $this->_album->relative_url()))),
                        gallery_rest::delete($request));
    $this->_sibling->reload();
    $this->assert_false($this->_sibling->loaded);
  }

  public function gallery_rest_post_album_test() {
    access::allow(identity::registered_users(), "edit", $this->_album);

    $new_path = $this->_child->relative_url() . "/new%20child";
    identity::set_active_user($this->_user);
    $request = (object)array("path" => $new_path);

    $this->assert_equal(json_encode(array("status" => "OK", "path" => $new_path)),
                        gallery_rest::post($request));
    $album = ORM::factory("item")
      ->where("relative_url_cache", $new_path)
      ->find();
    $this->assert_true($album->loaded);
    $this->assert_equal("new child", $album->slug);
  }
}
