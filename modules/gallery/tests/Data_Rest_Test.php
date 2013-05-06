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
class Data_Rest_Test extends Unittest_TestCase {
  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_resolve() {
    $photo = Test::random_photo();
    $resolved = Rest::resolve(Rest::url("data", $photo, 640));
    $this->assertEquals($photo->id, $resolved->id);
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_resolve_needs_permission() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();  // new photo changed the album in the db

    Access::deny(Identity::everybody(), "view", $album);
    Identity::set_active_user(Identity::guest());

    Hook_Rest_Data::resolve($photo->id);
  }

  public function test_basic_get() {
    $photo = Test::random_photo();

    $request = new stdClass();
    $request->url = Rest::url("data", $photo, "thumb");
    $request->params = new stdClass();

    $request->params->size = "thumb";
    $this->assertSame($photo->thumb_path(), Hook_Rest_Data::get($request));

    $request->params->size = "resize";
    $this->assertSame($photo->resize_path(), Hook_Rest_Data::get($request));

    $request->params->size = "full";
    $this->assertSame($photo->file_path(), Hook_Rest_Data::get($request));
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_illegal_access() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();

    Access::deny(Identity::everybody(), "view", $album);
    Identity::set_active_user(Identity::guest());

    $request = new stdClass();
    $request->url = Rest::url("data", $photo, "thumb");
    $request->params = new stdClass();
    $request->params->size = "thumb";

    Hook_Rest_Data::get($request);
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_missing_file() {
    $photo = Test::random_photo();

    $request = new stdClass();
    $request->url = Rest::url("data", $photo, "thumb");
    $request->params = new stdClass();
    $request->params->size = "thumb";

    unlink($photo->thumb_path());  // oops!

    Hook_Rest_Data::get($request);
  }

  public function test_cache_buster() {
    $photo = Test::random_photo();

    $this->assertSame(
      URL::abs_site("rest/data/{$photo->id}?size=thumb&m=" . filemtime($photo->thumb_path())),
      Hook_Rest_Data::url($photo, "thumb"));
  }
}

