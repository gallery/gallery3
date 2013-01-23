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
class Item_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

  public function resolve_test() {
    $album = test::random_album();
    $resolved = rest::resolve(rest::url("item", $album));
    $this->assert_equal($album->id, $resolved->id);
  }

  public function get_scope_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $photo2 = test::random_photo($album2);
    $album1->reload();

    // No scope is the same as "direct"
    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $this->assert_equal_array(
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
      item_rest::get($request));

    $request->url = rest::url("item", $album1);
    $request->params->scope = "direct";
    $this->assert_equal_array(
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
      item_rest::get($request));

    $request->url = rest::url("item", $album1);
    $request->params->scope = "all";
    $this->assert_equal_array(
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
              rest::url("item", $album2),
              rest::url("item", $photo2)),
            ),
      item_rest::get($request));
  }

  public function get_children_like_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $photo2 = test::random_photo_unsaved($album1);
    $photo2->name = "foo.jpg";
    $photo2->save();
    $album1->reload();

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->name = "foo";
    $this->assert_equal_array(
      array("url" => rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              rest::url("item", $photo2)),
            ),
      item_rest::get($request));
  }

  public function get_children_type_test() {
    $album1 = test::random_album();
    $photo1 = test::random_photo($album1);
    $album2 = test::random_album($album1);
    $album1->reload();

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->type = "album";
    $this->assert_equal_array(
      array("url" => rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              rest::url("item", $album2)),
            ),
      item_rest::get($request));
  }

  public function update_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->title = "my new title";

    item_rest::put($request);
    $this->assert_equal("my new title", $album1->reload()->title);
  }

  public function update_album_illegal_value_fails_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->title = "my new title";
    $request->params->entity->slug = "not url safe";

    try {
      item_rest::put($request);
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("slug" => "not_url_safe"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function add_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "album";
    $request->params->entity->name = "my album";
    $request->params->entity->title = "my album";
    $response = item_rest::post($request);
    $new_album = rest::resolve($response["url"]);

    $this->assert_true($new_album->is_album());
    $this->assert_equal($album1->id, $new_album->parent_id);
  }

  public function add_album_illegal_value_fails_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "album";
    $request->params->entity->name = "my album";
    $request->params->entity->title = "my album";
    $request->params->entity->slug = "not url safe";

    try {
      item_rest::post($request);
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("slug" => "not_url_safe"), $e->validation->errors());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }


  public function add_photo_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "photo";
    $request->params->entity->name = "my photo.jpg";
    $request->file = MODPATH . "gallery/tests/test.jpg";
    $response = item_rest::post($request);
    $new_photo = rest::resolve($response["url"]);

    $this->assert_true($new_photo->is_photo());
    $this->assert_equal($album1->id, $new_photo->parent_id);
  }

  public function delete_album_test() {
    $album1 = test::random_album();
    access::allow(identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    item_rest::delete($request);

    $album1->reload();
    $this->assert_false($album1->loaded());
  }

  public function delete_album_fails_without_permission_test() {
    $album1 = test::random_album();
    access::deny(identity::everybody(), "edit", $album1);
    identity::set_active_user(identity::guest());

    $request = new stdClass();
    $request->url = rest::url("item", $album1);
    try {
      item_rest::delete($request);
    } catch (Exception $e) {
      $this->assert_equal("@todo FORBIDDEN", $e->getMessage());
      return;
    }
    $this->assert_true(false, "Shouldn't get here");
  }
}
