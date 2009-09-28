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
class gallery_graphics_Core {
  /**
   * Resize an image.  Valid options are width, height and master.  Master is one of the Image
   * master dimension constants.
   *
   * @param string     $input_file
   * @param string     $output_file
   * @param array      $options
   */
  static function resize($input_file, $output_file, $options) {
    graphics::init_toolkit();

    module::event("graphics_resize", $input_file, $output_file, $options);

    if (@filesize($input_file) == 0) {
      throw new Exception("@todo EMPTY_INPUT_FILE");
    }

    $dims = getimagesize($input_file);
    if (max($dims[0], $dims[1]) < min($options["width"], $options["height"])) {
      // Image would get upscaled; do nothing
      copy($input_file, $output_file);
    } else {
      $image = Image::factory($input_file)
        ->resize($options["width"], $options["height"], $options["master"])
        ->quality(module::get_var("gallery", "image_quality"));
      if (graphics::can("sharpen")) {
        $image->sharpen(module::get_var("gallery", "image_sharpen"));
      }
      $image->save($output_file);
    }

    module::event("graphics_resize_completed", $input_file, $output_file, $options);
  }

  /**
   * Overlay an image on top of the input file.
   *
   * Valid options are: file, mime_type, position, transparency_percent, padding
   *
   * Valid positions: northwest, north, northeast,
   *                  west, center, east,
   *                  southwest, south, southeast
   *
   * padding is in pixels
   *
   * @param string     $input_file
   * @param string     $output_file
   * @param array      $options
   */
  static function composite($input_file, $output_file, $options) {
    try {
      graphics::init_toolkit();

      module::event("graphics_composite", $input_file, $output_file, $options);

      list ($width, $height) = getimagesize($input_file);
      list ($w_width, $w_height) = getimagesize($options["file"]);

      $pad = isset($options["padding"]) ? $options["padding"] : 10;
      $top = $pad;
      $left = $pad;
      $y_center = max($height / 2 - $w_height / 2, $pad);
      $x_center = max($width / 2 - $w_width / 2, $pad);
      $bottom = max($height - $w_height - $pad, $pad);
      $right = max($width - $w_width - $pad, $pad);

      switch ($options["position"]) {
      case "northwest": $x = $left;     $y = $top;       break;
      case "north":     $x = $x_center; $y = $top;       break;
      case "northeast": $x = $right;    $y = $top;       break;
      case "west":      $x = $left;     $y = $y_center;  break;
      case "center":    $x = $x_center; $y = $y_center;  break;
      case "east":      $x = $right;    $y = $y_center;  break;
      case "southwest": $x = $left;     $y = $bottom;    break;
      case "south":     $x = $x_center; $y = $bottom;    break;
      case "southeast": $x = $right;    $y = $bottom;    break;
      }

      Image::factory($input_file)
        ->composite($options["file"], $x, $y, $options["transparency"])
        ->quality(module::get_var("gallery", "image_quality"))
        ->save($output_file);

      module::event("graphics_composite_completed", $input_file, $output_file, $options);
    } catch (ErrorException $e) {
      Kohana::log("error", $e->get_message());
    }
  }
}
