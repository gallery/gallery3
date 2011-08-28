<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
  /**
   * Return the path to an executable version of the named binary, or null.
   * Traverse the PATH environment variable looking for the given file.  If
   * the $priority_path variable is set, check that path first.
   */
  static function find_binary($binary, $priority_path=null) {
    $paths = array_merge(
      explode(":", getenv("PATH")),
      explode(":", module::get_var("gallery", "extra_binary_paths")));
    if ($priority_path) {
      array_unshift($paths, $priority_path);
    }

    foreach ($paths as $path) {
      $candidate = "$path/$binary";
      // @suppress errors below to avoid open_basedir issues
      if (@file_exists($candidate) && @is_executable($candidate)) {
        return $candidate;
      }
    }
    return null;
  }

  /**
   * Create a file with a unique file name.
   * This helper is similar to the built-in tempnam.
   * It allows the caller to specify a prefix and an extension.
   * It always places the file in TMPPATH.
   */
  static function temp_filename($prefix="", $extension="") {
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
    return $filename;
  }
}