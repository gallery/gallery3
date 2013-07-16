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
class Tags_Controller_Test extends Unittest_TestCase {
  public function test_redirect_30x_url() {
    $name = Test::random_name();
    $album = Test::random_album();
    Tag::add($album, $name);
    $tag = ORM::factory("Tag")->where("name", "=", $name)->find();

    $urls = array(
      "tag/{$tag->id}/{$tag->name}",  // Correct Gallery 3.0.x tag URL
      "tag/{$tag->id}",               // Malformed Gallery 3.0.x tag URL
      "tag_name/{$tag->name}",        // Correct Gallery 3.0.x tag_name URL
    );

    $canonical_url = $tag->abs_url(); // Correct Gallery 3.1.x tag URL

    foreach ($urls as $url) {
      // Check that URL is redirected to canonical URL.
      $redirected_url = Request::factory($url)->execute()->headers("Location");
      $this->assertEquals($canonical_url, $redirected_url);
    }
  }

  public function test_redirect_malformed_multitag_url() {
    $name = Test::random_name();
    $album = Test::random_album();
    Tag::add($album, "{$name}1");
    Tag::add($album, "{$name}2");
    $tag1 = ORM::factory("Tag")->where("name", "=", "{$name}1")->find();
    $tag2 = ORM::factory("Tag")->where("name", "=", "{$name}2")->find();

    $urls = array(
      "tag/,{$tag1->slug},{$tag2->slug}",               // Leading comma
      "tag/{$tag1->slug},{$tag2->slug},",               // Trailing comma
      "tag/{$tag1->slug},,{$tag2->slug},,",             // Duplicate commas
      "tag/{$tag1->slug},{$tag2->slug},{$tag1->slug}",  // Duplicate tag
    );

    $canonical_url = Tag::abs_url(array($tag1, $tag2)); // Correct URL

    foreach ($urls as $url) {
      // Check that URL is redirected to canonical URL.
      $redirected_url = Request::factory($url)->execute()->headers("Location");
      $this->assertEquals($canonical_url, $redirected_url);
    }
  }
}
