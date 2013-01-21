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

  /**
   * Extract a frame from a movie file.  Valid movie_options are start_time (in seconds),
   * input_args (extra ffmpeg input args) and output_args (extra ffmpeg output args).  Extra args
   * are added at the end of the list, so they can override any prior args.
   *
   * @param string     $input_file
   * @param string     $output_file
   * @param array      $movie_options (optional)
   */
  static function extract_frame($input_file, $output_file, $movie_options=null) {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }

    list($width, $height, $mime_type, $extension, $duration) = movie::get_file_metadata($input_file);

    if (isset($movie_options["start_time"]) && is_numeric($movie_options["start_time"])) {
      $start_time = max(0, $movie_options["start_time"]); // ensure it's non-negative
    } else {
      $start_time = module::get_var("gallery", "movie_extract_frame_time", 3); // use default
    }
    // extract frame at start_time, unless movie is too short
    $start_time_arg = ($duration >= $start_time + 0.1) ?
      "-ss " . movie::seconds_to_hhmmssdd($start_time) : "";
      
    $input_args = isset($movie_options["input_args"]) ? $movie_options["input_args"] : "";
    $output_args = isset($movie_options["output_args"]) ? $movie_options["output_args"] : "";

    $cmd = escapeshellcmd($ffmpeg) . " $input_args -i " . escapeshellarg($input_file) .
      " -an $start_time_arg -an -r 1 -vframes 1" .
      " -s {$width}x{$height}" .
      " -y -f mjpeg $output_args " . escapeshellarg($output_file) . " 2>&1";
    exec($cmd, $exec_output, $exec_return);

    clearstatcache();  // use $filename parameter when PHP_version is 5.3+
    if (filesize($output_file) == 0 || $exec_return) {
      // Maybe the movie needs the "-threads 1" argument added
      // (see http://sourceforge.net/apps/trac/gallery/ticket/1924)
      $cmd = escapeshellcmd($ffmpeg) . " -threads 1 $input_args -i " . escapeshellarg($input_file) .
        " -an $start_time_arg -an -r 1 -vframes 1" .
        " -s {$width}x{$height}" .
        " -y -f mjpeg $output_args " . escapeshellarg($output_file) . " 2>&1";
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
    if (preg_match("/Stream.*?Video:.*?, (\d+)x(\d+)/", $result, $matches_res)) {
      if (preg_match("/Stream.*?Video:.*? \[.*?DAR (\d+):(\d+).*?\]/", $result, $matches_dar) &&
          $matches_dar[1] >= 1 && $matches_dar[2] >= 1) {
        // DAR is defined - determine width based on height and DAR
        // (should always be int, but adding round to be sure)
        $matches_res[1] = round($matches_res[2] * $matches_dar[1] / $matches_dar[2]);
      }
      list ($width, $height) = array($matches_res[1], $matches_res[2]);
    } else {
      list ($width, $height) = array(0, 0);
    }

    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $extension = $extension ? $extension : "flv"; // No extension?  Assume FLV.
    $mime_type = legal_file::get_movie_types_by_extension($extension);
    $mime_type = $mime_type ? $mime_type : "video/x-flv"; // No MIME found?  Default to video/x-flv.

    if (preg_match("/Duration: (\d+:\d+:\d+\.\d+)/", $result, $matches)) {
      $duration = movie::hhmmssdd_to_seconds($matches[1]);
    } else if (preg_match("/duration.*?:.*?(\d+)/", $result, $matches)) {
      $duration = $matches[1];
    } else {
      $duration = 0;
    }

    return array($width, $height, $mime_type, $extension, $duration);
  }

  /**
   * Return the time/duration formatted in hh:mm:ss.dd from a number of seconds.
   * Useful for inputs to ffmpeg.
   *
   * Note that this is similar to date("H:i:s", mktime(0,0,$seconds,0,0,0,0)), but unlike this 
   * approach avoids potential issues with time zone and DST mismatch and/or using deprecated
   * features (the last argument of mkdate above, which disables DST, is deprecated as of PHP 5.3).
   */
  static function seconds_to_hhmmssdd($seconds) {
    return sprintf("%02d:%02d:%05.2f", floor($seconds / 3600), floor(($seconds % 3600) / 60),
                   floor(100 * $seconds % 6000) / 100);
  }
  
  /**
   * Return the number of seconds from a time/duration formatted in hh:mm:ss.dd.
   * Useful for outputs from ffmpeg.
   */
  static function hhmmssdd_to_seconds($hhmmssdd) {
    preg_match("/(\d+):(\d+):(\d+\.\d+)/", $hhmmssdd, $matches);
    return 3600 * $matches[1] + 60 * $matches[2] + $matches[3];
  }
}
