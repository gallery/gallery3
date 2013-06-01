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

    // Check that correct Gallery 3.0.x tag URL is redirected
    $url = "tag/{$tag->id}/$name";
    $redirected_url = Request::factory($url)->execute()->headers("location");
    $this->assertEquals($tag->abs_url(), $redirected_url);

    // Check that malformed Gallery 3.0.x tag URL is redirected
    $url = "tag/{$tag->id}";
    $redirected_url = Request::factory($url)->execute()->headers("location");
    $this->assertEquals($tag->abs_url(), $redirected_url);
  }
}
