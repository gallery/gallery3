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
    // We don't need to save the session for this request
    Session::instance()->abort_save();

    // type: subdir in var (e.g. "albums")
    // path: relative path in subdir (e.g. "Bobs Wedding/Eating-Cake.jpg")
    $path = rawurldecode($this->request->param("path"));
    $type = $this->request->param("type");

    // See if we have a valid type.  This also catches the case where the user navigated directly
    // to this controller (e.g. "/gallery3/index.php/file_proxy/index/foo.jpg"), in which case
    // type would be empty.
    if ($type != "resizes" && $type != "albums" && $type != "thumbs") {
      $code = $type ? 2 : 1;  // 1 is no type at all, 2 is invalid type
      // For security purposes, we do not leak the error code unless we're in TEST_MODE.
      // Otherwise, navigating to "/gallery3/var/albums/image_to_steal.jpg" could produce
      // an error screen with something like "Kohana_HTTP_Exception [ 404 ]: 3".  This
      // applies to the other 5 instances like this below, too.
      throw HTTP_Exception::factory(404, TEST_MODE ? $code : null);
    }

    // Get the item model using the path and type (which corresponds to a var subdir)
    $item = Item::find_by_path($path, $type);

    $this->action_show($item, $type);
  }

  public function action_show($item=null, $type=null, $encoding=null) {
    // If we got here from FileProxy::action_find(), $item and $type would already be set.
    if (!isset($item) || !isset($type)) {
      if ($this->request->is_initial()) {
        // No external access allowed.
        throw HTTP_Exception::factory(404);
      } else {
        // Got here internally (e.g. from REST Data resource) - use passed query parameters
        $item      = $this->request->query("item");
        $type      = $this->request->query("type");
        $encoding  = $this->request->query("encoding");
      }
    }

    if (!$item->loaded()) {
      throw HTTP_Exception::factory(404, TEST_MODE ? 3 : null);
    }

    // Make sure we have access to the item
    if (!Access::can("view", $item)) {
      throw HTTP_Exception::factory(404, TEST_MODE ? 4 : null);
    }

    // Make sure we have view_full access to the original
    if ($type == "albums" && !Access::can("view_full", $item)) {
      throw HTTP_Exception::factory(404, TEST_MODE ? 5 : null);
    }

    // Don't try to load a directory
    if ($type == "albums" && $item->is_album()) {
      throw HTTP_Exception::factory(404, TEST_MODE ? 6 : null);
    }

    if ($type == "albums") {
      $file = $item->file_path();
    } else if ($type == "resizes") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    if (!file_exists($file)) {
      throw HTTP_Exception::factory(404, TEST_MODE ? 7 : null);
    }

    if (Gallery::show_profiler()) {
      Profiler::enable();
      $profiler = new Profiler();
      $profiler->render();
      // @todo: we probably shouldn't force a hard exit here, as it's the only non-CLI place in
      // our core code that we do this.  This can be tidied up when we fix Profiler for K3.
      exit;
    }

    // Set the filemtime as the etag (same as cache buster), use to check if cache needs refreshing.
    $this->check_cache(filemtime($file));

    // Force zlib compression off.  Image and movie files are already compressed and
    // recompressing them is CPU intensive.
    if (ini_get("zlib.output_compression")) {
      ini_set("zlib.output_compression", "Off");
    }

    // Dump out the image.  If the item is a movie or album, then its thumbnail will be a JPG.
    if (($item->is_movie() || $item->is_album()) && $type == "thumbs") {
      $mime_type = "image/jpeg";
    } else {
      $mime_type = $item->mime_type;
    }

    if (TEST_MODE) {
      $this->response->body($file);
    } else {
      // Send the file as the response.  The filename will be set automatically from the path.
      // We allow base64 encoding *only* if called internally (typically by a REST Data resource).
      // The "resumable" option enables byte-range requests, which are especially useful for
      // streaming HTML5 videos.
      // Note: send_file() will automatically halt script execution after sending the file.
      $options = array("inline" => "true", "mime_type" => $mime_type, "resumable" => true);
      if ($encoding == "base64") {
        $options["encoding"] = "base64";
      }
      $this->response->send_file($file, null, $options);
    }
  }
}
