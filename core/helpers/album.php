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
 * This is the API for handling albums.
 *
 * Note: by design, this class does not do any permission checking.
 */
class Album_Core {
  /**
   * Create a new album.
   * @param integer $parent_id id of parent album
   * @param string  $name the name of this new album (it will become the directory name on disk)
   * @param integer $title the title of the new album
   * @param string  $description (optional) the longer description of this album
   * @return Item_Model
   */
  static function create($parent_id, $name, $title, $description=null) {
    $album = ORM::factory("item");
    $album->type = "album";
    $album->title = $title;
    $album->description = $description;
    $album->name = $name;

    while (ORM::Factory("item")
           ->where("parent_id", $parent_id)
           ->where("name", $album->name)
           ->find()->id) {
      $album->name = "{$name}-" . rand();
    }

    $album = $album->add_to_parent($parent_id);
    mkdir($album->path());
    mkdir($album->thumbnail_path());
    return $album;
  }
}
