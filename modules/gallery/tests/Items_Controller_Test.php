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
class Items_Controller_Test extends Unittest_TestCase {
  public function test_change_photo() {
    $photo = Test::random_photo();
    Access::allow(Identity::everybody(), "edit", Item::root());

    $response = Request::factory("items/edit/{$photo->id}")
      ->method(Request::POST)
      ->post(array(
          "name"        => "new name.jpg",
          "title"       => "new title",
          "description" => "new description",
          "slug"        => "new-slug",
          "csrf"        => Access::csrf_token()
        ))
      ->make_ajax()
      ->execute()
      ->body();

    $response = json_decode($response, true);
    $this->assertEquals("success", $response["result"]);

    $photo->reload();
    $this->assertEquals("new-slug", $photo->slug);
    $this->assertEquals("new title", $photo->title);
    $this->assertEquals("new description", $photo->description);
    $this->assertEquals("new name.jpg", $photo->name);
  }

  public function test_change_photo_no_csrf_fails() {
    $photo = Test::random_photo();
    Access::allow(Identity::everybody(), "edit", Item::root());

    $response = Request::factory("items/edit/{$photo->id}")
      ->method(Request::POST)
      ->post(array(
          "name"        => "new name.jpg",
          "title"       => "new title",
          "description" => "new description",
          "slug"        => "new-slug"
        ))
      ->make_ajax()
      ->execute()
      ->body();

    $response = explode("\n", $response);
    $this->assertSame("HTTP:403", $response[0]);
  }
}
