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
    $this->_save = array($_GET, $_POST, $_SERVER);
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

    identity::set_active_user($this->_user);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER) = $this->_save;
    identity::set_active_user($this->_saved_active_user);

    try {
      if (!empty($this->_user)) {
        $this->_user->delete();
      }
      if (!empty($this->_album)) {
        $this->_album->delete();
      }
    } catch (Exception $e) { }
  }

  public function gallery_rest_get_album_test() {
    $request = (object)array("path" => $this->_child->relative_path());

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "album" => array("path" => $this->_child->relative_path(),
                                         "title" => $this->_child->title,
                                         "thumb_url" => $this->_child->thumb_url(),
                                         "url" => $this->_child->abs_url(),
                                         "description" => $this->_child->description,
                                         "internet_address" => $this->_child->slug,
                                         "children" => array(array(
                                           "type" => "photo",
                                           "has_children" => false,
                                           "path" => $this->_photo->relative_path(),
                                           "title" => $this->_photo->title))))),
      gallery_rest::get($request));
  }

  public function gallery_rest_get_photo_test() {
    $request = (object)array("path" => $this->_photo->relative_path());

    $this->assert_equal(
      json_encode(array("status" => "OK",
                        "photo" => array("path" => $this->_photo->relative_path(),
                                         "title" => $this->_photo->title,
                                         "thumb_url" => $this->_photo->thumb_url(),
                                         "url" => $this->_photo->abs_url(),
                                         "description" => $this->_photo->description,
                                         "internet_address" => $this->_photo->slug))),
      gallery_rest::get($request));
  }
}
