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
class Tag_Item_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    try {
      Database::instance()->query("TRUNCATE {tags}");
      Database::instance()->query("TRUNCATE {items_tags}");
    } catch (Exception $e) { }
  }

  public function get_test() {
    $tag = Tag::add(Item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = Rest::url("tag_item", $tag, Item::root());
    $this->assert_equal_array(
      array("url" => Rest::url("tag_item", $tag, Item::root()),
            "entity" => array(
              "tag" => Rest::url("tag", $tag),
              "item" => Rest::url("item", Item::root()))),
      Hook_Rest_TagItem::get($request));
  }

  public function get_with_invalid_url_test() {
    $request = new stdClass();
    $request->url = "bogus";
    try {
      Hook_Rest_TagItem::get($request);
    } catch (Kohana_404_Exception $e) {
      return;  // pass
    }
    $this->assert_true(false, "Shouldn't get here");
  }

  public function delete_test() {
    $tag = Tag::add(Item::root(), "tag1")->reload();

    $request = new stdClass();
    $request->url = Rest::url("tag_item", $tag, Item::root());
    Hook_Rest_TagItem::delete($request);

    $this->assert_false($tag->reload()->has(Item::root()));
  }

  public function resolve_test() {
    $album = test::random_album();
    $tag = Tag::add($album, "tag1")->reload();

    $tuple = Rest::resolve(Rest::url("tag_item", $tag, $album));
    $this->assert_equal_array($tag->as_array(), $tuple[0]->as_array());
    $this->assert_equal_array($album->as_array(), $tuple[1]->as_array());
  }
}
