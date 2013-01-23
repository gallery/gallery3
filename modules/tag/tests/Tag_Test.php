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
class Tag_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    ORM::factory("tag")->delete_all();
  }

  public function create_tag_test() {
    $album = test::random_album();

    tag::add($album, "tag1");
    $tag = ORM::factory("tag")->where("name", "=", "tag1")->find();
    $this->assert_equal(1, $tag->count);

    // Make sure adding the tag again doesn't increase the count
    tag::add($album, "tag1");
    $this->assert_equal(1, $tag->reload()->count);

    tag::add(test::random_album(), "tag1");
    $this->assert_equal(2, $tag->reload()->count);
  }

  public function rename_merge_tag_test() {
    $album1 = test::random_album();
    $album2 = test::random_album();

    tag::add($album1, "tag1");
    tag::add($album2, "tag2");

    $tag1 = ORM::factory("tag")->where("name", "=", "tag1")->find();
    $tag1->name = "tag2";
    $tag1->save();

    // Tags should be merged; $tag2 should be deleted
    $tag1->reload();

    $this->assert_equal(2, $tag1->count);
    $this->assert_true($tag1->has($album1));
    $this->assert_true($tag1->has($album2));
    $this->assert_equal(1, ORM::factory("tag")->count_all());
  }

  public function rename_merge_tag_with_same_items_test() {
    $album = test::random_album();

    tag::add($album, "tag1");
    tag::add($album, "tag2");

    $tag1 = ORM::factory("tag")->where("name", "=", "tag1")->find();
    $tag1->name = "tag2";
    $tag1->save();

    // Tags should be merged
    $tag1->reload();

    $this->assert_equal(1, $tag1->count);
    $this->assert_true($tag1->has($album));
    $this->assert_equal(1, ORM::factory("tag")->count_all());
  }
}
