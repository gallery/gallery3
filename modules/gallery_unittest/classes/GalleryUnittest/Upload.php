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
 * These are hacked versions of Kohana's Upload::save() and Upload::not_empty() that bypass
 * security.  We use them in unit testing to allow us to fake file uploads (e.g. Watermarks)
 * without PHP's is_uploaded_file() and move_uploaded_file() correctly identifying that
 * they're invalid.  By design, these functions are quite secure and, consequently, do not
 * have any simple workarounds.
 *
 * For clarity, below are verbatim copies of the original functions with the relevant pieces
 * commented out (identified with "// EDIT:..."), which still use Kohana-style formatting.
 */
if (md5_file(SYSPATH . "classes/Kohana/Upload.php") != "034760b30518689559940009961d4e3f") {
  throw new Exception(
    "Kohana Upload has changed - new checksum: " . md5_file(SYSPATH . "classes/Kohana/Upload.php"));
}

class GalleryUnittest_Upload extends Kohana_Upload {
  /**
   * @see Upload::save()
   */
  public static function save(array $file, $filename = NULL, $directory = NULL, $chmod = 0644)
  {
    if (!TEST_MODE) {
      throw new Kohana_Exception();
    }

    // if ( ! isset($file['tmp_name']) OR ! is_uploaded_file($file['tmp_name']))
    if ( ! isset($file['tmp_name'])) // EDIT: replaces line above
    {
      // Ignore corrupted uploads
      return FALSE;
    }

    if ($filename === NULL)
    {
      // Use the default filename, with a timestamp pre-pended
      $filename = uniqid().$file['name'];
    }

    if (Upload::$remove_spaces === TRUE)
    {
      // Remove spaces from the filename
      $filename = preg_replace('/\s+/u', '_', $filename);
    }

    if ($directory === NULL)
    {
      // Use the pre-configured upload directory
      $directory = Upload::$default_directory;
    }

    if ( ! is_dir($directory) OR ! is_writable(realpath($directory)))
    {
      throw new Kohana_Exception('Directory :dir must be writable',
        array(':dir' => Debug::path($directory)));
    }

    // Make the filename into a complete path
    $filename = realpath($directory).DIRECTORY_SEPARATOR.$filename;

    // if (move_uploaded_file($file['tmp_name'], $filename))
    if (rename($file['tmp_name'], $filename)) // EDIT: replaces line above
    {
      if ($chmod !== FALSE)
      {
        // Set permissions on filename
        chmod($filename, $chmod);
      }

      // Return new file path
      return $filename;
    }

    return FALSE;
  }

  /**
   * @see Upload::not_empty()
   */
  public static function not_empty(array $file)
  {
    return (isset($file['error'])
      AND isset($file['tmp_name'])
      AND $file['error'] === UPLOAD_ERR_OK
      // AND is_uploaded_file($file['tmp_name']));
      ); // EDIT: replaces line above
  }
}
