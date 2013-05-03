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
class Tag_Item_Rest_Helper_Test extends Unittest_TestCase {
  public function setup() {
    parent::setup();
    try {
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {tags}");
      Database::instance()->query(Database::TRUNCATE, "TRUNCATE {items_tags}");
    } catch (Exception $e) { }
  }

  public function test_get() {
    $tag = Tag::add(Item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = Rest::url("tag_item", $tag, Item::root());
    $this->assertEquals(
      array("url" => Rest::url("tag_item", $tag, Item::root()),
            "entity" => array(
              "tag" => Rest::url("tag", $tag),
              "item" => Rest::url("item", Item::root()))),
      Hook_Rest_TagItem::get($request));
  }

  public function test_get_with_invalid_url() {
    $request = new stdClass();
    $request->url = "bogus";
    try {
      Hook_Rest_TagItem::get($request);
    } catch (HTTP_Exception_404 $e) {
      return;  // pass
    }
    $this->assertTrue(false, "Shouldn't get here");
  }

  public function test_delete() {
    $tag = Tag::add(Item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = Rest::url("tag_item", $tag, Item::root());
    Hook_Rest_TagItem::delete($request);

    $this->assertFalse($tag->reload()->has("items", Item::root()));
  }

  public function test_resolve() {
    $album = Test::random_album();
    $tag = Tag::add($album, "tag1")->reload();

    $tuple = Rest::resolve(Rest::url("tag_item", $tag, $album));
    $this->assertEquals($tag->as_array(), $tuple[0]->as_array());
    $this->assertEquals($album->as_array(), $tuple[1]->as_array());
  }
}
