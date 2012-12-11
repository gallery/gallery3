<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
  static function get_edit_form($movie) {
    $form = new Forge("movies/update/$movie->id", "", "post", array("id" => "g-edit-movie-form"));
    $form->hidden("from_id")->value($movie->id);
    $group = $form->group("edit_item")->label(t("Edit Movie"));
    $group->input("title")->label(t("Title"))->value($movie->title)
      ->error_messages("required", t("You must provide a title"))
      ->error_messages("length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($movie->description);
    $group->input("name")->label(t("Filename"))->value($movie->name)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The movie name can't contain a \"/\""))
      ->error_messages("no_trailing_period", t("The movie name can't end in \".\""))
      ->error_messages("illegal_data_file_extension", t("You cannot change the movie file extension"))
      ->error_messages("required", t("You must provide a movie file name"))
      ->error_messages("length", t("Your movie file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($movie->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("required", t("You must provide an internet address"))
      ->error_messages("length", t("Your internet address is too long"));

    module::event("item_edit_form", $movie, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));

    return $form;
  }

  static function extract_frame($input_file, $output_file) {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }

    list($width, $height, $mime_type, $extension, $duration) = movie::get_file_metadata($input_file);

    // extract frame at 0:03, unless movie is shorter than 4 sec.
    $start_time_arg = ($duration > 4) ? " -ss 00:00:03" : "";

    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($input_file) .
      " -an" . $start_time_arg . " -an -r 1 -vframes 1" .
      " -s " . $width . "x" . $height .
      " -y -f mjpeg " . escapeshellarg($output_file) . " 2>&1";
    exec($cmd, $exec_output, $exec_return);

    clearstatcache();  // use $filename parameter when PHP_version is 5.3+
    if (filesize($output_file) == 0 || $exec_return) {
      // Maybe the movie needs the "-threads 1" argument added (see http://sourceforge.net/apps/trac/gallery/ticket/1924)
      $cmd = escapeshellcmd($ffmpeg) . " -threads 1 -i " . escapeshellarg($input_file) .
        " -an" . $start_time_arg . " -an -r 1 -vframes 1" .
        " -s " . $width . "x" . $height .
        " -y -f mjpeg " . escapeshellarg($output_file) . " 2>&1";
      exec($cmd, $exec_output, $exec_return);

      clearstatcache();
      if (filesize($output_file) == 0 || $exec_return) {
        throw new Exception("@todo FFMPEG_FAILED");
      }
    }
  }

  /**
   * Return the path to the ffmpeg binary if one exists and is executable, or null.
   */
  static function find_ffmpeg() {
    if (!($ffmpeg_path = module::get_var("gallery", "ffmpeg_path")) || !file_exists($ffmpeg_path)) {
      $ffmpeg_path = system::find_binary(
        "ffmpeg", module::get_var("gallery", "graphics_toolkit_path"));
      module::set_var("gallery", "ffmpeg_path", $ffmpeg_path);
    }
    return $ffmpeg_path;
  }

  /**
   * Return the width, height, mime_type, extension and duration of the given movie file.
   */
  static function get_file_metadata($file_path) {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }

    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($file_path) . " 2>&1";
    $result = `$cmd`;
    if (preg_match("/Stream.*?Video:.*?, (\d+)x(\d+)/", $result, $res)) {
      if (preg_match("/Stream.*?Video:.*? \[.*?DAR (\d+):(\d+).*?\]/", $result, $dar)) {
        // DAR is defined - determine width based on height and DAR (should always be int, but adding round to be sure)
        $res[1] = round($res[2] * $dar[1] / $dar[2]);
      }
      list ($width, $height) = array($res[1], $res[2]);
    } else {
      list ($width, $height) = array(0, 0);
    }

    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $extension = $extension ? $extension : "flv"; // No extension?  Assume FLV.
    $mime_type = legal_file::get_movie_types_by_extension($extension);
    $mime_type = $mime_type ? $mime_type : "video/x-flv"; // No MIME found?  Assign video/x-flv to mimic behavior of v3.0.4 and older.

    if (preg_match("/Duration: (\d+):(\d+):(\d+\.\d+)/", $result, $regs)) {
      $duration = 3600 * $regs[1] + 60 * $regs[2] + $regs[3];
    } else if (preg_match("/duration.*?:.*?(\d+)/", $result, $regs)) {
      $duration = $regs[1];
    } else {
      $duration = 0;
    }

    return array($width, $height, $mime_type, $extension, $duration);
  }

}
