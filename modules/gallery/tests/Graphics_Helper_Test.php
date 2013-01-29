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
class Graphics_Helper_Test extends Gallery_Unit_Test_Case {
  public function generate_photo_test() {
    $photo = test::random_photo();
    // Check that the images were correctly resized
    $this->assert_equal(array(640, 480, "image/jpeg", "jpg"),
                        photo::get_file_metadata($photo->resize_path()));
    $this->assert_equal(array(200, 150, "image/jpeg", "jpg"),
                        photo::get_file_metadata($photo->thumb_path()));
    // Check that the items table got updated
    $this->assert_equal(array(640, 480), array($photo->resize_width, $photo->resize_height));
    $this->assert_equal(array(200, 150), array($photo->thumb_width, $photo->thumb_height));
    // Check that the images are not marked dirty
    $this->assert_equal(0, $photo->resize_dirty);
    $this->assert_equal(0, $photo->thumb_dirty);
  }

  public function generate_movie_test() {
    $movie = test::random_movie();
    // Check that the image was correctly resized
    $this->assert_equal(array(200, 160, "image/jpeg", "jpg"),
                        photo::get_file_metadata($movie->thumb_path()));
    // Check that the items table got updated
    $this->assert_equal(array(200, 160), array($movie->thumb_width, $movie->thumb_height));
    // Check that the image is not marked dirty
    $this->assert_equal(0, $movie->thumb_dirty);
  }

  public function generate_bad_photo_test() {
    $photo = test::random_photo();
    // At this point, the photo is valid and has a valid resize and thumb.  Make it garble.
    file_put_contents($photo->file_path(), test::lorem_ipsum(200));
    // Regenerate
    $photo->resize_dirty = 1;
    $photo->thumb_dirty = 1;
    try {
      graphics::generate($photo);
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // Exception expected
    }
    // Check that the images got replaced with missing image placeholders
    $this->assert_same(file_get_contents(MODPATH . "gallery/images/missing_photo.jpg"),
                       file_get_contents($photo->resize_path()));
    $this->assert_same(file_get_contents(MODPATH . "gallery/images/missing_photo.jpg"),
                       file_get_contents($photo->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assert_equal(array(200, 200), array($photo->resize_width, $photo->resize_height));
    $this->assert_equal(array(200, 200), array($photo->thumb_width, $photo->thumb_height));
    // Check that the images are marked as dirty
    $this->assert_equal(1, $photo->resize_dirty);
    $this->assert_equal(1, $photo->thumb_dirty);
  }

  public function generate_bad_movie_test() {
    // Unlike photos, its ok to have missing movies - no thrown exceptions, thumb_dirty can be reset.
    $movie = test::random_movie();
    // At this point, the movie is valid and has a valid thumb.  Make it garble.
    file_put_contents($movie->file_path(), test::lorem_ipsum(200));
    // Regenerate
    $movie->thumb_dirty = 1;
    graphics::generate($movie);
    // Check that the image got replaced with a missing image placeholder
    $this->assert_same(file_get_contents(MODPATH . "gallery/images/missing_movie.jpg"),
                       file_get_contents($movie->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assert_equal(array(200, 200), array($movie->thumb_width, $movie->thumb_height));
    // Check that the image is *not* marked as dirty
    $this->assert_equal(0, $movie->thumb_dirty);
  }
}