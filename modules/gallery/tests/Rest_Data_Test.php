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
class Rest_Data_Test extends Unittest_TestCase {
  // Note: Rest_Data uses Controller_FileProxy directly, so there's no need to re-test
  // access control as Controller_FileProxy's tests already do that nicely.

  public function test_basic_get() {
    $photo = Test::random_photo();

    $rest = Rest::factory("Data", $photo->id, array("size" => "thumb"));
    $this->assertSame($photo->thumb_path(), $rest->get_response());

    $rest = Rest::factory("Data", $photo->id, array("size" => "resize"));
    $this->assertSame($photo->resize_path(), $rest->get_response());

    $rest = Rest::factory("Data", $photo->id, array("size" => "full"));
    $this->assertSame($photo->file_path(), $rest->get_response());
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_missing_size() {
    $photo = Test::random_album();

    $rest = Rest::factory("Data", $photo->id);
    $this->assertSame($photo->thumb_path(), $rest->get_response());
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_invalid_size() {
    $photo = Test::random_album();

    $rest = Rest::factory("Data", $photo->id, array("size" => "albums")); // should be full
    $this->assertSame($photo->file_path(), $rest->get_response());
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_illegal_access() {
    // Rest_Data calls Controller_FileProxy in a sub-request, which by itself
    // isolates exceptions... let's ensure we're rethrowing them correctly.

    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();

    Access::deny(Identity::everybody(), "view", $album);
    Identity::set_active_user(Identity::guest());

    $rest = Rest::factory("Data", $photo->id, array("size" => "thumb"));
    $this->assertSame($photo->thumb_path(), $rest->get_response());
  }
}
