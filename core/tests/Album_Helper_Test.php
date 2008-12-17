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
class Album_Helper_Test extends Unit_Test_Case {
  public function create_album_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);

    $this->assert_equal(VARPATH . "albums/$rand", $album->file_path());
    $this->assert_equal(VARPATH . "thumbs/$rand/_album.jpg", $album->thumb_path());
    $this->assert_true(is_dir(VARPATH . "thumbs/$rand"), "missing thumb dir");

    // It's unclear that a resize makes sense for an album.  But we have one.
    $this->assert_equal(VARPATH . "resizes/$rand/_album.jpg", $album->resize_path());
    $this->assert_true(is_dir(VARPATH . "resizes/$rand"), "missing resizes dir");

    $this->assert_equal(1, $album->parent_id);  // MPTT tests will cover other hierarchy checks
    $this->assert_equal($rand, $album->name);
    $this->assert_equal($rand, $album->title);
    $this->assert_equal($rand, $album->description);
  }

  public function create_conflicting_album_test() {
    $rand = rand();
    $album1 = album::create(1, $rand, $rand, $rand);
    $album2 = album::create(1, $rand, $rand, $rand);
    $this->assert_true($album1->name != $album2->name);
  }

  public function thumb_url_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);
    $this->assert_equal("http://./var/thumbs/$rand/_album.jpg", $album->thumb_url());
  }

  public function resize_url_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);
    $this->assert_equal("http://./var/resizes/$rand/_album.jpg", $album->resize_url());
  }
}
