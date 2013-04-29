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
class Tag_Test extends Unittest_Testcase {
  public function setup() {
    parent::setup();
    // We use ORM instead of DB to delete tags so the pivot table is cleared, too.
    foreach (ORM::factory("Tag")->find_all() as $tag) {
      $tag->delete();
    }
  }

  public function test_create_tag() {
    $album = Test::random_album();

    Tag::add($album, "tag1");
    $tag = ORM::factory("Tag")->where("name", "=", "tag1")->find();
    $this->assertEquals(1, $tag->count);

    // Make sure adding the tag again doesn't increase the count
    Tag::add($album, "tag1");
    $this->assertEquals(1, $tag->reload()->count);

    Tag::add(Test::random_album(), "tag1");
    $this->assertEquals(2, $tag->reload()->count);
  }

  public function test_rename_merge_tag() {
    $album1 = Test::random_album();
    $album2 = Test::random_album();

    Tag::add($album1, "tag1");
    Tag::add($album2, "tag2");

    $tag1 = ORM::factory("Tag")->where("name", "=", "tag1")->find();
    $tag1->name = "tag2";
    $tag1->save();

    // Tags should be merged; $tag2 should be deleted
    $tag1->reload();

    $this->assertEquals(2, $tag1->count);
    $this->assertTrue($tag1->has("Item", $album1));
    $this->assertTrue($tag1->has("Item", $album2));
    $this->assertEquals(1, ORM::factory("Tag")->count_all());
  }

  public function test_rename_merge_tag_with_same_items() {
    $album = Test::random_album();

    Tag::add($album, "tag1");
    Tag::add($album, "tag2");

    $tag1 = ORM::factory("Tag")->where("name", "=", "tag1")->find();
    $tag1->name = "tag2";
    $tag1->save();

    // Tags should be merged
    $tag1->reload();

    $this->assertEquals(1, $tag1->count);
    $this->assertTrue($tag1->has("Item", $album));
    $this->assertEquals(1, ORM::factory("Tag")->count_all());
  }
}
