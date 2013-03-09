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
class system_Core {
  private static $files_marked_for_deletion = array();

  /**
   * Return the path to an executable version of the named binary, or null.
   * The paths are traversed in the following order:
   *   1. $priority_path (if specified)
   *   2. Gallery's own bin directory (DOCROOT . "bin")
   *   3. PATH environment variable
   *   4. extra_binary_paths Gallery variable (if specified)
   * In addition, if the file is found inside Gallery's bin directory but
   * it's not executable, we try to change its permissions to 0755.
   *
   * @param  string $binary
   * @param  string $priority_path (optional)
   * @return string path to binary if found; null if not found
   */
  static function find_binary($binary, $priority_path=null) {
    $bin_path = DOCROOT . "bin";

    if ($priority_path) {
      $paths = array($priority_path, $bin_path);
    } else {
      $paths = array($bin_path);
    }
    $paths = array_merge($paths,
      explode(":", getenv("PATH")),
      explode(":", module::get_var("gallery", "extra_binary_paths")));

    foreach ($paths as $path) {
      $path = rtrim($path, "/");
      $candidate = "$path/$binary";
      // @suppress errors below to avoid open_basedir issues
      if (@file_exists($candidate)) {
        if (!@is_executable($candidate) &&
            (substr_compare($bin_path, $candidate, 0, strlen($bin_path)) == 0)) {
          // Binary isn't executable but is in Gallery's bin directory - try fixing permissions.
          @chmod($candidate, 0755);
        }
        if (@is_executable($candidate)) {
          return $candidate;
        }
      }
    }
    return null;
  }

  /**
   * Create a file with a unique file name.
   * This helper is similar to the built-in tempnam.
   * It allows the caller to specify a prefix and an extension.
   * It always places the file in TMPPATH.
   * Unless specified with the $delete_later argument, it will be marked
   * for deletion at shutdown using system::delete_later.
   */
  static function temp_filename($prefix="", $extension="", $delete_later=true) {
    do {
      $basename = tempnam(TMPPATH, $prefix);
      if (!$basename) {
        return false;
      }
      $filename = "$basename.$extension";
      $success = !file_exists($filename) && @rename($basename, $filename);
      if (!$success) {
        @unlink($basename);
      }
    } while (!$success);

    if ($delete_later) {
      system::delete_later($filename);
    }

    return $filename;
  }

  /**
   * Mark a file for deletion at shutdown time.  This is useful for temp files, where we can delay
   * the deletion time until shutdown to keep page load time quick.
   */
  static function delete_later($filename) {
    self::$files_marked_for_deletion[] = $filename;
  }

  /**
   * Delete all files marked using system::delete_later.  This is called at gallery shutdown.
   */
  static function delete_marked_files() {
    foreach (self::$files_marked_for_deletion as $filename) {
      // We want to suppress all errors, as it's possible that some of these
      // files may have been deleted/moved before we got here.
      @unlink($filename);
    }
  }
}
