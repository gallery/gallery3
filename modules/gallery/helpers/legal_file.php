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
class legal_file_Core {
  /**
   * Create a default list of allowed photo extensions and then let modules modify it.
   */
  static function get_photo_extensions() {
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array("gif", "jpg", "jpeg", "png");
    module::event("legal_photo_extensions", $extensions_wrapper);
    return $extensions_wrapper->extensions;
  }

  /**
   * Create a default list of allowed movie extensions and then let modules modify it.
   */
  static function get_movie_extensions() {
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array("flv", "mp4", "m4v");
    module::event("legal_movie_extensions", $extensions_wrapper);
    return $extensions_wrapper->extensions;
  }

  /**
   * Create a merged list of all allowed photo and movie extensions.
   */
  static function get_extensions() {
    $extensions = legal_file::get_photo_extensions();
    if (movie::find_ffmpeg()) {
      $extensions = array_merge($extensions, legal_file::get_movie_extensions());
    }
    return $extensions;
  }

  /**
   * Create a merged list of all photo and movie filename filters,
   * (e.g. "*.gif"), based on allowed extensions.
   */
  static function get_filters() {
    $filters = array();
    foreach (legal_file::get_extensions() as $extension) {
      array_push($filters, "*." . $extension, "*." . strtoupper($extension));
    }
    return $filters;
  }

  /**
   * Create a default list of allowed photo MIME types and then let modules modify it.
   */
  static function get_photo_types() {
    $types_wrapper = new stdClass();
    $types_wrapper->types = array("image/jpeg", "image/gif", "image/png");
    module::event("legal_photo_types", $types_wrapper);
    return $types_wrapper->types;
  }

  /**
   * Create a default list of allowed movie MIME types and then let modules modify it.
   */
  static function get_movie_types() {
    $types_wrapper = new stdClass();
    $types_wrapper->types = array("video/flv", "video/x-flv", "video/mp4");
    module::event("legal_movie_types", $types_wrapper);
    return $types_wrapper->types;
  }

  /**
   * Convert the extension of a filename.  If the original filename has no
   * extension, add the new one to the end.
   */
  static function change_extension($filename, $new_ext) {
    if (strpos($filename, ".") === false) {
      return "{$filename}.{$new_ext}";
    } else {
      return preg_replace("/\.[^\.]*?$/", ".{$new_ext}", $filename);
    }
  }

  /**
   * Reduce the given file to having a single extension.
   */
  static function smash_extensions($filename) {
    $parts = pathinfo($filename);
    $result = "";
    if ($parts["dirname"] != ".") {
      $result .= $parts["dirname"] . "/";
    }
    $parts["filename"] = str_replace(".", "_", $parts["filename"]);
    $parts["filename"] = preg_replace("/[_]+/", "_", $parts["filename"]);
    $parts["filename"] = trim($parts["filename"], "_");
    $result .= "{$parts['filename']}.{$parts['extension']}";
    return $result;
  }
}
