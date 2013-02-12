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
class legal_file_Core {
  private static $photo_types_by_extension;
  private static $movie_types_by_extension;
  private static $photo_extensions;
  private static $movie_extensions;
  private static $photo_types;
  private static $movie_types;
  private static $blacklist = array("php", "php3", "php4", "php5", "phtml", "phtm", "shtml", "shtm",
                                    "pl", "cgi", "asp", "sh", "py", "c", "js");

  /**
   * Create a default list of allowed photo MIME types paired with their extensions and then let
   * modules modify it.  This is an ordered map, mapping extensions to their MIME types.
   * Extensions cannot be duplicated, but MIMEs can (e.g. jpeg and jpg both map to image/jpeg).
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_photo_types_by_extension($extension=null) {
    if (empty(self::$photo_types_by_extension)) {
      $types_by_extension_wrapper = new stdClass();
      $types_by_extension_wrapper->types_by_extension = array(
        "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
      module::event("photo_types_by_extension", $types_by_extension_wrapper);
      foreach (self::$blacklist as $key) {
        unset($types_by_extension_wrapper->types_by_extension[$key]);
      }
      self::$photo_types_by_extension = $types_by_extension_wrapper->types_by_extension;
    }
    if ($extension) {
      // return matching MIME type
      $extension = strtolower($extension);
      if (isset(self::$photo_types_by_extension[$extension])) {
        return self::$photo_types_by_extension[$extension];
      } else {
        return null;
      }
    } else {
      // return complete array
      return self::$photo_types_by_extension;
    }
  }

  /**
   * Create a default list of allowed movie MIME types paired with their extensions and then let
   * modules modify it.  This is an ordered map, mapping extensions to their MIME types.
   * Extensions cannot be duplicated, but MIMEs can (e.g. jpeg and jpg both map to image/jpeg).
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_movie_types_by_extension($extension=null) {
    if (empty(self::$movie_types_by_extension)) {
      $types_by_extension_wrapper = new stdClass();
      $types_by_extension_wrapper->types_by_extension = array(
        "flv" => "video/x-flv", "mp4" => "video/mp4", "m4v" => "video/x-m4v");
      module::event("movie_types_by_extension", $types_by_extension_wrapper);
      foreach (self::$blacklist as $key) {
        unset($types_by_extension_wrapper->types_by_extension[$key]);
      }
      self::$movie_types_by_extension = $types_by_extension_wrapper->types_by_extension;
    }
    if ($extension) {
      // return matching MIME type
      $extension = strtolower($extension);
      if (isset(self::$movie_types_by_extension[$extension])) {
        return self::$movie_types_by_extension[$extension];
      } else {
        return null;
      }
    } else {
      // return complete array
      return self::$movie_types_by_extension;
    }
  }

  /**
   * Create a merged list of all allowed photo and movie MIME types paired with their extensions.
   *
   * @param string $extension (opt.) - return MIME of extension; if not given, return complete array
   */
  static function get_types_by_extension($extension=null) {
    $types_by_extension = legal_file::get_photo_types_by_extension();
    if (movie::allow_uploads()) {
      $types_by_extension = array_merge($types_by_extension,
                                        legal_file::get_movie_types_by_extension());
    }
    if ($extension) {
      // return matching MIME type
      $extension = strtolower($extension);
      if (isset($types_by_extension[$extension])) {
        return $types_by_extension[$extension];
      } else {
        return null;
      }
    } else {
      // return complete array
      return $types_by_extension;
    }
  }

  /**
   * Create a default list of allowed photo extensions and then let modules modify it.
   *
   * @param string $extension (opt.) - return true if allowed; if not given, return complete array
   */
  static function get_photo_extensions($extension=null) {
    if (empty(self::$photo_extensions)) {
      $extensions_wrapper = new stdClass();
      $extensions_wrapper->extensions = array_keys(legal_file::get_photo_types_by_extension());
      module::event("legal_photo_extensions", $extensions_wrapper);
      self::$photo_extensions = array_diff($extensions_wrapper->extensions, self::$blacklist);
    }
    if ($extension) {
      // return true if in array, false if not
      return in_array(strtolower($extension), self::$photo_extensions);
    } else {
      // return complete array
      return self::$photo_extensions;
    }
  }

  /**
   * Create a default list of allowed movie extensions and then let modules modify it.
   *
   * @param string $extension (opt.) - return true if allowed; if not given, return complete array
   */
  static function get_movie_extensions($extension=null) {
    if (empty(self::$movie_extensions)) {
      $extensions_wrapper = new stdClass();
      $extensions_wrapper->extensions = array_keys(legal_file::get_movie_types_by_extension());
      module::event("legal_movie_extensions", $extensions_wrapper);
      self::$movie_extensions = array_diff($extensions_wrapper->extensions, self::$blacklist);
    }
    if ($extension) {
      // return true if in array, false if not
      return in_array(strtolower($extension), self::$movie_extensions);
    } else {
      // return complete array
      return self::$movie_extensions;
    }
  }

  /**
   * Create a merged list of all allowed photo and movie extensions.
   *
   * @param string $extension (opt.) - return true if allowed; if not given, return complete array
   */
  static function get_extensions($extension=null) {
    $extensions = legal_file::get_photo_extensions();
    if (movie::allow_uploads()) {
      $extensions = array_merge($extensions, legal_file::get_movie_extensions());
    }
    if ($extension) {
      // return true if in array, false if not
      return in_array(strtolower($extension), $extensions);
    } else {
      // return complete array
      return $extensions;
    }
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
    if (empty(self::$photo_types)) {
      $types_wrapper = new stdClass();
      // Need array_unique since types_by_extension can be many-to-one (e.g. jpeg and jpg).
      $types_wrapper->types = array_unique(array_values(legal_file::get_photo_types_by_extension()));
      module::event("legal_photo_types", $types_wrapper);
      self::$photo_types = $types_wrapper->types;
    }
    return self::$photo_types;
  }

  /**
   * Create a default list of allowed movie MIME types and then let modules modify it.
   * Can be used to add legal alternatives for default MIME types.
   * (e.g. flv maps to video/x-flv by default, but video/flv is still legal).
   */
  static function get_movie_types() {
    if (empty(self::$movie_types)) {
      $types_wrapper = new stdClass();
      // Need array_unique since types_by_extension can be many-to-one (e.g. jpeg and jpg).
      $types_wrapper->types = array_unique(array_values(legal_file::get_movie_types_by_extension()));
      $types_wrapper->types[] = "video/flv";
      module::event("legal_movie_types", $types_wrapper);
      self::$movie_types = $types_wrapper->types;
    }
    return self::$movie_types;
  }

  /**
   * Change the extension of a filename.  If the original filename has no
   * extension, add the new one to the end.
   */
  static function change_extension($filename, $new_ext) {
    $filename_no_ext = preg_replace("/\.[^\.\/]*?$/", "", $filename);
    return "{$filename_no_ext}.{$new_ext}";
  }

  /**
   * Reduce the given file to having a single extension.
   */
  static function smash_extensions($filename) {
    if (!$filename) {
      // It's harmless, so return it before it causes issues with pathinfo.
      return $filename;
    }
    $parts = pathinfo($filename);
    $result = "";
    if ($parts["dirname"] != ".") {
      $result .= $parts["dirname"] . "/";
    }
    $parts["filename"] = str_replace(".", "_", $parts["filename"]);
    $parts["filename"] = preg_replace("/[_]+/", "_", $parts["filename"]);
    $parts["filename"] = trim($parts["filename"], "_");
    $result .= isset($parts["extension"]) ? "{$parts['filename']}.{$parts['extension']}" : $parts["filename"];
    return $result;
  }

  /**
   * Sanitize a filename for a given type (given as "photo" or "movie") and a target file format
   * (given as an extension).  This returns a completely legal and valid filename,
   * or throws an exception if the type or extension given is invalid or illegal.  It tries to
   * maintain the filename's original extension even if it's not identical to the given extension
   * (e.g. don't change "JPG" or "jpeg" to "jpg").
   *
   * Note: it is not okay if the extension given is legal but does not match the type (e.g. if
   * extension is "mp4" and type is "photo", it will throw an exception)
   *
   * @param  string $filename  (with no directory)
   * @param  string $extension (can be uppercase or lowercase)
   * @param  string $type      (as "photo" or "movie")
   * @return string sanitized filename (or null if bad extension argument)
   */
  static function sanitize_filename($filename, $extension, $type) {
    // Check if the type is valid - if so, get the mime types of the
    // original and target extensions; if not, throw an exception.
    $original_extension = pathinfo($filename, PATHINFO_EXTENSION);
    switch ($type) {
      case "photo":
        $mime_type = legal_file::get_photo_types_by_extension($extension);
        $original_mime_type = legal_file::get_photo_types_by_extension($original_extension);
        break;
      case "movie":
        $mime_type = legal_file::get_movie_types_by_extension($extension);
        $original_mime_type = legal_file::get_movie_types_by_extension($original_extension);
        break;
      default:
        throw new Exception("@todo INVALID_TYPE");
    }

    // Check if the target extension is blank or invalid - if so, throw an exception.
    if (!$extension || !$mime_type) {
      throw new Exception("@todo ILLEGAL_EXTENSION");
    }

    // Check if the mime types of the original and target extensions match - if not, fix it.
    if (!$original_extension || ($mime_type != $original_mime_type)) {
      $filename = legal_file::change_extension($filename, $extension);
    }

    // It should be a filename without a directory - remove all slashes (and backslashes).
    $filename = str_replace("/", "_", $filename);
    $filename = str_replace("\\", "_", $filename);

    // Remove extra dots from the filename.  This will also remove extraneous underscores.
    $filename = legal_file::smash_extensions($filename);

    // It's possible that the filename has no base (e.g. ".jpg") - if so, give it a generic one.
    if (empty($filename) || (substr($filename, 0, 1) == ".")) {
      $filename = $type . $filename;  // e.g. "photo.jpg" or "movie.mp4"
    }

    return $filename;
  }
}
