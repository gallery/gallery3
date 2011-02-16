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

    // If the last element is .album.jpg, pop that off since it's not a real item
    $path = preg_replace("|/.album.jpg$|", "", $path);

    $item = item::find_by_path($path);
    if (!$item->loaded()) {
      // We didn't turn it up. If we're looking for a .jpg then it's it's possible that we're
      // requesting the thumbnail for a movie.  In that case, the .flv, .mp4 or .m4v file would
      // have been converted to a .jpg. So try some alternate types:
      if (preg_match('/.jpg$/', $path)) {
        foreach (array("flv", "mp4", "m4v") as $ext) {
          $movie_path = preg_replace('/.jpg$/', ".$ext", $path);
          $item = item::find_by_path($movie_path);
          if ($item->loaded()) {
            break;
          }
        }
      }
    }

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
    if ($type == "albums" && $item->is_album()) {
      throw new Kohana_404_Exception();
    }

    if ($type == "albums") {
      $file = $item->file_path();
    } else if ($type == "resizes") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    if (!file_exists($file)) {
      throw new Kohana_404_Exception();
    }

    header("Content-Length: " . filesize($file));

    header("Pragma:");
    // Check that the content hasn't expired or it wasn't changed since cached
    expires::check(2592000, $item->updated);

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    expires::set(2592000, $item->updated);  // 30 days

    // Dump out the image.  If the item is a movie, then its thumbnail will be a JPG.
    if ($item->is_movie() && $type != "albums") {
      header("Content-Type: image/jpeg");
    } else {
      header("Content-Type: $item->mime_type");
    }
    Kohana::close_buffers(false);
    readfile($file);
  }
}
