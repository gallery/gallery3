<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
    $type = array_shift($paths);
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      kohana::show_404();
    }

    // Walk down from the root until we find the item that matches this path
    $item = ORM::factory("item", 1);
    while ($path = array_shift($paths)) {
      $item = ORM::factory("item")
        ->where("name", $path)
        ->where("level", $item->level + 1)
        ->where("parent_id", $item->id)
        ->find();
      if (!$item->loaded) {
        kohana::show_404();
      }

      // If the last element is _album.jpg then we're done.
      if (count($paths) == 1 && $paths[0] == "_album.jpg") {
        break;
      }
    }

    // Make sure we have access to the item
    if (!access::can("view", $item)) {
      kohana::show_404();
    }

    if ($type == "albums") {
      if ($item->is_album()) {
        kohana::show_404();
      }
      $path = $item->file_path();
    } else if ($type == "resizes") {
      $path = $item->resize_path();
    } else {
      $path = $item->thumb_path();
    }

    if (!file_exists($path)) {
      kohana::show_404();
    }

    // Dump out the image
    header("Content-Type: $item->mime_type");
    Kohana::close_buffers(false);
    $fd = fopen($path, "rb");
    fpassthru($fd);
    fclose($fd);
  }
}
