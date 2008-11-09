<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Photo_Test extends Unit_Test_Case {
  public function create_photo_test() {
    $rand = rand();
    $photo = photo::create(1, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);

    $this->assert_equal(VARPATH . "albums/$rand.jpg", $photo->file_path());
    $this->assert_equal(VARPATH . "resizes/{$rand}.thumb.jpg", $photo->thumbnail_path());
    $this->assert_equal(VARPATH . "resizes/{$rand}.resize.jpg", $photo->resize_path());

    $this->assert_true(is_file($photo->file_path()), "missing: {$photo->file_path()}");
    $this->assert_true(is_file($photo->resize_path()), "missing: {$photo->resize_path()}");
    $this->assert_true(is_file($photo->thumbnail_path()), "missing: {$photo->thumbnail_path()}");

    $this->assert_equal(1, $photo->parent_id);  // MPTT tests will cover other hierarchy checks
    $this->assert_equal("$rand.jpg", $photo->name);
    $this->assert_equal($rand, $photo->title);
    $this->assert_equal($rand, $photo->description);

    $this->assert_equal($photo->parent()->right - 2, $photo->left);
    $this->assert_equal($photo->parent()->right - 1, $photo->right);
  }

  public function create_conflicting_photo_test() {
    $rand = rand();
    $photo1 = photo::create(1, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $photo2 = photo::create(1, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $this->assert_true($photo1->name != $photo2->name);
  }

  public function create_photo_with_no_extension_test() {
    try {
      photo::create(1, "/tmp", "name", "title", "description");
      $this->assert_false("should fail with an exception");
    } catch (Exception $e) {
      // pass
    }
  }

  public function thumbnail_url_test() {
    $rand = rand();
    $photo = photo::create(1, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $this->assert_equal("http://./var/resizes/{$rand}.thumb.jpg", $photo->thumbnail_url());
  }

  public function resize_url_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);
    $photo = photo::create($album->id, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);

    $this->assert_equal("http://./var/resizes/{$rand}/{$rand}.resize.jpg", $photo->resize_url());
  }
}
