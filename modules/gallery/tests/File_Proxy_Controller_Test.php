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
class File_Proxy_Controller_Test extends Unittest_TestCase {
  public $var;

  public function setup() {
    $this->var = substr(VARPATH, strlen(DOCROOT), -1);  // i.e. "var" or "var/test"
    parent::setup();
    $this->_save = array($_SERVER);
  }

  public function teardown() {
    list($_SERVER) = $this->_save;
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_basic() {
    $photo = Test::random_photo();
    $request = Request::factory("{$this->var}/albums/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame($photo->file_path(), $response[0]);
  }

  public function test_query_params_are_ignored() {
    $photo = Test::random_photo();
    $request = Request::factory("{$this->var}/albums/{$photo->name}?a=1&b=2");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame($photo->file_path(), $response[0]);
  }

  public function test_file_proxy_cannot_be_called_directly() {
    $request = Request::factory("file_proxy/index");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:1", $response[0]);
  }

  public function test_file_must_be_in_albums_thumbs_or_resizes() {
    $request = Request::factory("{$this->var}/uploads/.htaccess");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:2", $response[0]);
  }

  public function test_movie_thumbnails_are_jpgs() {
    $movie = Test::random_movie();
    $name = LegalFile::change_extension($movie->name, "jpg");
    $request = Request::factory("{$this->var}/thumbs/$name");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame($movie->thumb_path(), $response[0]);
  }

  public function test_invalid_item() {
    $photo = Test::random_photo();
    $request = Request::factory("{$this->var}/albums/x_{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:3", $response[0]);
  }

  public function test_need_view_permission() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db

    Access::deny(Identity::everybody(), "view", $album);
    Identity::set_active_user(Identity::guest());

    // Cannot see thumb.
    $request = Request::factory("{$this->var}/thumbs/{$album->name}/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:4", $response[0]);
    // Cannot see original.
    $request = Request::factory("{$this->var}/albums/{$album->name}/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:4", $response[0]);
  }

  public function test_need_view_full_permission_to_view_original() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db

    Access::deny(Identity::everybody(), "view_full", $album);
    Identity::set_active_user(Identity::guest());

    // Can see thumb.
    $request = Request::factory("{$this->var}/thumbs/{$album->name}/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame($photo->thumb_path(), $response[0]);
    // Cannot see original.
    $request = Request::factory("{$this->var}/albums/{$album->name}/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:5", $response[0]);
  }

  public function test_cant_proxy_an_album() {
    $album = Test::random_album();

    $request = Request::factory("{$this->var}/albums/{$album->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:6", $response[0]);
  }

  public function test_missing_file() {
    $photo = Test::random_photo();
    unlink($photo->file_path());

    $request = Request::factory("{$this->var}/albums/{$photo->name}");
    $response = explode("\n", $request->execute()->body());
    $this->assertSame("HTTP:404:7", $response[0]);
  }
}