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
 * This is the API for handling movies.
 *
 * Note: by design, this class does not do any permission checking.
 */
class movie_Core {
  /**
   * Create a new movie.
   * @param integer $parent_id id of parent album
   * @param string  $filename path to the photo file on disk
   * @param string  $name the filename to use for this photo in the album
   * @param integer $title the title of the new photo
   * @param string  $description (optional) the longer description of this photo
   * @return Item_Model
   */
  static function create($parent, $filename, $name, $title,
                         $description=null, $owner_id=null) {
    if (!$parent->loaded || !$parent->is_album()) {
      throw new Exception("@todo INVALID_PARENT");
    }

    if (!is_file($filename)) {
      throw new Exception("@todo MISSING_MOVIE_FILE");
    }

    $movie_info = movie::getmoviesize($filename);

    // Force an extension onto the name
    $pi = pathinfo($name);
    if (empty($pi["extension"])) {
      $pi["extension"] = image_type_to_extension($movie_info[2], false);
      $name .= "." . $pi["extension"];
    }

    $movie = ORM::factory("item");
    $movie->type = "movie";
    $movie->title = $title;
    $movie->description = $description;
    $movie->name = $name;
    $movie->owner_id = $owner_id;
    $movie->width = $movie_info[0];
    $movie->height = $movie_info[1];
    $movie->mime_type = "video/x-flv";
    $movie->thumb_dirty = 1;
    $movie->resize_dirty = 1;

    // Randomize the name if there's a conflict
    while (ORM::Factory("item")
           ->where("parent_id", $parent->id)
           ->where("name", $movie->name)
           ->find()->id) {
      // @todo Improve this.  Random numbers are not user friendly
      $movie->name = rand() . "." . $pi["extension"];
    }

    // This saves the photo
    $movie->add_to_parent($parent);
    copy($filename, $movie->file_path());

    module::event("item_created", $movie);

    // Build our thumbnail
    graphics::generate($movie);

    // If the parent has no cover item, make this it.
    $parent = $movie->parent();
    if ($parent->album_cover_item_id == null)  {
      $parent->album_cover_item_id = $movie->id;
      $parent->save();
      graphics::generate($parent);
    }

    return $movie;
  }

  static function getmoviesize($filename) {
    if (!$ffmpeg = exec("which ffmpeg")) {
      throw new Exception("@todo MISSING_FFMPEG");
    }
    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($filename) . " 2>&1";
    $result = `$cmd`;
    if (preg_match("/Stream.*?Video:.*?(\d+)x(\d+).*\ +([0-9\.]+) (fps|tb).*/",
                   $result, $regs)) {
      list ($width, $height) = array($regs[1], $regs[2]);
    } else {
      list ($width, $height) = array(0, 0);
    }
    return array($width, $height);
  }

  function extract_frame($input_file, $output_file) {
    if (!$ffmpeg = exec("which ffmpeg")) {
      throw new Exception("@todo MISSING_FFMPEG");
    }

    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($input_file) .
      " -t 0.001 -y -f mjpeg " . escapeshellarg($output_file);
    exec($cmd);
  }
}
