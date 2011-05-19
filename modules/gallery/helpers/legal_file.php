<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2011 Chad Parry
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
  static function get_photo_extensions() {
    // Create a default list of allowed extensions and then let modules modify it.
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array("gif", "jpg", "jpeg", "png");
    module::event("legal_photo_extensions", $extensions_wrapper);
    return $extensions_wrapper->extensions;
  }

  static function get_movie_extensions() {
    // Create a default list of allowed extensions and then let modules modify it.
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array("flv", "mp4", "m4v");
    module::event("legal_movie_extensions", $extensions_wrapper);
    return $extensions_wrapper->extensions;
  }

  static function get_extensions() {
    $extensions = legal_file::get_photo_extensions();
    if (movie::find_ffmpeg()) {
      array_push($extensions, legal_file::get_movie_extensions());
    }
    return $extensions;
  }

  static function get_filters() {
    $filters = array();
    foreach (legal_file::get_extensions() as $extension) {
      array_push($filters, "*." . $extension, "*." . strtoupper($extension));
    }
    return $filters;
  }

  static function get_photo_types() {
    // Create a default list of allowed types and then let modules modify it.
    $types_wrapper = new stdClass();
    module::event("legal_photo_types", $types_wrapper);
    $types_wrapper->types = array("image/jpeg", "image/gif", "image/png");
  }

  static function get_movie_types() {
    // Create a default list of allowed types and then let modules modify it.
    $types_wrapper = new stdClass();
    $types_wrapper->types = array("video/flv", "video/x-flv", "video/mp4");
    module::event("legal_movie_types", $types_wrapper);
    return $types_wrapper->types;
  }
}
