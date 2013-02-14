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
class photo_Core {
  static function get_edit_form($photo) {
    $form = new Forge("photos/update/$photo->id", "", "post", array("id" => "g-edit-photo-form"));
    $form->hidden("from_id")->value($photo->id);
    $group = $form->group("edit_item")->label(t("Edit Photo"));
    $group->input("title")->label(t("Title"))->value($photo->title)
      ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($photo->description);
    $group->input("name")->label(t("Filename"))->value($photo->name)
      ->error_messages("conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The photo name can't contain a \"/\""))
      ->error_messages("no_trailing_period", t("The photo name can't end in \".\""))
      ->error_messages("illegal_data_file_extension", t("You cannot change the photo file extension"))
      ->error_messages("required", t("You must provide a photo file name"))
      ->error_messages("length", t("Your photo file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($photo->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("required", t("You must provide an internet address"))
      ->error_messages("length", t("Your internet address is too long"));

    module::event("item_edit_form", $photo, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));
    return $form;
  }

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
   * This function and its use cases are symmetric to those of photo::get_file_metadata.
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
      throw new Exception("@todo UNREADABLE_FILE");
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
          (!$metadata->mime_type = legal_file::get_photo_types_by_extension($extension))) {
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
    module::event("photo_get_file_metadata", $file_path, $metadata);

    // If the post-events results are invalid, throw an exception.
    if (!$metadata->width || !$metadata->height || !$metadata->mime_type || !$metadata->extension ||
        ($metadata->mime_type != legal_file::get_photo_types_by_extension($metadata->extension))) {
      throw new Exception("@todo ILLEGAL_OR_UNINDENTIFIABLE_FILE");
    }

    return array($metadata->width, $metadata->height, $metadata->mime_type, $metadata->extension);
  }
}
