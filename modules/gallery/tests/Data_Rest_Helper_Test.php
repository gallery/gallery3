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
class Data_Rest_Helper_Test extends Gallery_Unit_Test_Case {
  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

  public function resolve_test() {
    $photo = test::random_photo();
    $resolved = rest::resolve(rest::url("data", $photo, 640));
    $this->assert_equal($photo->id, $resolved->id);
  }

  public function resolve_needs_permission_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album->reload();  // new photo changed the album in the db

    access::deny(identity::everybody(), "view", $album);
    identity::set_active_user(identity::guest());

    try {
      data_rest::resolve($photo->id);
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      // pass
    }
  }

  public function basic_get_test() {
    $photo = test::random_photo();

    $request = new stdClass();
    $request->url = rest::url("data", $photo, "thumb");
    $request->params = new stdClass();

    $request->params->size = "thumb";
    $this->assert_same($photo->thumb_path(), data_rest::get($request));

    $request->params->size = "resize";
    $this->assert_same($photo->resize_path(), data_rest::get($request));

    $request->params->size = "full";
    $this->assert_same($photo->file_path(), data_rest::get($request));
  }

  public function illegal_access_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album->reload();

    access::deny(identity::everybody(), "view", $album);
    identity::set_active_user(identity::guest());

    $request = new stdClass();
    $request->url = rest::url("data", $photo, "thumb");
    $request->params = new stdClass();
    $request->params->size = "thumb";

    try {
      data_rest::get($request);
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      // pass
    }
  }

  public function missing_file_test() {
    $photo = test::random_photo();

    $request = new stdClass();
    $request->url = rest::url("data", $photo, "thumb");
    $request->params = new stdClass();
    $request->params->size = "thumb";

    unlink($photo->thumb_path());  // oops!

    try {
      data_rest::get($request);
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      // pass
    }
  }

  public function cache_buster_test() {
    $photo = test::random_photo();

    $this->assert_same(
      url::abs_site("rest/data/{$photo->id}?size=thumb&m=" . filemtime($photo->thumb_path())),
      data_rest::url($photo, "thumb"));
  }
}

