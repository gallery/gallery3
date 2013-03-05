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
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 1;
      throw $e;
    }

    // file_uri: albums/foo/bar.jpg
    $file_uri = substr($request_uri, strlen($var_uri));

    // type: albums
    // path: foo/bar.jpg
    list ($type, $path) = explode("/", $file_uri, 2);
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 2;
      throw $e;
    }

    // If the last element is .album.jpg, pop that off since it's not a real item
    $path = preg_replace("|/.album.jpg$|", "", $path);

    $item = item::find_by_path($path);
    if (!$item->loaded()) {
      // We didn't turn it up. If we're looking for a .jpg then it's it's possible that we're
      // requesting the thumbnail for a movie.  In that case, the movie file would
      // have been converted to a .jpg. So try some alternate types:
      if (preg_match('/.jpg$/', $path)) {
        foreach (legal_file::get_movie_extensions() as $ext) {
          $movie_path = preg_replace('/.jpg$/', ".$ext", $path);
          $item = item::find_by_path($movie_path);
          if ($item->loaded()) {
            break;
          }
        }
      }
    }

    if (!$item->loaded()) {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 3;
      throw $e;
    }

    // Make sure we have access to the item
    if (!access::can("view", $item)) {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 4;
      throw $e;
    }

    // Make sure we have view_full access to the original
    if ($type == "albums" && !access::can("view_full", $item)) {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 5;
      throw $e;
    }

    // Don't try to load a directory
    if ($type == "albums" && $item->is_album()) {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 6;
      throw $e;
    }

    // Note: this code is roughly duplicated in data_rest, so if you modify this, please look to
    // see if you should make the same change there as well.

    if ($type == "albums") {
      $file = $item->file_path();
    } else if ($type == "resizes") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    if (!file_exists($file)) {
      $e = new Kohana_404_Exception();
      $e->test_fail_code = 7;
      throw $e;
    }

    if (gallery::show_profiler()) {
      Profiler::enable();
      $profiler = new Profiler();
      $profiler->render();
      exit;
    }

    header("Content-Length: " . filesize($file));

    header("Pragma:");
    // Check that the content hasn't expired or it wasn't changed since cached
    expires::check(2592000, $item->updated);

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    expires::set(2592000, $item->updated);  // 30 days

    // Dump out the image.  If the item is a movie or album, then its thumbnail will be a JPG.
    if (($item->is_movie() || $item->is_album()) && $type == "thumbs") {
      header("Content-Type: image/jpeg");
    } else {
      header("Content-Type: $item->mime_type");
    }

    if (TEST_MODE) {
      return $file;
    } else {
      // Don't use Kohana::close_buffers(false) here because that only closes all the buffers
      // that Kohana started.  We want to close *all* buffers at this point because otherwise we're
      // going to buffer up whatever file we're proxying (and it may be very large).  This may
      // affect embedding or systems with PHP's output_buffering enabled.
      while (ob_get_level()) {
        if (!@ob_end_clean()) {
          // ob_end_clean() can return false if the buffer can't be removed for some reason
          // (zlib output compression buffers sometimes cause problems).
          break;
        }
      }
      readfile($file);
    }
  }
}
