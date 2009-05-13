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

    // var_uri: http://example.com/gallery3/var/
    $var_uri = url::file("var/");

    // Make sure that the request is for a file inside var
    $offset = strpos($request_uri, $var_uri);
    if ($offset === false) {
      kohana::show_404();
    }

    $file = substr($request_uri, strlen($var_uri));

    // Make sure that we don't leave the var dir
    if (strpos($file, "..") !== false) {
      kohana::show_404();
    }

    // We only handle var/resizes and var/albums
    $paths = explode("/", $file);
    $type = $paths[0];
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      kohana::show_404();
    }

    // If the last element is .album.jpg, pop that off since it's not a real item
    if ($paths[count($paths)-1] == ".album.jpg") {
      array_pop($paths);
    }
    if ($paths[count($paths)-1] == "") {
      array_pop($paths);
    }

    // Find all items that match the level and name, then iterate over those to find a match.
    // In most cases we'll get it in one.  Note that for the level calculation, we just count the
    // size of $paths.  $paths includes the type ("thumbs", etc) but it doesn't include the root,
    // so it's a wash.
    $count = count($paths);
    $compare_file = VARPATH . $file;
    $item = null;
    foreach (ORM::factory("item")
             ->where("name", $paths[$count - 1])
             ->where("level", $count)
             ->find_all() as $match) {
      if ($type == "albums") {
        $match_file = $match->file_path();
      } else if ($type == "resizes") {
        $match_file = $match->resize_path();
      } else {
        $match_file = $match->thumb_path();
      }
      if ($match_file == $compare_file) {
        $item = $match;
        break;
      }
    }

    if (!$item) {
      kohana::show_404();
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

    if (!file_exists($match_file)) {
      kohana::show_404();
    }

    // Dump out the image
    header("Content-Type: $item->mime_type");
    Kohana::close_buffers(false);
    $fd = fopen($match_file, "rb");
    fpassthru($fd);
    fclose($fd);
  }
}
