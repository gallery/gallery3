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
class Photo_Core {
  /**
   * Create a new photo.
   * @param integer $parent_id id of parent album
   * @param string  $filename path to the photo file on disk
   * @param string  $name the filename to use for this photo in the album
   * @param integer $title the title of the new photo
   * @param string  $description (optional) the longer description of this photo
   * @return Item_Model
   */
  static function create($parent_id, $filename, $name, $title, $description=null, $owner_id = null) {
    $photo = ORM::factory("item");
    $photo->type = "photo";
    $photo->title = $title;
    $photo->description = $description;
    $photo->name = $name;
    $photo->owner_id = $owner_id;

    $pi = pathinfo(basename($filename));
    if (empty($pi["extension"])) {
      throw new Exception("@todo UNKNOWN_FILE_TYPE");
    }

    while (ORM::Factory("item")
           ->where("parent_id", $parent_id)
           ->where("name", $photo->name)
           ->find()->id) {
      $photo->name = rand() . "." . $pi["extension"];
    }

    copy($filename, $photo->file_path());

    // This saves the photo
    $photo->add_to_parent($parent_id);

    /** @todo: parameterize these dimensions */
    // This saves the photo a second time, which is unfortunate but difficult to avoid.
    return $photo->set_thumbnail($filename, 200, 140)
      ->set_resize($filename, 800, 600)
      ->save();
  }
}
