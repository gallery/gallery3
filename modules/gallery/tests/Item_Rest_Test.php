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
class Item_Rest_Test extends Unittest_TestCase {
  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_resolve() {
    $album = Test::random_album();
    $resolved = Rest::resolve(Rest::url("item", $album));
    $this->assertEquals($album->id, $resolved->id);
  }

  public function test_get_scope() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();

    // No scope is the same as "direct"
    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $this->assertEquals(
      array("url" => Rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => Rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => Rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              Rest::url("item", $photo1),
              Rest::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));

    $request->url = Rest::url("item", $album1);
    $request->params->scope = "direct";
    $this->assertEquals(
      array("url" => Rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => Rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => Rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              Rest::url("item", $photo1),
              Rest::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));

    $request->url = Rest::url("item", $album1);
    $request->params->scope = "all";
    $this->assertEquals(
      array("url" => Rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => Rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => Rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              Rest::url("item", $photo1),
              Rest::url("item", $album2),
              Rest::url("item", $photo2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_get_children_like() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $photo2 = Test::random_photo_unsaved($album1);
    $photo2->name = "foo.jpg";
    $photo2->save();
    $album1->reload();

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->name = "foo";
    $this->assertEquals(
      array("url" => Rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => Rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => Rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              Rest::url("item", $photo2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_get_children_type() {
    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $album1->reload();

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->type = "album";
    $this->assertEquals(
      array("url" => Rest::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => Rest::url("item_comments", $album1)),
              "tags" => array(
                "url" => Rest::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              Rest::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_update_album() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->title = "my new title";

    Hook_Rest_Item::put($request);
    $this->assertEquals("my new title", $album1->reload()->title);
  }

  public function test_update_album_illegal_value_fails() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->title = "my new title";
    $request->params->entity->slug = "not url safe";

    try {
      Hook_Rest_Item::put($request);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("not_url_safe", $errors["slug"][0]);
    }
  }

  public function test_add_album() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "album";
    $request->params->entity->name = "my album";
    $request->params->entity->title = "my album";
    $response = Hook_Rest_Item::post($request);
    $new_album = Rest::resolve($response["url"]);

    $this->assertTrue($new_album->is_album());
    $this->assertEquals($album1->id, $new_album->parent_id);
  }

  public function test_add_album_illegal_value_fails() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "album";
    $request->params->entity->name = "my album";
    $request->params->entity->title = "my album";
    $request->params->entity->slug = "not url safe";

    try {
      Hook_Rest_Item::post($request);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("not_url_safe", $errors["slug"][0]);
    }
  }


  public function test_add_photo() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "photo";
    $request->params->entity->name = "my photo.jpg";
    $request->file = MODPATH . "gallery_unittest/assets/test.jpg";
    $response = Hook_Rest_Item::post($request);
    $new_photo = Rest::resolve($response["url"]);

    $this->assertTrue($new_photo->is_photo());
    $this->assertEquals($album1->id, $new_photo->parent_id);
  }

  public function test_delete_album() {
    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    Hook_Rest_Item::delete($request);

    $album1->reload();
    $this->assertFalse($album1->loaded());
  }

  public function test_delete_album_fails_without_permission() {
    $album1 = Test::random_album();
    Access::deny(Identity::everybody(), "edit", $album1);
    Identity::set_active_user(Identity::guest());

    $request = new stdClass();
    $request->url = Rest::url("item", $album1);
    try {
      Hook_Rest_Item::delete($request);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (HTTP_Exception_403 $e) {
      // pass
    }
  }
}
