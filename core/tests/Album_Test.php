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
class Album_Test extends Unit_Test_Case {
  public function create_album_test() {
    $rand = rand();
    $album = album::create(1, $rand, $rand, $rand);

    $this->assert_equal(VARPATH . "albums/$rand", $album->path());
    $this->assert_equal(VARPATH . "thumbnails/$rand", $album->thumbnail_path());
    $this->assert_equal(VARPATH . "thumbnails/$rand", $album->resize_path());

    $this->assert_true(is_dir($album->path()), "missing path: {$album->path()}");
    $this->assert_true(is_dir($album->resize_path()), "missing path: {$album->resize_path()}");

    $this->assert_equal(1, $album->parent_id);  // MPTT tests will cover other hierarchy checks
    $this->assert_equal($rand, $album->name);
    $this->assert_equal($rand, $album->title);
    $this->assert_equal($rand, $album->description);

    $this->assert_equal($album->parent()->right - 2, $album->left);
    $this->assert_equal($album->parent()->right - 1, $album->right);
  }

  public function create_conflicting_album_test() {
    $rand = rand();
    $album1 = album::create(1, $rand, $rand, $rand);
    $album2 = album::create(1, $rand, $rand, $rand);
    $this->assert_true($album1->name != $album2->name);
  }
}
