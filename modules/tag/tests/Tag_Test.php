<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Tag_Test extends Unit_Test_Case {
  public function create_tag_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);
    $tag1 = "tag1";

    tag::add($album, $tag1);
    $tag = ORM::factory("tag")->where("name", $tag1)->find();
    $this->assert_true(1, $tag->count);

    // Make sure adding the tag again doesn't increase the count
    tag::add($album, $tag1);
    $tag = ORM::factory("tag")->where("name", $tag1)->find();
    $this->assert_true(1, $tag->count);

    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);
    tag::add($album, $tag1);
    $tag = ORM::factory("tag")->where("name", $tag1)->find();
    $this->assert_true(2, $tag->count);
  }

  public function load_buckets_test() {

    $tags = array();
    for ($tag_count = 1; $tag_count <= 8; $tag_count++) {
      $rand = rand();
      $album = album::create(1, $rand, $rand, $rand);
      for ($idx = 0; $idx < $tag_count; $idx++) {
        tag::add($album, "tag$idx");
      }
    }

    $tag_list = tag::load_buckets();
    Kohana::log("debug", print_r($tag_list, true));
    $expected_tag_list = array(
      array("name" => "tag0", "count" => 8, "class" => 5),
      array("name" => "tag1", "count" => 9, "class" => 6),
      array("name" => "tag2", "count" => 6, "class" => 4),
      array("name" => "tag3", "count" => 5, "class" => 3),
      array("name" => "tag4", "count" => 4, "class" => 2),
      array("name" => "tag5", "count" => 3, "class" => 1),
      array("name" => "tag6", "count" => 2, "class" => 0),
      array("name" => "tag7", "count" => 1, "class" => 0)
    );
    $this->assert_equal($expected_tag_list, $tag_list, "incorrect non filtered tag list");
    
    $tag_list = tag::load_buckets(2);
    Kohana::log("debug", print_r($tag_list, true));
    $expected_tag_list = array(
      array("name" => "tag0", "count" => 8, "class" => 5),
      array("name" => "tag1", "count" => 9, "class" => 6),
      array("name" => "tag2", "count" => 6, "class" => 4),
      array("name" => "tag3", "count" => 5, "class" => 3),
      array("name" => "tag4", "count" => 4, "class" => 2),
      array("name" => "tag5", "count" => 3, "class" => 1),
      array("name" => "tag6", "count" => 2, "class" => 0)
    );
    $this->assert_equal($expected_tag_list, $tag_list, "incorrect filtered tag list");
  }
}