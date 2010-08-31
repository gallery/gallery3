<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
   */
  static function get_file_metadata($file_path) {
    $image_info = getimagesize($file_path);
    $width = $image_info[0];
    $height = $image_info[1];
    $mime_type = $image_info["mime"];
    $extension = image_type_to_extension($image_info[2], false);
    return array($width, $height, $mime_type, $extension);
  }
}
