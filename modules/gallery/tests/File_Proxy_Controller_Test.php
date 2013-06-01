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
    parent::setup();
    $this->var = substr(VARPATH, strlen(DOCROOT), -1);  // i.e. "var" or "var/test"
  }

  public function teardown() {
    Identity::set_active_user(Identity::admin_user());
    parent::teardown();
  }

  public function test_basic() {
    $photo = Test::random_photo();
    $response = Request::factory("{$this->var}/albums/{$photo->name}")->execute();
    $this->assertEquals($photo->file_path(), $response->body());
  }

  public function test_query_params_are_ignored() {
    $photo = Test::random_photo();
    $response = Request::factory("{$this->var}/albums/{$photo->name}?a=1&b=2")->execute();
    $this->assertEquals($photo->file_path(), $response->body());
  }

  public function test_file_proxy_cannot_be_called_directly() {
    $response = Request::factory("file_proxy/index")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(1, substr($response->body(), 0, 1));
  }

  public function test_file_must_be_in_albums_thumbs_or_resizes() {
    $response = Request::factory("{$this->var}/uploads/.htaccess")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(2, substr($response->body(), 0, 1));
  }

  public function test_movie_thumbnails_are_jpgs() {
    $movie = Test::random_movie();
    $name = LegalFile::change_extension($movie->name, "jpg");
    $response = Request::factory("{$this->var}/thumbs/$name")->execute();
    $this->assertEquals($movie->thumb_path(), $response->body());
  }

  public function test_invalid_item() {
    $photo = Test::random_photo();
    $response = Request::factory("{$this->var}/albums/x_{$photo->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(3, substr($response->body(), 0, 1));
  }

  public function test_need_view_permission() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db

    Access::deny(Identity::everybody(), "view", $album);
    Identity::set_active_user(Identity::guest());

    // Cannot see thumb.
    $response = Request::factory("{$this->var}/thumbs/{$album->name}/{$photo->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(4, substr($response->body(), 0, 1));
    // Cannot see original.
    $response = Request::factory("{$this->var}/albums/{$album->name}/{$photo->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(4, substr($response->body(), 0, 1));
  }

  public function test_need_view_full_permission_to_view_original() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db

    Access::deny(Identity::everybody(), "view_full", $album);
    Identity::set_active_user(Identity::guest());

    // Can see thumb.
    $response = Request::factory("{$this->var}/thumbs/{$album->name}/{$photo->name}")->execute();
    $this->assertEquals($photo->thumb_path(), $response->body());
    // Cannot see original.
    $response = Request::factory("{$this->var}/albums/{$album->name}/{$photo->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(5, substr($response->body(), 0, 1));
  }

  public function test_cant_proxy_an_album() {
    $album = Test::random_album();

    $response = Request::factory("{$this->var}/albums/{$album->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(6, substr($response->body(), 0, 1));
  }

  public function test_missing_file() {
    $photo = Test::random_photo();
    unlink($photo->file_path());

    $response = Request::factory("{$this->var}/albums/{$photo->name}")->execute();
    $this->assertEquals(404, $response->status());
    $this->assertEquals(7, substr($response->body(), 0, 1));
  }
}