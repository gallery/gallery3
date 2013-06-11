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
 * This resource returns the raw contents of Model_Item data files.  It's analogous to the
 * FileProxy controller, but it uses the REST authentication model.
 */
class Gallery_Controller_Rest_Data extends Controller_Rest {
  public function action_get() {
    $item = ORM::factory("Item", $this->rest_id);
    Access::required("view", $item);

    $size = $this->request->query("size");
    if (!in_array($size, array("thumb", "resize", "full"))) {
      throw Rest_Exception::factory(400, array("size" => "invalid"));
    }

    // Note: this code is roughly duplicated in FileProxy, so if you modify this, please look to
    // see if you should make the same change there as well.

    if ($size == "full") {
      Access::required("view_full", $item);
      $file = $item->file_path();
    } else if ($size == "resize") {
      $file = $item->resize_path();
    } else {
      $file = $item->thumb_path();
    }

    if (!file_exists($file)) {
      throw Rest_Exception::factory(404);
    }

    // Set the filemtime as the etag (same as cache buster), use to check if cache needs refreshing.
    $this->check_cache(filemtime($file));

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    // Dump out the image.  If the item is a movie or album, then its thumbnail will be a JPG.
    if (($item->is_movie() || $item->is_album()) && $size == "thumb") {
      $mime_type = "image/jpeg";
    } else {
      $mime_type = $item->mime_type;
    }

    if (TEST_MODE) {
      return $file;
    } else {
      // Send the file as the response.  The filename will be set automatically from the path.
      // Note: send_file() will automatically halt script execution after sending the file.
      $options = array("inline" => "true", "mime_type" => $mime_type);
      if ($this->request->query("encoding") == "base64") {
        $options["encoding"] = "base64";
      }
      $this->response->send_file($file, null, $options);
    }
  }
}
