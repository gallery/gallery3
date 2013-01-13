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
/**
 * Proxy access to files in var/albums and var/resizes, making sure that the session user has
 * access to view these files.
 *
 * Security Philosophy: we do not use the information provided to find if the file exists on
 * disk.  We use this information only to locate the correct item in the database and then we
 * *only* use information from the database to find and proxy the correct file.  This way all user
 * input is sanitized against the database before we perform any file I/O.
 */
class File_Proxy_Controller extends Controller {
  const ALLOW_PRIVATE_GALLERY = true;
  public function __call($function, $args) {

    // Force zlib compression off.  Image and movie files are already compressed and
    // recompressing them is CPU intensive.
    if (ini_get("zlib.output_compression")) {
      ini_set("zlib.output_compression", "Off");
    }

    // request_uri: gallery3/var/albums/foo/bar.jpg?m=1234
    $request_uri = rawurldecode(Input::instance()->server("REQUEST_URI"));

    // get rid of query parameters
    // request_uri: gallery3/var/albums/foo/bar.jpg
    $request_uri = preg_replace("/\?.*/", "", $request_uri);

    // var_uri: gallery3/var/
    $var_uri = url::file("var/");

    // Make sure that the request is for a file inside var
    $offset = strpos(rawurldecode($request_uri), $var_uri);
    if ($offset !== 0) {
      throw new Kohana_404_Exception();
    }

    // file_uri: albums/foo/bar.jpg
    $file_uri = substr($request_uri, strlen($var_uri));

    // type: albums
    // path: foo/bar.jpg
    list ($type, $path) = explode("/", $file_uri, 2);
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      throw new Kohana_404_Exception();
    }

    // Find our item by its path
    $item = item::find_by_path($path, $type);

    // Make sure we found something
    if (!$item->loaded()) {
      throw new Kohana_404_Exception();
    }

    // Make sure we have access to the item
    if (!access::can("view", $item)) {
      throw new Kohana_404_Exception();
    }

    // Make sure we have view_full access to the original
    if ($type == "albums" && !access::can("view_full", $item)) {
      throw new Kohana_404_Exception();
    }

    // Don't try to load a directory
    if ($type != "thumbs" && $item->is_album()) {
      throw new Kohana_404_Exception();
    }

    // Note: this code is roughly duplicated in data_rest, so if you modify this, please look to
    // see if you should make the same change there as well.

    // Find the file path and mime type
    switch ($type) {
    case "albums":
      $file = $item->file_path();
      $mime = $item->mime_type;
      break;
    case "resizes":
      $file = $item->resize_path();
      $mime = legal_file::get_photo_types_by_extension($item->resize_extension);
      break;
    case "thumbs":
      $file = $item->thumb_path();
      $mime = legal_file::get_photo_types_by_extension($item->thumb_extension);
      break;
    }

    // Make sure the file exists and its mime is defined
    if (!file_exists($file) || !$mime) {
      throw new Kohana_404_Exception();
    }

    // We're all set - let's dump the image
    
    header("Content-Length: " . filesize($file));

    header("Pragma:");
    // Check that the content hasn't expired or it wasn't changed since cached
    expires::check(2592000, $item->updated);

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    expires::set(2592000, $item->updated);  // 30 days

    // Dump out the image
    header("Content-Type: $mime");

    // Don't use Kohana::close_buffers(false) here because that only closes all the buffers
    // that Kohana started.  We want to close *all* buffers at this point because otherwise we're
    // going to buffer up whatever file we're proxying (and it may be very large).  This may
    // affect embedding or systems with PHP's output_buffering enabled.
    while (ob_get_level()) {
      Kohana_Log::add("error","".print_r(ob_get_level(),1));
      if (!@ob_end_clean()) {
        // ob_end_clean() can return false if the buffer can't be removed for some reason
        // (zlib output compression buffers sometimes cause problems).
        break;
      }
    }

    readfile($file);
  }
}
