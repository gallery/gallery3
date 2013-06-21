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
class Rest_Items_Test extends Unittest_TestCase {
  public function test_get_url() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      RestAPI::url("item", $photo1),
      RestAPI::url("item", $album2)));
    $this->assertEquals(
      array(
        array("url" => RestAPI::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => RestAPI::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => RestAPI::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => RestAPI::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 RestAPI::url("item", $photo2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_album() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      RestAPI::url("item", $photo2),
      RestAPI::url("item", $album1)));
    $request->params->type = "album";
    $this->assertEquals(
      array(
         array("url" => RestAPI::url("item", $album1),
               "entity" => $album1->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => RestAPI::url("item_comments", $album1)),
                 "tags" => array(
                   "url" => RestAPI::url("item_tags", $album1),
                   "members" => array())),
               "members" => array(
                 RestAPI::url("item", $album2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_photo() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      RestAPI::url("item", $photo1),
      RestAPI::url("item", $album2)));
    $request->params->type = "photo";
    $this->assertEquals(
      array(
        array("url" => RestAPI::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $photo1),
                  "members" => array())))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_url_filter_albums_photos() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->urls = json_encode(array(
      RestAPI::url("item", $photo1),
      RestAPI::url("item", $album2)));
    $request->params->type = "photo,album";
    $this->assertEquals(
      array(
        array("url" => RestAPI::url("item", $photo1),
              "entity" => $photo1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $photo1)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $photo1),
                  "members" => array()))),
         array("url" => RestAPI::url("item", $album2),
               "entity" => $album2->as_restful_array(),
               "relationships" => array(
                 "comments" => array(
                   "url" => RestAPI::url("item_comments", $album2)),
                 "tags" => array(
                   "url" => RestAPI::url("item_tags", $album2),
                   "members" => array())),
               "members" => array(
                 RestAPI::url("item", $photo2)))),
      Hook_Rest_Items::get($request));
  }

  public function test_get_ancestors() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $root = Item::root();
    $restful_root = array(
      "url" => RestAPI::url("item", $root),
      "entity" => $root->as_restful_array(),
      "relationships" => RestAPI::relationships("item", $root));
    $restful_root["members"] = array();
    foreach ($root->children->find_all() as $child) {
      $restful_root["members"][] = RestAPI::url("item", $child);
    }

    $request = new stdClass();
    $request->params = new stdClass();
    $request->params->ancestors_for = RestAPI::url("item", $photo2);
    $this->assertEquals(
      array(
        $restful_root,
        array("url" => RestAPI::url("item", $album1),
              "entity" => $album1->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $album1)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $album1),
                  "members" => array())),
              "members" => array(
                RestAPI::url("item", $photo1),
                RestAPI::url("item", $album2)),
            ),
        array("url" => RestAPI::url("item", $album2),
              "entity" => $album2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $album2)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $album2),
                  "members" => array())),
              "members" => array(
                RestAPI::url("item", $photo2))),
        array("url" => RestAPI::url("item", $photo2),
              "entity" => $photo2->as_restful_array(),
              "relationships" => array(
                "comments" => array(
                  "url" => RestAPI::url("item_comments", $photo2)),
                "tags" => array(
                  "url" => RestAPI::url("item_tags", $photo2),
                  "members" => array())))),
      Hook_Rest_Items::get($request));
  }

  public function test_item_resolve() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album = Test::random_album();
    $resolved = RestAPI::resolve(RestAPI::url("item", $album));
    $this->assertEquals($album->id, $resolved->id);
  }

  public function test_item_get_scope() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo2 = Test::random_photo($album2);
    $album1->reload();

    // No scope is the same as "direct"
    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $this->assertEquals(
      array("url" => RestAPI::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => RestAPI::url("item_comments", $album1)),
              "tags" => array(
                "url" => RestAPI::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              RestAPI::url("item", $photo1),
              RestAPI::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));

    $request->url = RestAPI::url("item", $album1);
    $request->params->scope = "direct";
    $this->assertEquals(
      array("url" => RestAPI::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => RestAPI::url("item_comments", $album1)),
              "tags" => array(
                "url" => RestAPI::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              RestAPI::url("item", $photo1),
              RestAPI::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));

    $request->url = RestAPI::url("item", $album1);
    $request->params->scope = "all";
    $this->assertEquals(
      array("url" => RestAPI::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => RestAPI::url("item_comments", $album1)),
              "tags" => array(
                "url" => RestAPI::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              RestAPI::url("item", $photo1),
              RestAPI::url("item", $album2),
              RestAPI::url("item", $photo2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_item_get_children_like() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $photo2 = Test::random_photo_unsaved($album1);
    $photo2->name = "foo.jpg";
    $photo2->save();
    $album1->reload();

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $request->params->name = "foo";
    $this->assertEquals(
      array("url" => RestAPI::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => RestAPI::url("item_comments", $album1)),
              "tags" => array(
                "url" => RestAPI::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              RestAPI::url("item", $photo2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_item_get_children_type() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album1 = Test::random_album();
    $photo1 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $album1->reload();

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $request->params->type = "album";
    $this->assertEquals(
      array("url" => RestAPI::url("item", $album1),
            "entity" => $album1->as_restful_array(),
            "relationships" => array(
              "comments" => array(
                "url" => RestAPI::url("item_comments", $album1)),
              "tags" => array(
                "url" => RestAPI::url("item_tags", $album1),
                "members" => array())),
            "members" => array(
              RestAPI::url("item", $album2)),
            ),
      Hook_Rest_Item::get($request));
  }

  public function test_item_update_album() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->title = "my new title";

    Hook_Rest_Item::put($request);
    $this->assertEquals("my new title", $album1->reload()->title);
  }

  public function test_item_update_album_illegal_value_fails() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
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

  public function test_item_add_album() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "album";
    $request->params->entity->name = "my album";
    $request->params->entity->title = "my album";
    $response = Hook_Rest_Item::post($request);
    $new_album = RestAPI::resolve($response["url"]);

    $this->assertTrue($new_album->is_album());
    $this->assertEquals($album1->id, $new_album->parent_id);
  }

  public function test_item_add_album_illegal_value_fails() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
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


  public function test_item_add_photo() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->type = "photo";
    $request->params->entity->name = "my photo.jpg";
    $request->file = MODPATH . "gallery_unittest/assets/test.jpg";
    $response = Hook_Rest_Item::post($request);
    $new_photo = RestAPI::resolve($response["url"]);

    $this->assertTrue($new_photo->is_photo());
    $this->assertEquals($album1->id, $new_photo->parent_id);
  }

  public function test_item_delete_album() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::allow(Identity::everybody(), "edit", $album1);

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    Hook_Rest_Item::delete($request);

    $album1->reload();
    $this->assertFalse($album1->loaded());
  }

  public function test_item_delete_album_fails_without_permission() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $album1 = Test::random_album();
    Access::deny(Identity::everybody(), "edit", $album1);
    Identity::set_active_user(Identity::guest());

    $request = new stdClass();
    $request->url = RestAPI::url("item", $album1);
    try {
      Hook_Rest_Item::delete($request);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (HTTP_Exception_403 $e) {
      // pass
    }
  }

  public function test_as_restful_array() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();

    $result = $album->as_restful_array();
    $this->assertEquals(RestAPI::url("item", Item::root()), $result["parent"]);
    $this->assertEquals(RestAPI::url("item", $photo), $result["album_cover"]);
    $this->assertTrue(!array_key_exists("parent_id", $result));
    $this->assertTrue(!array_key_exists("album_cover_item_id", $result));
  }

  public function test_as_restful_array_with_edit_bit() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $response = Item::root()->as_restful_array();
    $this->assertTrue($response["can_edit"]);

    Access::deny(Identity::everybody(), "edit", Item::root());
    Identity::set_active_user(Identity::guest());
    $response = Item::root()->as_restful_array();
    $this->assertFalse($response["can_edit"]);
  }

  public function test_as_restful_array_with_add_bit() {
    $this->markTestIncomplete("REST API is currently under re-construction - as_restful_array() no longer in item model");

    $response = Item::root()->as_restful_array();
    $this->assertTrue($response["can_add"]);

    Access::deny(Identity::everybody(), "add", Item::root());
    Identity::set_active_user(Identity::guest());
    $response = Item::root()->as_restful_array();
    $this->assertFalse($response["can_add"]);
  }
}
