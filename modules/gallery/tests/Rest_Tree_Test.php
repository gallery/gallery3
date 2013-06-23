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
class Rest_Tree_Test extends Unittest_TestCase {
  public function test_get_response() {
    Identity::set_active_user(Identity::admin_user());

    $album1 = Test::random_album();
    $photo2 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo3 = Test::random_photo($album2);

    $rest = Rest::factory("Tree", $album1->id, array("fields" => "name,type"));

    $expected = array(
      "url" => URL::abs_site("rest/tree/{$album1->id}?fields=name%2Ctype"),
      "entity" => array(
        0 => array(
          "url" => URL::abs_site("rest/items/{$album1->id}"),
          "entity" => array(
            "name" => $album1->name,
            "type" => "album")),
        1 => array(
          "url" => URL::abs_site("rest/items/{$photo2->id}"),
          "entity" => array(
            "name" => $photo2->name,
            "type" => "photo")),
        2 => array(
          "url" => URL::abs_site("rest/items/{$album2->id}"),
          "entity" => array(
            "name" => $album2->name,
            "type" => "album")),
        3 => array(
          "url" => URL::abs_site("rest/items/{$photo3->id}"),
          "entity" => array(
            "name" => $photo3->name,
            "type" => "photo"))),
      "members" => array());

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_entity() {
    Identity::set_active_user(Identity::admin_user());

    $album1 = Test::random_album();
    $photo2 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo3 = Test::random_photo($album2);

    // Get with "depth=1" query parameter - no photo3.
    $rest = Rest::factory("Tree", $album1->id, array("depth" => 1));
    $entity = $rest->get_entity();
    $this->assertEquals($album1->id, Arr::path($entity, "0.entity.id"));
    $this->assertEquals($photo2->id, Arr::path($entity, "1.entity.id"));
    $this->assertEquals($album2->id, Arr::path($entity, "2.entity.id"));
    $this->assertEquals(null,        Arr::path($entity, "3.entity.id"));

    // Get with "type=album" query parameter - no photo2 or photo3.
    $rest = Rest::factory("Tree", $album1->id, array("type" => array("album")));
    $entity = $rest->get_entity();
    $this->assertEquals($album1->id, Arr::path($entity, "0.entity.id"));
    $this->assertEquals($album2->id, Arr::path($entity, "1.entity.id"));
    $this->assertEquals(null,        Arr::path($entity, "2.entity.id"));
    $this->assertEquals(null,        Arr::path($entity, "3.entity.id"));
  }

  public function test_get_entity_with_fields_params() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();

    // Get with no query param - should include all fields
    $rest = Rest::factory("Tree", $album->id);
    $entity = $rest->get_entity();
    $this->assertEquals($album->name, Arr::path($entity, "0.entity.name"));
    $this->assertEquals($album->type, Arr::path($entity, "0.entity.type"));
    $this->assertEquals($album->slug, Arr::path($entity, "0.entity.slug"));

    // Get with "fields=name,type" query param - should only include name and type
    $rest = Rest::factory("Tree", $album->id, array("fields" => "name,type"));
    $entity = $rest->get_entity();
    $this->assertEquals($album->name, Arr::path($entity, "0.entity.name"));
    $this->assertEquals($album->type, Arr::path($entity, "0.entity.type"));
    $this->assertEquals(null,         Arr::path($entity, "0.entity.slug"));
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_get_entity_with_non_album() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_photo();

    Rest::factory("Tree", $item->id)->get_entity();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_with_invalid_item() {
    Identity::set_active_user(Identity::admin_user());
    Access::allow(Identity::everybody(), "view", Item::root());  // ensure it's not an access error
    $item = Test::random_album();

    $id = $item->id;
    $item->delete();

    Rest::factory("Tree", $id)->get_entity();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_without_view_access() {
    Identity::set_active_user(Identity::guest());
    Access::deny(Identity::everybody(), "view", Item::root());
    $item = Test::random_album();

    Rest::factory("Tree", $item->id)->get_entity();
  }

  public function test_get_members() {
    Identity::set_active_user(Identity::admin_user());

    $album1 = Test::random_album();
    $album2 = Test::random_album($album1);
    $photo3 = Test::random_photo($album2);
    $album3a = Test::random_album($album2);
    $album3b = Test::random_album($album2);
    $album4 = Test::random_album($album3);

    // Get with no query parameters - empty list.
    $rest = Rest::factory("Tree", $album1->id);
    $expected = array();
    $this->assertEquals($expected, $rest->get_members());

    // Get with "depth=2" query parameter - album3a and album3b with params.
    $rest = Rest::factory("Tree", $album1->id, array("depth" => 2));
    $expected = array(
      0 => Rest::factory("Tree", $album3a->id, array("depth" => 2)),
      1 => Rest::factory("Tree", $album3b->id, array("depth" => 2)));
    $this->assertEquals($expected, $rest->get_members());

    // Get with "depth=2" and "fields=name" query parameter - album3a and album3b with params.
    $rest = Rest::factory("Tree", $album1->id, array("depth" => 2, "fields" => "name"));
    $expected = array(
      0 => Rest::factory("Tree", $album3a->id, array("depth" => 2, "fields" => "name")),
      1 => Rest::factory("Tree", $album3b->id, array("depth" => 2, "fields" => "name")));
    $this->assertEquals($expected, $rest->get_members());
  }

  public function test_expand_members_param_ignored() {
    $rest = Rest::factory("Tree", Item::root()->id);
    $this->assertFalse(isset($rest->params["expand_members"]));

    $rest = Rest::factory("Tree", Item::root()->id, array("expand_members" => false));
    $this->assertFalse(isset($rest->params["expand_members"]));

    $rest = Rest::factory("Tree", Item::root()->id, array("expand_members" => true));
    $this->assertFalse(isset($rest->params["expand_members"]));
  }
}
