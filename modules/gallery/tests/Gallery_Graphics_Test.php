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
class Gallery_Graphics_Helper_Test extends Unittest_TestCase {
  public function test_rotate_jpg() {
    // Input is a 1024x768 jpg, output is rotated 90 degrees
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    $options = array("degrees" => 90);
    GalleryGraphics::rotate($input_file, $output_file, $options, null);

    // Output is rotated to 768x1024 jpg
    $this->assertEquals(array(768, 1024, "image/jpeg", "jpg"), Photo::get_file_metadata($output_file));
  }

  public function test_rotate_jpg_without_options() {
    // Input is a 1024x768 jpg, output options undefined
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    GalleryGraphics::rotate($input_file, $output_file, null, null);

    // Output is not rotated, still a 1024x768 jpg
    $this->assertEquals(array(1024, 768, "image/jpeg", "jpg"), Photo::get_file_metadata($output_file));
  }

  /**
   * @expectedException Gallery_Exception
   */
  public function test_rotate_bad_jpg() {
    // Input is a garbled jpg, output is jpg autofit to 300x300
    $input_file = TMPPATH . Test::random_name() . ".jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    $options = array("degrees" => 90);
    file_put_contents($input_file, Test::lorem_ipsum(200));

    // Should get passed to Image library and throw an exception
    GalleryGraphics::rotate($input_file, $output_file, $options, null);
  }

  public function test_resize_jpg() {
    // Input is a 1024x768 jpg, output is jpg autofit to 300x300
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    GalleryGraphics::resize($input_file, $output_file, $options, null);

    // Output is resized to 300x225 jpg
    $this->assertEquals(array(300, 225, "image/jpeg", "jpg"), Photo::get_file_metadata($output_file));
  }

  public function test_resize_jpg_to_png() {
    // Input is a 1024x768 jpg, output is png autofit to 300x300
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".png";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    GalleryGraphics::resize($input_file, $output_file, $options, null);

    // Output is resized to 300x225 png
    $this->assertEquals(array(300, 225, "image/png", "png"), Photo::get_file_metadata($output_file));
  }

  public function test_resize_jpg_with_no_upscale() {
    // Input is a 1024x768 jpg, output is jpg autofit to 1200x1200 - should not upscale
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    $options = array("width" => 1200, "height" => 1200, "master" => Image::AUTO);
    GalleryGraphics::resize($input_file, $output_file, $options, null);

    // Output is copied directly from input
    $this->assertEquals(file_get_contents($input_file), file_get_contents($output_file));
  }

  public function test_resize_jpg_to_png_with_no_upscale() {
    // Input is a 1024x768 jpg, output is png autofit to 1200x1200 - should not upscale
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".png";
    $options = array("width" => 1200, "height" => 1200, "master" => Image::AUTO);
    GalleryGraphics::resize($input_file, $output_file, $options, null);

    // Output is converted from input without resize
    $this->assertEquals(array(1024, 768, "image/png", "png"), Photo::get_file_metadata($output_file));
  }

  public function test_resize_jpg_without_options() {
    // Input is a 1024x768 jpg, output is jpg without options - should not attempt resize
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    GalleryGraphics::resize($input_file, $output_file, null, null);

    // Output is copied directly from input
    $this->assertEquals(file_get_contents($input_file), file_get_contents($output_file));
  }

  public function test_resize_jpg_to_png_without_options() {
    // Input is a 1024x768 jpg, output is png without options - should not attempt resize
    $input_file = MODPATH . "gallery_unittest/assets/test.jpg";
    $output_file = TMPPATH . Test::random_name() . ".png";
    GalleryGraphics::resize($input_file, $output_file, null, null);

    // Output is converted from input without resize
    $this->assertEquals(array(1024, 768, "image/png", "png"), Photo::get_file_metadata($output_file));
  }

  /**
   * @expectedException Gallery_Exception
   */
  public function test_resize_bad_jpg() {
    // Input is a garbled jpg, output is jpg autofit to 300x300
    $input_file = TMPPATH . Test::random_name() . ".jpg";
    $output_file = TMPPATH . Test::random_name() . ".jpg";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    file_put_contents($input_file, Test::lorem_ipsum(200));

    // Should get passed to Image library and throw an exception
    GalleryGraphics::resize($input_file, $output_file, $options, null);
  }
}