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
class Tag_Model_Test extends Unittest_TestCase {
  public function test_rename_merge_tag() {
    $name1 = Test::random_name();
    $name2 = Test::random_name();

    $album1 = Test::random_album();
    $album2 = Test::random_album();

    Tag::add($album1, $name1);
    Tag::add($album2, $name2);

    $tag1 = ORM::factory("Tag")->where("name", "=", $name1)->find();
    $tag2 = ORM::factory("Tag")->where("name", "=", $name2)->find();

    // Rename tag1 using tag2's name, effectively merging them
    $tag1->name = $name2;
    $tag1->save();

    $tag1->reload();
    $tag2->reload();

    // Tags should be merged; $tag2 should be deleted
    $this->assertEquals(2, $tag1->count);
    $this->assertTrue($tag1->has("items", $album1));
    $this->assertTrue($tag1->has("items", $album2));
    $this->assertFalse($tag2->loaded());
  }

  public function test_rename_merge_tag_with_same_items() {
    $name1 = Test::random_name();
    $name2 = Test::random_name();

    $album = Test::random_album();

    Tag::add($album, $name1);
    Tag::add($album, $name2);

    $tag1 = ORM::factory("Tag")->where("name", "=", $name1)->find();
    $tag2 = ORM::factory("Tag")->where("name", "=", $name2)->find();

    // Rename tag1 using tag2's name, effectively merging them
    $tag1->name = $name2;
    $tag1->save();

    $tag1->reload();
    $tag2->reload();

    // Tags should be merged; $tag2 should be deleted
    $this->assertEquals(1, $tag1->count);
    $this->assertTrue($tag1->has("items", $album));
    $this->assertFalse($tag2->loaded());
  }
}
