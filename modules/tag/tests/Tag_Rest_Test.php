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
class Tag_Rest_Test extends Unittest_TestCase {
  public function setup() {
    parent::setup();
    try {
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {tags}");
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {items_tags}");
    } catch (Exception $e) { }
  }

  public function test_get() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $tag = Tag::add(Item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = RestAPI::url("tag", $tag);
    $this->assertEquals(
      array("url" => RestAPI::url("tag", $tag),
            "entity" => $tag->as_array(),
            "relationships" => array(
              "items" => array(
                "url" => RestAPI::url("tag_items", $tag),
                "members" => array(
                  RestAPI::url("tag_item", $tag, Item::root()))))),
      Hook_Rest_Tag::get($request));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_with_invalid_url() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $request = new stdClass();
    $request->url = "bogus";
    Hook_Rest_Tag::get($request);
  }

  public function test_get_with_no_relationships() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $tag = Test::random_tag();

    $request = new stdClass();
    $request->url = RestAPI::url("tag", $tag);
    $this->assertEquals(
      array("url" => RestAPI::url("tag", $tag),
            "entity" => $tag->as_array(),
            "relationships" => array(
              "items" => array(
                "url" => RestAPI::url("tag_items", $tag),
                "members" => array()))),
      Hook_Rest_Tag::get($request));
  }

  public function test_put() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $tag = Test::random_tag();
    $request = new stdClass();
    $request->url = RestAPI::url("tag", $tag);
    $request->params = new stdClass();
    $request->params->entity = new stdClass();
    $request->params->entity->name = "new name";

    Hook_Rest_Tag::put($request);
    $this->assertEquals("new name", $tag->reload()->name);
  }

  public function test_delete_tag() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $tag = Test::random_tag();
    $request = new stdClass();
    $request->url = RestAPI::url("tag", $tag);
    Hook_Rest_Tag::delete($request);

    $this->assertFalse($tag->reload()->loaded());
  }

  public function test_resolve() {
    $this->markTestIncomplete("REST API is currently under re-construction...");

    $tag = Test::random_tag();

    $this->assertEquals(
      $tag->as_array(),
      RestAPI::resolve(RestAPI::url("tag", $tag))->as_array());
  }
}
