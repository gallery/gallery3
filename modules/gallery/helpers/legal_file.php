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
   * Create a default list of allowed photo MIME types paired with their extensions and then let 
   * modules modify it.  This is an ordered map, mapping extensions to their MIME types.
   * Extensions cannot be duplicated, but MIMEs can (e.g. jpeg and jpg both map to image/jpeg).
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_photo_types_by_extension($extension=NULL) {
    $types_by_extension_wrapper = new stdClass();
    $types_by_extension_wrapper->types_by_extension = array(
      "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    module::event("photo_types_by_extension", $types_by_extension_wrapper);
    if ($extension) {
      // return matching MIME type
      return $types_by_extension_wrapper->types_by_extension[strtolower($extension)];
    } else {
      // return complete array
      return $types_by_extension_wrapper->types_by_extension;
    }
  }

  /**
   * Create a default list of allowed movie MIME types paired with their extensions and then let
   * modules modify it.  This is an ordered map, mapping extensions to their MIME types.
   * Extensions cannot be duplicated, but MIMEs can (e.g. jpeg and jpg both map to image/jpeg).
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_movie_types_by_extension($extension=NULL) {
    $types_by_extension_wrapper = new stdClass();
    $types_by_extension_wrapper->types_by_extension = array(
      "flv" => "video/x-flv", "mp4" => "video/mp4", "m4v" => "video/x-m4v");
    module::event("movie_types_by_extension", $types_by_extension_wrapper);
    if ($extension) {
      // return matching MIME type
      return $types_by_extension_wrapper->types_by_extension[strtolower($extension)];
    } else {
      // return complete array
      return $types_by_extension_wrapper->types_by_extension;
    }
  }

  /**
   * Create a merged list of all allowed photo and movie MIME types paired with their extensions.
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_types_by_extension($extension=NULL) {
    if ($extension) {
      // return matching MIME type
      if ($photo_mime = legal_file::get_photo_types_by_extension($extention)) {
        return $photo_mime;
      } else {
        return legal_file::get_movie_types_by_extension($extention);
      }
    } else {
      // return complete array
      return array_merge(legal_file::get_photo_types_by_extension(),
                         legal_file::get_movie_types_by_extension());
    }
  }

   /**
   * Create a default list of allowed photo extensions and then let modules modify it.
   *
   * @param string $extension (opt.) - return TRUE if allowed, FALSE if not
   */
  static function get_photo_extensions($extension=NULL) {
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array_keys(legal_file::get_photo_types_by_extension());
    module::event("legal_photo_extensions", $extensions_wrapper);
    if ($extension) {
      // return TRUE if in array, FALSE if not
      return in_array(strtolower($extension), $extensions_wrapper->extensions);
    } else {
      // return complete array
      return $extensions_wrapper->extensions;
    }
  }

  /**
   * Create a default list of allowed movie extensions and then let modules modify it.
   *
   * @param string $extension (opt.) - return TRUE if allowed, FALSE if not
   */
  static function get_movie_extensions($extension=NULL) {
    $extensions_wrapper = new stdClass();
    $extensions_wrapper->extensions = array_keys(legal_file::get_movie_types_by_extension());
    module::event("legal_movie_extensions", $extensions_wrapper);
    if ($extension) {
      // return TRUE if in array, FALSE if not
      return in_array(strtolower($extension), $extensions_wrapper->extensions);
    } else {
      // return complete array
      return $extensions_wrapper->extensions;
    }
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
   * Can be used to add legal alternatives for default MIME types.
   * (e.g. flv maps to video/x-flv by default, but video/flv is still legal).
   */
  static function get_photo_types() {
    $types_wrapper = new stdClass();
    $types_wrapper->types = array_values(legal_file::get_photo_types_by_extension());
    module::event("legal_photo_types", $types_wrapper);
    return $types_wrapper->types;
  }

  /**
   * Create a default list of allowed movie MIME types and then let modules modify it.
   * Can be used to add legal alternatives for default MIME types.
   * (e.g. flv maps to video/x-flv by default, but video/flv is still legal).
   */
  static function get_movie_types() {
    $types_wrapper = new stdClass();
    $types_wrapper->types = array_values(legal_file::get_movie_types_by_extension());
    $types_wrapper->types[] = "video/flv";
    module::event("legal_movie_types", $types_wrapper);
    return $types_wrapper->types;
  }

  /**
   * Change the extension of a filename.  If the original filename has no
   * extension, add the new one to the end.  Filename can be a bare filename, a
   * full path, or a URL provided it has no query.
   */
  static function change_extension($filename, $new_ext) {
    $pi = pathinfo($filename);
    if ($pi["filename"] && isset($pi["extension"])) {
      // We have a defined extension (includes filenames that end in a dot) and a non-empty filename.
      // Replace the old extension with the new.
      return substr($filename, 0, -strlen($pi["extension"])) . $new_ext;
    } else {
      // We have either no extension or an empty filename (which can happen with filenames like
      // ".jpg" or ".album").  Add the new extension.
      return "{$filename}.{$new_ext}";
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
