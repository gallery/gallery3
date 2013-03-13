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
  private static $allow_uploads;

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
   * Return true if movie uploads are allowed, false if not.  This is based on the
   * "movie_allow_uploads" Gallery variable as well as whether or not ffmpeg is found.
   */
  static function allow_uploads() {
    if (empty(self::$allow_uploads)) {
      // Refresh ffmpeg settings
      $ffmpeg = movie::find_ffmpeg();
      switch (module::get_var("gallery", "movie_allow_uploads", "autodetect")) {
        case "always":
          self::$allow_uploads = true;
          break;
        case "never":
          self::$allow_uploads = false;
          break;
        default:
          self::$allow_uploads = !empty($ffmpeg);
          break;
      }
    }
    return self::$allow_uploads;
  }

  /**
   * Return the path to the ffmpeg binary if one exists and is executable, or null.
   */
  static function find_ffmpeg() {
    if (!($ffmpeg_path = module::get_var("gallery", "ffmpeg_path")) ||
        !@is_executable($ffmpeg_path)) {
      $ffmpeg_path = system::find_binary(
        "ffmpeg", module::get_var("gallery", "graphics_toolkit_path"));
      module::set_var("gallery", "ffmpeg_path", $ffmpeg_path);
    }
    return $ffmpeg_path;
  }

  /**
   * Return version number and build date of ffmpeg if found, empty string(s) if not.  When using
   * static builds that aren't official releases, the version numbers are strange, hence why the
   * date can be useful.
   */
  static function get_ffmpeg_version() {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      return array("", "");
    }

    // Find version using -h argument since -version wasn't available in early versions.
    // To keep the preg_match searches quick, we'll trim the (otherwise long) result.
    $cmd = escapeshellcmd($ffmpeg) . " -h 2>&1";
    $result = substr(`$cmd`, 0, 1000);
    if (preg_match("/ffmpeg version (\S+)/i", $result, $matches_version)) {
      // Version number found - see if we can get the build date or copyright year as well.
      if (preg_match("/built on (\S+\s\S+\s\S+)/i", $result, $matches_build_date)) {
        return array(trim($matches_version[1], ","), trim($matches_build_date[1], ","));
      } else if (preg_match("/copyright \S*\s?2000-(\d{4})/i", $result, $matches_copyright_date)) {
        return array(trim($matches_version[1], ","), $matches_copyright_date[1]);
      } else {
        return array(trim($matches_version[1], ","), "");
      }
    }
    return array("", "");
  }

  /**
   * Return the width, height, mime_type, extension and duration of the given movie file.
   * Metadata is first generated using ffmpeg (or set to defaults if it fails),
   * then can be modified by other modules using movie_get_file_metadata events.
   *
   * This function and its use cases are symmetric to those of photo::get_file_metadata.
   *
   * @param  string $file_path
   * @return array  array($width, $height, $mime_type, $extension, $duration)
   *
   * Use cases in detail:
   *   Input is standard movie type (flv/mp4/m4v)
   *     -> return metadata from ffmpeg
   *   Input is *not* standard movie type that is supported by ffmpeg (e.g. avi, mts...)
   *     -> return metadata from ffmpeg
   *   Input is *not* standard movie type that is *not* supported by ffmpeg but is legal
   *     -> return zero width, height, and duration; mime type and extension according to legal_file
   *   Input is illegal, unidentifiable, unreadable, or does not exist
   *     -> throw exception
   * Note: movie_get_file_metadata events can change any of the above cases (except the last one).
   */
  static function get_file_metadata($file_path) {
    if (!is_readable($file_path)) {
      throw new Exception("@todo UNREADABLE_FILE");
    }

    $metadata = new stdClass();
    $ffmpeg = movie::find_ffmpeg();
    if (!empty($ffmpeg)) {
      // ffmpeg found - use it to get width, height, and duration.
      $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($file_path) . " 2>&1";
      $result = `$cmd`;
      if (preg_match("/Stream.*?Video:.*?, (\d+)x(\d+)/", $result, $matches_res)) {
        if (preg_match("/Stream.*?Video:.*? \[.*?DAR (\d+):(\d+).*?\]/", $result, $matches_dar) &&
            $matches_dar[1] >= 1 && $matches_dar[2] >= 1) {
          // DAR is defined - determine width based on height and DAR
          // (should always be int, but adding round to be sure)
          $matches_res[1] = round($matches_res[2] * $matches_dar[1] / $matches_dar[2]);
        }
        list ($metadata->width, $metadata->height) = array($matches_res[1], $matches_res[2]);
      } else {
        list ($metadata->width, $metadata->height) = array(0, 0);
      }

      if (preg_match("/Duration: (\d+:\d+:\d+\.\d+)/", $result, $matches)) {
        $metadata->duration = movie::hhmmssdd_to_seconds($matches[1]);
      } else if (preg_match("/duration.*?:.*?(\d+)/", $result, $matches)) {
        $metadata->duration = $matches[1];
      } else {
        $metadata->duration = 0;
      }
    } else {
      // ffmpeg not found - set width, height, and duration to zero.
      $metadata->width = 0;
      $metadata->height = 0;
      $metadata->duration = 0;
    }

    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
    if (!$extension ||
        (!$metadata->mime_type = legal_file::get_movie_types_by_extension($extension))) {
        // Extension is empty or illegal.
        $metadata->extension = null;
        $metadata->mime_type = null;
    } else {
      // Extension is legal (and mime is already set above).
      $metadata->extension = strtolower($extension);
    }

    // Run movie_get_file_metadata events which can modify the class.
    module::event("movie_get_file_metadata", $file_path, $metadata);

    // If the post-events results are invalid, throw an exception.  Note that, unlike photos, having
    // zero width and height isn't considered invalid (as is the case when FFmpeg isn't installed).
    if (!$metadata->mime_type || !$metadata->extension ||
        ($metadata->mime_type != legal_file::get_movie_types_by_extension($metadata->extension))) {
      throw new Exception("@todo ILLEGAL_OR_UNINDENTIFIABLE_FILE");
    }

    return array($metadata->width, $metadata->height, $metadata->mime_type,
                 $metadata->extension, $metadata->duration);
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
