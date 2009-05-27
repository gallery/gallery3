<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Photo_Helper_Test extends Unit_Test_Case {
  public function create_photo_test() {
    $rand = rand();

    $filename = DOCROOT . "core/tests/test.jpg";
    $image_info = getimagesize($filename);

    $root = ORM::factory("item", 1);
    $photo = photo::create($root, $filename, "$rand.jpg", $rand, $rand);

    $this->assert_equal(VARPATH . "albums/$rand.jpg", $photo->file_path());
    $this->assert_equal(VARPATH . "thumbs/{$rand}.jpg", $photo->thumb_path());
    $this->assert_equal(VARPATH . "resizes/{$rand}.jpg", $photo->resize_path());

    $this->assert_true(is_file($photo->file_path()), "missing: {$photo->file_path()}");
    $this->assert_true(is_file($photo->resize_path()), "missing: {$photo->resize_path()}");
    $this->assert_true(is_file($photo->thumb_path()), "missing: {$photo->thumb_path()}");

    $this->assert_equal($root->id, $photo->parent_id); // MPTT tests cover other hierarchy checks
    $this->assert_equal("$rand.jpg", $photo->name);
    $this->assert_equal($rand, $photo->title);
    $this->assert_equal($rand, $photo->description);
    $this->assert_equal("image/jpeg", $photo->mime_type);
    $this->assert_equal($image_info[0], $photo->width);
    $this->assert_equal($image_info[1], $photo->height);

    $this->assert_equal($photo->parent()->right - 2, $photo->left);
    $this->assert_equal($photo->parent()->right - 1, $photo->right);
  }

  public function create_conflicting_photo_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    $photo1 = photo::create($root, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $photo2 = photo::create($root, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $this->assert_true($photo1->name != $photo2->name);
  }

  public function create_photo_with_no_extension_test() {
    $root = ORM::factory("item", 1);
    try {
      photo::create($root, "/tmp", "name", "title", "description");
      $this->assert_false("should fail with an exception");
    } catch (Exception $e) {
      // pass
    }
  }

  public function thumb_url_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    $photo = photo::create($root, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);
    $this->assert_equal("http://./var/thumbs/{$rand}.jpg", $photo->thumb_url());
  }

  public function resize_url_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    $album = album::create($root, $rand, $rand, $rand);
    $photo = photo::create($album, DOCROOT . "core/tests/test.jpg", "$rand.jpg", $rand, $rand);

    $this->assert_equal("http://./var/resizes/{$rand}/{$rand}.jpg", $photo->resize_url());
  }

  public function create_photo_shouldnt_allow_names_with_slash_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    try {
      $photo = photo::create($root, DOCROOT . "core/tests/test.jpg", "$rand/.jpg", $rand, $rand);
    } catch (Exception $e) {
      // pass
      return;
    }

    $this->assert_true(false, "Shouldn't create a photo with / in the name");
  }

  public function create_photo_silently_trims_trailing_periods_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    try {
      $photo = photo::create($root, DOCROOT . "core/tests/test.jpg", "$rand.jpg.", $rand, $rand);
    } catch (Exception $e) {
      $this->assert_equal("@todo NAME_CANNOT_END_IN_PERIOD", $e->getMessage());
      return;
    }

    $this->assert_true(false, "Shouldn't create a photo with trailing . in the name");
  }
}
