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
    if ($type != "resizes" && $type != "albums") {
      kohana::show_404();
    }

    // Pull the last item off of the list, explode it out to get the "resize" or "thumb" tag, then
    // put it back together without that tag.  This will give us the matching item name.
    $exploded_last = explode(".", array_pop($paths));
    $extension = array_pop($exploded_last);
    $image_type = array_pop($exploded_last);
    if ($image_type != "resize" && $image_type != "thumb") {
      kohana::show_404();
    }
    array_push($exploded_last, $extension);
    array_push($paths, implode(".", $exploded_last));

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

      // Try to detect when we're asking for an album thumbnail or resize.  In that case, the
      // second to last element will be an album and the last element will be .thumb.jpg or
      // .resize.jpg except we'll have stripped the .thumb and .resize parts so it'll just be .jpg
      if ($item->type == "album" && count($paths) == 1 &&
          $paths[0][0] == '.' && strlen($paths[0]) == 4) {
        break;
      }
    }

    // Make sure we have access to the item
    if (!access::can("view", $item)) {
      kohana::show_404();
    }

    $path = $image_type == "thumb" ? $item->thumbnail_path() : $item->resize_path();
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
