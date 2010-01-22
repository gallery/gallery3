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
class Gallery_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->_save = array($_GET, $_POST, $_SERVER, $_FILES);
  }

  public function teardown() {
    list($_GET, $_POST, $_SERVER, $_FILES) = $this->_save;
  }

  public function resolve_test() {
    $album = test::random_album();
    $resolved = rest::resolve(rest::url("gallery", $album));
    $this->assert_equal($album->id, $resolved->id);
  }

  public function get_scope_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();

    // No scope is the same as "direct"
    $request->url = rest::url("gallery", $album1);
    $request->params = new stdClass();
    $this->assert_equal_array(
      array("resource" => $album1->as_array(),
            "members" => array(
              rest::url("gallery", $photo1),
              rest::url("gallery", $album2))),
      gallery_rest::get($request));

    $request->url = rest::url("gallery", $album1);
    $request->params->scope = "direct";
    $this->assert_equal_array(
      array("resource" => $album1->as_array(),
            "members" => array(
              rest::url("gallery", $photo1),
              rest::url("gallery", $album2))),
      gallery_rest::get($request));

    $request->url = rest::url("gallery", $album1);
    $request->params->scope = "all";
    $this->assert_equal_array(
      array("resource" => $album1->as_array(),
            "members" => array(
              rest::url("gallery", $photo1),
              rest::url("gallery", $album2),
              rest::url("gallery", $photo2))),
      gallery_rest::get($request));
  }

  public function get_children_like_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $photo2 = test::random_photo_unsaved($album1);
    $photo2->name = "foo.jpg";
    $photo2->save();
    $album1->reload();

    $request->url = rest::url("gallery", $album1);
    $request->params->name = "foo";
    $this->assert_equal_array(
      array("resource" => $album1->as_array(),
            "members" => array(
              rest::url("gallery", $photo2))),
      gallery_rest::get($request));
  }

  public function get_children_type_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $album1->reload();

    $request->url = rest::url("gallery", $album1);
    $request->params->type = "album";
    $this->assert_equal_array(
      array("resource" => $album1->as_array(),
            "members" => array(
              rest::url("gallery", $album2))),
      gallery_rest::get($request));
  }

  public function update_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    $request->params->title = "my new title";

    $this->assert_equal_array(
      array("url" => rest::url("gallery", $album1)),
      gallery_rest::put($request));
    $this->assert_equal("my new title", $album1->reload()->title);
  }

  public function update_album_illegal_value_fails_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    $request->params->title = "my new title";
    $request->params->slug = "not url safe";

    try {
      gallery_rest::put($request);
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("slug" => "not_url_safe"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function add_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    $request->params->type = "album";
    $request->params->name = "my album";
    $request->params->title = "my album";
    $response = gallery_rest::post($request);
    $new_album = rest::resolve($response["url"]);

    $this->assert_true($new_album->is_album());
    $this->assert_equal($album1->id, $new_album->parent_id);
  }

  public function add_album_illegal_value_fails_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    $request->params->type = "album";
    $request->params->name = "my album";
    $request->params->title = "my album";
    $request->params->slug = "not url safe";

    try {
      gallery_rest::post($request);
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("slug" => "not_url_safe"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }


  public function add_photo_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    $request->params->type = "photo";
    $request->params->name = "my photo.jpg";
    $request->file = MODPATH . "gallery/tests/test.jpg";
    $response = gallery_rest::post($request);
    $new_photo = rest::resolve($response["url"]);

    $this->assert_true($new_photo->is_photo());
    $this->assert_equal($album1->id, $new_photo->parent_id);
  }

  public function delete_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    gallery_rest::delete($request);

    $album1->reload();
    $this->assert_false($album1->loaded());
  }

  public function delete_album_fails_without_permission_test() {
    $album1 = test::random_album();
    access::deny(identity::everybody(), "edit", $album1);

    $request->url = rest::url("gallery", $album1);
    try {
      gallery_rest::delete($request);
    } catch (Exception $e) {
      $this->assert_equal("@todo FORBIDDEN", $e->getMessage());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }
}
