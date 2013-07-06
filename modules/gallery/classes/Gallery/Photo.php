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

/**
 * This is the API for handling photos.
 *
 * Note: by design, this class does not do any permission checking.
 */
class Gallery_Photo {
  // IPTC tag names.
  // @see  Photo::get_file_iptc()
  static $iptc_tags = array(
    "2#005" => "ObjectName",
    "2#015" => "Category",
    "2#020" => "Supplementals",
    "2#025" => "Keywords",
    "2#040" => "SpecialsInstructions",
    "2#055" => "DateCreated",
    "2#060" => "TimeCreated",
    "2#062" => "DigitalCreationDate",
    "2#063" => "DigitalCreationTime",
    "2#080" => "ByLine",
    "2#085" => "ByLineTitle",
    "2#090" => "City",
    "2#092" => "Sublocation",
    "2#095" => "ProvinceState",
    "2#100" => "CountryCode",
    "2#101" => "CountryName",
    "2#105" => "Headline",
    "2#110" => "Credits",
    "2#115" => "Source",
    "2#116" => "Copyright",
    "2#118" => "Contact",
    "2#120" => "Caption",
    "2#122" => "CaptionWriter"
  );

  /**
   * Return scaled width and height.
   *
   * @param integer $width
   * @param integer $height
   * @param integer $max    the target size for the largest dimension
   * @param string  $format the output format using %d placeholders for width and height
   */
  static function img_dimensions($width, $height, $max, $format="width=\"%d\" height=\"%d\"") {
    if (!$width || !$height) {
      return "";
    }

    if ($width > $height) {
      $new_width = $max;
      $new_height = (int)$max * ($height / $width);
    } else {
      $new_height = $max;
      $new_width = (int)$max * ($width / $height);
    }
    return sprintf($format, $new_width, $new_height);
  }

  /**
   * Return the width, height, mime_type and extension of the given image file.
   * Metadata is first generated using getimagesize (or the legal_file mapping if it fails),
   * then can be modified by other modules using photo_get_file_metadata events.
   *
   * This function and its use cases are symmetric to those of Photo::get_file_metadata.
   *
   * @param  string $file_path
   * @return array  array($width, $height, $mime_type, $extension)
   *
   * Use cases in detail:
   *   Input is standard photo type (jpg/png/gif)
   *     -> return metadata from getimagesize()
   *   Input is *not* standard photo type that is supported by getimagesize (e.g. tif, bmp...)
   *     -> return metadata from getimagesize()
   *   Input is *not* standard photo type that is *not* supported by getimagesize but is legal
   *     -> return metadata if found by photo_get_file_metadata events
   *   Input is illegal, unidentifiable, unreadable, or does not exist
   *     -> throw exception
   * Note: photo_get_file_metadata events can change any of the above cases (except the last one).
   */
  static function get_file_metadata($file_path) {
    if (!is_readable($file_path)) {
      throw new Gallery_Exception("Unreadable file");
    }

    $metadata = new stdClass();
    if ($image_info = getimagesize($file_path)) {
      // getimagesize worked - use its results.
      $metadata->width = $image_info[0];
      $metadata->height = $image_info[1];
      $metadata->mime_type = $image_info["mime"];
      $metadata->extension = image_type_to_extension($image_info[2], false);
      // We prefer jpg instead of jpeg (which is returned by image_type_to_extension).
      if ($metadata->extension == "jpeg") {
        $metadata->extension = "jpg";
      }
    } else {
      // getimagesize failed - try to use legal_file mapping instead.
      $extension = pathinfo($file_path, PATHINFO_EXTENSION);
      if (!$extension ||
          (!$metadata->mime_type = LegalFile::get_photo_types_by_extension($extension))) {
        // Extension is empty or illegal.
        $metadata->extension = null;
        $metadata->mime_type = null;
      } else {
        // Extension is legal (and mime is already set above).
        $metadata->extension = strtolower($extension);
      }
      $metadata->width = 0;
      $metadata->height = 0;
    }

    // Run photo_get_file_metadata events which can modify the class.
    Module::event("photo_get_file_metadata", $file_path, $metadata);

    // If the post-events results are invalid, throw an exception.
    if (!$metadata->width || !$metadata->height || !$metadata->mime_type || !$metadata->extension ||
        ($metadata->mime_type != LegalFile::get_photo_types_by_extension($metadata->extension))) {
      throw new Gallery_Exception("Illegal or unindentifiable file");
    }

    return array($metadata->width, $metadata->height, $metadata->mime_type, $metadata->extension);
  }

  /**
   * Return the IPTC data of the given image file.  This parses the IPTC using the definitions
   * in Photo::$iptc_tags, and returns an array like:
   *   array("Keywords" => array("foo,bar", "baz"), "Caption" => array("Hello World!"))
   * @todo  Build function like Photo::get_file_xmp(), too.
   *
   * @param   string  file path
   * @return  array   array of IPTC data
   */
  static function get_file_iptc($file_path) {
    if (!is_readable($file_path)) {
      throw new Gallery_Exception("Unreadable file");
    }

    $metadata_image_info = getimagesize($file_path, $image_info);
    if (!$metadata_image_info || !$image_info ||
        !is_array($image_info) || empty($image_info["APP13"])) {
      // Not necessarily an error - it's possible that we have a legal file that
      // cannot be read by getimagesize() and/or has no IPTC data.  Return an empty array.
      return array();
    }

    $data = iptcparse($image_info["APP13"]);

    $values = array();
    foreach (static::$iptc_tags as $code => $name) {
      $value = Arr::get($data, $code);
      if (isset($value)) {
        $value = str_replace("\0",  "", $value);
        $values[$name] = Encoding::convert_to_utf8($value);
      }
    }

    return $values;
  }
}
