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
  public function setup() {
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
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/{$photo->name}");
    $controller = new Controller_FileProxy();
    $this->assertSame($photo->file_path(), $controller->__call("", array()));
  }

  public function test_query_params_are_ignored() {
    $photo = Test::random_photo();
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/{$photo->name}?a=1&b=2");
    $controller = new Controller_FileProxy();
    $this->assertSame($photo->file_path(), $controller->__call("", array()));
  }

  public function test_file_must_be_in_var() {
    $_SERVER["REQUEST_URI"] = URL::file("index.php");
    $controller = new Controller_FileProxy();
    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(1, $e->test_fail_code);
    }
  }

  public function test_file_must_be_in_albums_thumbs_or_resizes() {
    $_SERVER["REQUEST_URI"] = URL::file("var/test/var/uploads/.htaccess");
    $controller = new Controller_FileProxy();
    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(2, $e->test_fail_code);
    }
  }

  public function test_movie_thumbnails_are_jpgs() {
    $movie = Test::random_movie();
    $name = LegalFile::change_extension($movie->name, "jpg");
    $_SERVER["REQUEST_URI"] = URL::file("var/thumbs/$name");
    $controller = new Controller_FileProxy();
    $this->assertSame($movie->thumb_path(), $controller->__call("", array()));
  }

  public function test_invalid_item() {
    $photo = Test::random_photo();
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/x_{$photo->name}");
    $controller = new Controller_FileProxy();
    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(3, $e->test_fail_code);
    }
  }

  public function test_need_view_full_permission_to_view_original() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/{$album->name}/{$photo->name}");
    $controller = new Controller_FileProxy();

    Access::deny(Identity::everybody(), "view_full", $album);
    Identity::set_active_user(Identity::guest());

    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(5, $e->test_fail_code);
    }
  }

  public function test_cant_proxy_an_album() {
    $album = Test::random_album();
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/{$album->name}");
    $controller = new Controller_FileProxy();

    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(6, $e->test_fail_code);
    }
  }

  public function test_missing_file() {
    $photo = Test::random_photo();
    $_SERVER["REQUEST_URI"] = URL::file("var/albums/{$photo->name}");
    unlink($photo->file_path());
    $controller = new Controller_FileProxy();

    try {
      $controller->__call("", array());
      $this->assertTrue(false);
    } catch (HTTP_Exception_404 $e) {
      $this->assertSame(7, $e->test_fail_code);
    }
  }
}