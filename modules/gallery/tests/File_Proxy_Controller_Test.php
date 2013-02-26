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
class File_Proxy_Controller_Test extends Gallery_Unit_Test_Case {
  public function setup() {
    $this->_save = array($_SERVER);
  }

  public function teardown() {
    list($_SERVER) = $this->_save;
    identity::set_active_user(identity::admin_user());
  }

  public function basic_test() {
    $photo = test::random_photo();
    $_SERVER["REQUEST_URI"] = url::file("var/albums/{$photo->name}");
    $controller = new File_Proxy_Controller();
    $this->assert_same($photo->file_path(), $controller->__call("", array()));
  }

  public function query_params_are_ignored_test() {
    $photo = test::random_photo();
    $_SERVER["REQUEST_URI"] = url::file("var/albums/{$photo->name}?a=1&b=2");
    $controller = new File_Proxy_Controller();
    $this->assert_same($photo->file_path(), $controller->__call("", array()));
  }

  public function file_must_be_in_var_test() {
    $_SERVER["REQUEST_URI"] = url::file("index.php");
    $controller = new File_Proxy_Controller();
    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(1, $e->test_fail_code);
    }
  }

  public function file_must_be_in_albums_thumbs_or_resizes_test() {
    $_SERVER["REQUEST_URI"] = url::file("var/test/var/uploads/.htaccess");
    $controller = new File_Proxy_Controller();
    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(2, $e->test_fail_code);
    }
  }

  public function movie_thumbnails_are_jpgs_test() {
    $movie = test::random_movie();
    $name = legal_file::change_extension($movie->name, "jpg");
    $_SERVER["REQUEST_URI"] = url::file("var/thumbs/$name");
    $controller = new File_Proxy_Controller();
    $this->assert_same($movie->thumb_path(), $controller->__call("", array()));
  }

  public function invalid_item_test() {
    $photo = test::random_photo();
    $_SERVER["REQUEST_URI"] = url::file("var/albums/x_{$photo->name}");
    $controller = new File_Proxy_Controller();
    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(3, $e->test_fail_code);
    }
  }

  public function need_view_full_permission_to_view_original_test() {
    $album = test::random_album();
    $photo = test::random_photo($album);
    $album = $album->reload(); // adding the photo changed the album in the db
    $_SERVER["REQUEST_URI"] = url::file("var/albums/{$album->name}/{$photo->name}");
    $controller = new File_Proxy_Controller();

    access::deny(identity::everybody(), "view_full", $album);
    identity::set_active_user(identity::guest());

    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(5, $e->test_fail_code);
    }
  }

  public function cant_proxy_an_album_test() {
    $album = test::random_album();
    $_SERVER["REQUEST_URI"] = url::file("var/albums/{$album->name}");
    $controller = new File_Proxy_Controller();

    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(6, $e->test_fail_code);
    }
  }

  public function missing_file_test() {
    $photo = test::random_photo();
    $_SERVER["REQUEST_URI"] = url::file("var/albums/{$photo->name}");
    unlink($photo->file_path());
    $controller = new File_Proxy_Controller();

    try {
      $controller->__call("", array());
      $this->assert_true(false);
    } catch (Kohana_404_Exception $e) {
      $this->assert_same(7, $e->test_fail_code);
    }
  }
}