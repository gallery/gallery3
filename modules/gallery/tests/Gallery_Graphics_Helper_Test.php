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
class Gallery_Graphics_Helper_Test extends Gallery_Unit_Test_Case {
  public function rotate_jpg_test() {
    // Input is a 1024x768 jpg, output is rotated 90 degrees
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    $options = array("degrees" => 90);
    gallery_graphics::rotate($input_file, $output_file, $options, null);

    // Output is rotated to 768x1024 jpg
    $this->assert_equal(array(768, 1024, "image/jpeg", "jpg"), photo::get_file_metadata($output_file));
  }

  public function rotate_jpg_without_options_test() {
    // Input is a 1024x768 jpg, output options undefined
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    gallery_graphics::rotate($input_file, $output_file, null, null);

    // Output is not rotated, still a 1024x768 jpg
    $this->assert_equal(array(1024, 768, "image/jpeg", "jpg"), photo::get_file_metadata($output_file));
  }

  public function rotate_bad_jpg_test() {
    // Input is a garbled jpg, output is jpg autofit to 300x300
    $input_file = TMPPATH . test::random_name() . ".jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    $options = array("degrees" => 90);
    file_put_contents($input_file, test::lorem_ipsum(200));

    // Should get passed to Image library and throw an exception
    try {
      gallery_graphics::rotate($input_file, $output_file, $options, null);
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }

  public function resize_jpg_test() {
    // Input is a 1024x768 jpg, output is jpg autofit to 300x300
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    gallery_graphics::resize($input_file, $output_file, $options, null);

    // Output is resized to 300x225 jpg
    $this->assert_equal(array(300, 225, "image/jpeg", "jpg"), photo::get_file_metadata($output_file));
  }

  public function resize_jpg_to_png_test() {
    // Input is a 1024x768 jpg, output is png autofit to 300x300
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".png";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    gallery_graphics::resize($input_file, $output_file, $options, null);

    // Output is resized to 300x225 png
    $this->assert_equal(array(300, 225, "image/png", "png"), photo::get_file_metadata($output_file));
  }

  public function resize_jpg_with_no_upscale_test() {
    // Input is a 1024x768 jpg, output is jpg autofit to 1200x1200 - should not upscale
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    $options = array("width" => 1200, "height" => 1200, "master" => Image::AUTO);
    gallery_graphics::resize($input_file, $output_file, $options, null);

    // Output is copied directly from input
    $this->assert_equal(file_get_contents($input_file), file_get_contents($output_file));
  }

  public function resize_jpg_to_png_with_no_upscale_test() {
    // Input is a 1024x768 jpg, output is png autofit to 1200x1200 - should not upscale
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".png";
    $options = array("width" => 1200, "height" => 1200, "master" => Image::AUTO);
    gallery_graphics::resize($input_file, $output_file, $options, null);

    // Output is converted from input without resize
    $this->assert_equal(array(1024, 768, "image/png", "png"), photo::get_file_metadata($output_file));
  }

  public function resize_jpg_without_options_test() {
    // Input is a 1024x768 jpg, output is jpg without options - should not attempt resize
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    gallery_graphics::resize($input_file, $output_file, null, null);

    // Output is copied directly from input
    $this->assert_equal(file_get_contents($input_file), file_get_contents($output_file));
  }

  public function resize_jpg_to_png_without_options_test() {
    // Input is a 1024x768 jpg, output is png without options - should not attempt resize
    $input_file = MODPATH . "gallery/tests/test.jpg";
    $output_file = TMPPATH . test::random_name() . ".png";
    gallery_graphics::resize($input_file, $output_file, null, null);

    // Output is converted from input without resize
    $this->assert_equal(array(1024, 768, "image/png", "png"), photo::get_file_metadata($output_file));
  }

  public function resize_bad_jpg_test() {
    // Input is a garbled jpg, output is jpg autofit to 300x300
    $input_file = TMPPATH . test::random_name() . ".jpg";
    $output_file = TMPPATH . test::random_name() . ".jpg";
    $options = array("width" => 300, "height" => 300, "master" => Image::AUTO);
    file_put_contents($input_file, test::lorem_ipsum(200));

    // Should get passed to Image library and throw an exception
    try {
      gallery_graphics::resize($input_file, $output_file, $options, null);
      $this->assert_true(false, "Shouldn't get here");
    } catch (Exception $e) {
      // pass
    }
  }
}