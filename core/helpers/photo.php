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

/**
 * This is the API for handling photos.
 *
 * Note: by design, this class does not do any permission checking.
 */
class photo_Core {
  /**
   * Create a new photo.
   * @param integer $parent_id id of parent album
   * @param string  $filename path to the photo file on disk
   * @param string  $name the filename to use for this photo in the album
   * @param integer $title the title of the new photo
   * @param string  $description (optional) the longer description of this photo
   * @return Item_Model
   */
  static function create($parent_id, $filename, $name, $title, $description=null, $owner_id=null) {
    if (!is_file($filename)) {
      throw new Exception("@todo MISSING_IMAGE_FILE");
    }

    if (!($image_info = getimagesize($filename))) {
      throw new Exception("@todo INVALID_IMAGE_FILE");
    }

    // Force an extension onto the name
    $pi = pathinfo($name);
    if (empty($pi["extension"])) {
      $pi["extension"] = image_type_to_extension($image_info[2], false);
      $name .= "." . $pi["extension"];
    }

    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->title = $title;
    $photo->description = $description;
    $photo->name = $name;
    $photo->owner_id = $owner_id;
    $photo->width = $image_info[0];
    $photo->height = $image_info[1];
    $photo->mime_type = empty($image_info['mime']) ? "application/unknown" : $image_info['mime'];

    // Randomize the name if there's a conflict
    while (ORM::Factory("item")
           ->where("parent_id", $parent_id)
           ->where("name", $photo->name)
           ->find()->id) {
      // @todo Improve this.  Random numbers are not user friendly
      $photo->name = rand() . "." . $pi["extension"];
    }

    // This saves the photo
    $parent = ORM::factory("item", $parent_id);
    if (!$parent->loaded) {
      throw new Exception("@todo INVALID_PARENT_ID");
    }

    $photo->add_to_parent($parent);
    copy($filename, $photo->file_path());

    // @todo: parameterize these dimensions
    // This saves the photo a second time, which is unfortunate but difficult to avoid.
    $result = $photo->set_thumbnail($filename, 200, 200)
      ->set_resize($filename, 640, 640)
      ->save();

    module::event("photo_created", $photo);

    return $result;
  }

  static function get_add_form($parent) {
    $form = new Forge("albums/{$parent->id}", "", "post",
      array("id" => "gAddPhotoForm", "enctype" => "multipart/form-data"));
    $group = $form->group(sprintf(_("Add Photo to %s"), $parent->title));
    $group->input("name")->label(true);
    $group->input("title")->label(true);
    $group->textarea("description")->label(true)->rules("length[0, 255");
    $group->upload("file")->label(true)->rules("allow[jpg,png,gif,tiff]");
    $group->hidden("type")->value("photo");
    $group->submit(_("Upload"));
    $form->add_rules_from(ORM::factory("item"));
    return $form;
  }

}
