<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
  public function __call($function, $args) {
    // request_uri: http://example.com/gallery3/var/trunk/albums/foo/bar.jpg
    $request_uri = $this->input->server("REQUEST_URI");
    $request_uri = preg_replace("/\?.*/", "", $request_uri);

    // Unescape %7E (~), %20 ( ) and %27 (')
    // @todo: figure out why we have to do this and unescape everything appropriate
    $request_uri = str_replace(array("%7E", "%20", "%27"), array("~", " ", "'"), $request_uri);

    // var_uri: http://example.com/gallery3/var/
    $var_uri = url::file("var/");

    // Make sure that the request is for a file inside var
    $offset = strpos($request_uri, $var_uri);
    if ($offset === false) {
      kohana::show_404();
    }

    $file_uri = substr($request_uri, strlen($var_uri));

    // Make sure that we don't leave the var dir
    if (strpos($file_uri, "..") !== false) {
      kohana::show_404();
    }

    list ($type, $path) = explode("/", $file_uri, 2);
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      kohana::show_404();
    }

    // If the last element is .album.jpg, pop that off since it's not a real item
    $path = preg_replace("|/.album.jpg$|", "", $path);

    // We now have the relative path to the item.  Search for it in the path cache
    $item = ORM::factory("item")->where("relative_path_cache", $path)->find();
    if (!$item->loaded) {
      // We didn't turn it up.  It's possible that the relative_path_cache is out of date here.
      // There was fallback code, but bharat deleted it in 8f1bca74.  If it turns out to be
      // necessary, it's easily resurrected.

      // If we're looking for a .jpg then it's it's possible that we're requesting the thumbnail
      // for a movie.  In that case, the .flv or .mp4 file would have been converted to a .jpg.
      // So try some alternate types:
      if (preg_match('/.jpg$/', $path)) {
        foreach (array("flv", "mp4") as $ext) {
          $movie_path = preg_replace('/.jpg$/', ".$ext", $path);
          $item = ORM::factory("item")->where("relative_path_cache", $movie_path)->find();
          if ($item->loaded) {
            break;
          }
        }
      }
    }

    if (!$item->loaded) {
      kohana::show_404();
    }

    if ($type == "albums") {
      $file = $item->file_path();
    } else if ($type == "resizes") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    // Make sure we have access to the item
    if (!access::can("view", $item)) {
      kohana::show_404();
    }

    // Make sure we have view_full access to the original
    if ($type == "albums" && !access::can("view_full", $item)) {
      kohana::show_404();
    }

    // Don't try to load a directory
    if ($type == "albums" && $item->is_album()) {
      kohana::show_404();
    }

    if (!file_exists($file)) {
      kohana::show_404();
    }

    // We don't need to save the session for this request
    Session::abort_save();

    // Dump out the image.  If the item is a movie, then its thumbnail will be a JPG.
    if (in_array($item->mime_type, array("video/x-flv", "video/mp4"))) {
      header("Content-type: image/jpeg");
    } else {
      header("Content-Type: $item->mime_type");
    }

    Kohana::close_buffers(false);
    $fd = fopen($file, "rb");
    fpassthru($fd);
    fclose($fd);
  }
}
