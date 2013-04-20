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
 * Proxy access to files in var/albums and var/resizes, making sure that the session user has
 * access to view these files.
 *
 * Security Philosophy: we do not use the information provided to find if the file exists on
 * disk.  We use this information only to locate the correct item in the database and then we
 * *only* use information from the database to find and proxy the correct file.  This way all user
 * input is sanitized against the database before we perform any file I/O.
 */
class Gallery_Controller_FileProxy extends Controller {
  public $allow_private_gallery = true;

  public function action_index() {
    // Force zlib compression off.  Image and movie files are already compressed and
    // recompressing them is CPU intensive.
    if (ini_get("zlib.output_compression")) {
      ini_set("zlib.output_compression", "Off");
    }

    // type: subdir in var (e.g. "albums")
    // path: relative path in subdir (e.g. "Bobs Wedding/Eating-Cake.jpg")
    $path = rawurldecode($this->request->param("path"));
    $type = $this->request->param("type");

    // See if we have a valid type.  This also catches the case where the user navigated directly
    // to this controller (e.g. "/gallery3/index.php/file_proxy/index/foo.jpg"), in which case
    // type would be empty.
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 2;
      throw $e;
    }

    // Get the item model using the path and type (which corresponds to a var subdir)
    $item = Item::find_by_path($path, $type);

    if (!$item->loaded()) {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 3;
      throw $e;
    }

    // Make sure we have access to the item
    if (!Access::can("view", $item)) {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 4;
      throw $e;
    }

    // Make sure we have view_full access to the original
    if ($type == "albums" && !Access::can("view_full", $item)) {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 5;
      throw $e;
    }

    // Don't try to load a directory
    if ($type == "albums" && $item->is_album()) {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 6;
      throw $e;
    }

    // Note: this code is roughly duplicated in Hook_Rest_Data, so if you modify this, please look to
    // see if you should make the same change there as well.

    if ($type == "albums") {
      $file = $item->file_path();
    } else if ($type == "resizes") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    if (!file_exists($file)) {
      $e = HTTP_Exception::factory(404);
      $e->test_fail_code = 7;
      throw $e;
    }

    if (Gallery::show_profiler()) {
      Profiler::enable();
      $profiler = new Profiler();
      $profiler->render();
      exit;
    }

    // Set the filemtime as the etag (same as cache buster), use to check if cache needs refreshing.
    $this->check_cache(filemtime($file));

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    // Dump out the image.  If the item is a movie or album, then its thumbnail will be a JPG.
    if (($item->is_movie() || $item->is_album()) && $type == "thumbs") {
      $mime_type = "image/jpeg";
    } else {
      $mime_type = $item->mime_type;
    }

    if (TEST_MODE) {
      return $file;
    } else {
      // Send the file as the response.  The filename will be set automatically from the path.
      // Note: send_file() will automatically halt script execution after sending the file.
      $this->response->send_file($file, null,
                                 array("inline" => "true", "mime_type" => $mime_type));
    }
  }
}
