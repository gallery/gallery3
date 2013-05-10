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
class Graphics_Test extends Unittest_TestCase {
  public function test_generate_photo() {
    $photo = Test::random_photo();
    // Check that the images were correctly resized
    $this->assertEquals(array(640, 480, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($photo->resize_path()));
    $this->assertEquals(array(200, 150, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($photo->thumb_path()));
    // Check that the items table got updated
    $this->assertEquals(array(640, 480), array($photo->resize_width, $photo->resize_height));
    $this->assertEquals(array(200, 150), array($photo->thumb_width, $photo->thumb_height));
    // Check that the images are not marked dirty
    $this->assertEquals(0, $photo->resize_dirty);
    $this->assertEquals(0, $photo->thumb_dirty);
  }

  public function test_generate_movie() {
    $movie = Test::random_movie();
    // Check that the image was correctly resized
    $this->assertEquals(array(200, 160, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($movie->thumb_path()));
    // Check that the items table got updated
    $this->assertEquals(array(200, 160), array($movie->thumb_width, $movie->thumb_height));
    // Check that the image is not marked dirty
    $this->assertEquals(0, $movie->thumb_dirty);
  }

  public function test_generate_album_cover() {
    $album = Test::random_album();
    $photo = Test::random_unique_photo($album);
    $album->reload();
    // Check that the image was copied directly from item thumb
    $this->assertEquals(file_get_contents($photo->thumb_path()),
                        file_get_contents($album->thumb_path()));
    // Check that the items table got updated
    $this->assertEquals(array(200, 150), array($album->thumb_width, $album->thumb_height));
    // Check that the image is not marked dirty
    $this->assertEquals(0, $album->thumb_dirty);
  }

  public function test_generate_album_cover_from_png() {
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".png";
    GalleryGraphics::resize($input_file, $output_file, null, null);

    $album = Test::random_album();
    $photo = Test::random_photo_unsaved($album);
    $photo->set_data_file($output_file);
    $photo->name = "album_cover_from_png.png";
    $photo->save();
    $album->reload();
    // Check that the image was correctly resized and converted to jpg
    $this->assertEquals(array(200, 150, "image/jpeg", "jpg"),
                        Photo::get_file_metadata($album->thumb_path()));
    // Check that the items table got updated
    $this->assertEquals(array(200, 150), array($album->thumb_width, $album->thumb_height));
    // Check that the image is not marked dirty
    $this->assertEquals(0, $album->thumb_dirty);
  }

  public function test_generate_album_cover_for_empty_album() {
    $album = Test::random_album();
    // Check that the album cover is the missing image placeholder
    $this->assertSame(file_get_contents(MODPATH . "gallery/assets/graphics/missing_album_cover.jpg"),
                       file_get_contents($album->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assertEquals(array(200, 200), array($album->thumb_width, $album->thumb_height));
    // Check that the image is *not* marked as dirty
    $this->assertEquals(0, $album->thumb_dirty);
  }

  public function test_generate_bad_photo() {
    $photo = Test::random_photo();
    // At this point, the photo is valid and has a valid resize and thumb.  Make it garble.
    file_put_contents($photo->file_path(), Test::lorem_ipsum(200));
    // Regenerate
    $photo->resize_dirty = 1;
    $photo->thumb_dirty = 1;
    try {
      Graphics::generate($photo);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // Exception expected
    }
    // Check that the images got replaced with missing image placeholders
    $this->assertSame(file_get_contents(MODPATH . "gallery/assets/graphics/missing_photo.jpg"),
                       file_get_contents($photo->resize_path()));
    $this->assertSame(file_get_contents(MODPATH . "gallery/assets/graphics/missing_photo.jpg"),
                       file_get_contents($photo->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assertEquals(array(200, 200), array($photo->resize_width, $photo->resize_height));
    $this->assertEquals(array(200, 200), array($photo->thumb_width, $photo->thumb_height));
    // Check that the images are marked as dirty
    $this->assertEquals(1, $photo->resize_dirty);
    $this->assertEquals(1, $photo->thumb_dirty);
  }

  public function test_generate_bad_movie() {
    // Unlike photos, its ok to have missing movies - no thrown exceptions, thumb_dirty can be reset.
    $movie = Test::random_movie();
    // At this point, the movie is valid and has a valid thumb.  Make it garble.
    file_put_contents($movie->file_path(), Test::lorem_ipsum(200));
    // Regenerate
    $movie->thumb_dirty = 1;
    Graphics::generate($movie);
    // Check that the image got replaced with a missing image placeholder
    $this->assertSame(file_get_contents(MODPATH . "gallery/assets/graphics/missing_movie.jpg"),
                       file_get_contents($movie->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assertEquals(array(200, 200), array($movie->thumb_width, $movie->thumb_height));
    // Check that the image is *not* marked as dirty
    $this->assertEquals(0, $movie->thumb_dirty);
  }

  public function test_generate_album_cover_from_bad_photo() {
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $album->reload();
    // At this point, the photo is valid and has a valid resize and thumb.  Make it garble.
    file_put_contents($photo->file_path(), Test::lorem_ipsum(200));
    // Regenerate album from garbled photo.
    $photo->thumb_dirty = 1;
    $photo->save();
    $album->thumb_dirty = 1;
    try {
      Graphics::generate($album);
      $this->assertTrue(false, "Shouldn't get here");
    } catch (Exception $e) {
      // Exception expected
    }
    // Check that the image got replaced with a missing image placeholder
    $this->assertSame(file_get_contents(MODPATH . "gallery/assets/graphics/missing_photo.jpg"),
                       file_get_contents($album->thumb_path()));
    // Check that the items table got updated with new metadata
    $this->assertEquals(array(200, 200), array($album->thumb_width, $album->thumb_height));
    // Check that the images are marked as dirty
    $this->assertEquals(1, $album->thumb_dirty);
  }
}