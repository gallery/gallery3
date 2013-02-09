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
class gallery_graphics_Core {
  /**
   * Rotate an image.  Valid options are degrees
   *
   * @param string     $input_file
   * @param string     $output_file
   * @param array      $options
   * @param Item_Model $item (optional)
   */
  static function rotate($input_file, $output_file, $options, $item=null) {
    graphics::init_toolkit();

    $temp_file = system::temp_filename("rotate_", pathinfo($output_file, PATHINFO_EXTENSION));
    module::event("graphics_rotate", $input_file, $temp_file, $options, $item);

    if (@filesize($temp_file) > 0) {
      // A graphics_rotate event made an image - move it to output_file and use it.
      @rename($temp_file, $output_file);
    } else {
      // No events made an image - proceed with standard process.
      if (@filesize($input_file) == 0) {
        throw new Exception("@todo EMPTY_INPUT_FILE");
      }

      if (!isset($options["degrees"])) {
        $options["degrees"] = 0;
      }

      // Rotate the image.  This also implicitly converts its format if needed.
      Image::factory($input_file)
        ->quality(module::get_var("gallery", "image_quality"))
        ->rotate($options["degrees"])
        ->save($output_file);
    }

    module::event("graphics_rotate_completed", $input_file, $output_file, $options, $item);
  }

  /**
   * Resize an image.  Valid options are width, height and master.  Master is one of the Image
   * master dimension constants.
   *
   * @param string     $input_file
   * @param string     $output_file
   * @param array      $options
   * @param Item_Model $item (optional)
   */
  static function resize($input_file, $output_file, $options, $item=null) {
    graphics::init_toolkit();

    $temp_file = system::temp_filename("resize_", pathinfo($output_file, PATHINFO_EXTENSION));
    module::event("graphics_resize", $input_file, $temp_file, $options, $item);

    if (@filesize($temp_file) > 0) {
      // A graphics_resize event made an image - move it to output_file and use it.
      @rename($temp_file, $output_file);
    } else {
      // No events made an image - proceed with standard process.
      if (@filesize($input_file) == 0) {
        throw new Exception("@todo EMPTY_INPUT_FILE");
      }

      list ($input_width, $input_height, $input_mime, $input_extension) =
        photo::get_file_metadata($input_file);
      if ($input_width && $input_height &&
          (empty($options["width"]) || empty($options["height"]) || empty($options["master"]) ||
          (max($input_width, $input_height) <= min($options["width"], $options["height"])))) {
        // Photo dimensions well-defined, but options not well-defined or would upscale the image.
        // Do not resize.  Check mimes to see if we can copy the file or if we need to convert it.
        // (checking mimes avoids needlessly converting jpg to jpeg, etc.)
        $output_mime = legal_file::get_photo_types_by_extension(pathinfo($output_file, PATHINFO_EXTENSION));
        if ($input_mime && $output_mime && ($input_mime == $output_mime)) {
          // Mimes well-defined and identical - copy input to output
          copy($input_file, $output_file);
        } else {
          // Mimes not well-defined or not the same - convert input to output
          $image = Image::factory($input_file)
            ->quality(module::get_var("gallery", "image_quality"))
            ->save($output_file);
        }
      } else {
        // Resize the image.  This also implicitly converts its format if needed.
        $image = Image::factory($input_file)
          ->resize($options["width"], $options["height"], $options["master"])
          ->quality(module::get_var("gallery", "image_quality"));
        if (graphics::can("sharpen")) {
          $image->sharpen(module::get_var("gallery", "image_sharpen"));
        }
        $image->save($output_file);
      }
    }

    module::event("graphics_resize_completed", $input_file, $output_file, $options, $item);
  }

  /**
   * Overlay an image on top of the input file.
   *
   * Valid options are: file, position, transparency, padding
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
   * @param Item_Model $item (optional)
   */
  static function composite($input_file, $output_file, $options, $item=null) {
    try {
      graphics::init_toolkit();

      $temp_file = system::temp_filename("composite_", pathinfo($output_file, PATHINFO_EXTENSION));
      module::event("graphics_composite", $input_file, $temp_file, $options, $item);

      if (@filesize($temp_file) > 0) {
        // A graphics_composite event made an image - move it to output_file and use it.
        @rename($temp_file, $output_file);
      } else {
        // No events made an image - proceed with standard process.

        list ($width, $height) = photo::get_file_metadata($input_file);
        list ($w_width, $w_height) = photo::get_file_metadata($options["file"]);

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
      }

      module::event("graphics_composite_completed", $input_file, $output_file, $options, $item);
    } catch (ErrorException $e) {
      // Unlike rotate and resize, composite catches its exceptions here.  This is because
      // composite is typically called for watermarks.  If during thumb/resize generation
      // the watermark fails, we'd still like the image resized, just without its watermark.
      // If the exception isn't caught here, graphics::generate will replace it with a
      // placeholder.
      Kohana_Log::add("error", $e->getMessage());
    }
  }
}
