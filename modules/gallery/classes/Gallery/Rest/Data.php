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
class Gallery_Rest_Data extends Rest {
  /**
   * This read-only resource returns Model_Item data files.  Once verified, it calls a
   * sub-request to the FileProxy controller (and therefore uses the same access control).
   *
   * GET returns the raw contents of a data file (id and "size" parameter required)
   *   size=<full, resize, or thumb>
   *     Return the raw contents of an item's full, resize, or thumb data files.
   *   m=<int>
   *     Query parameter added as a browser cache buster (ignored during processing).
   *   encoding=base64
   *     Output the data file using base64 encoding.
   */
  public function get_response() {
    $item = ORM::factory("Item", $this->id);
    $size = Arr::get($this->params, "size");
    $encoding = Arr::get($this->params, "encoding");

    switch ($size) {
    case "full":
      $file = $item->file_path();
      break;

    case "resize":
      $file = $item->resize_path();
      break;

    case "thumb":
      $file = $item->thumb_path();
      break;

    default:
      throw Rest_Exception::factory(400, array("size" => "invalid"));
    }

    $params = $encoding ? array("encoding" => $encoding) : array();
    $file = substr($file, strlen(DOCROOT));

    // If successful, FileProxy will dump the file to the browser and halt script execution.
    $response = Request::factory($file)->query($params)->execute();

    // If not, we likely have an HTTP error code to throw (typically a 404).
    if ($response->status() >= 400) {
      throw Rest_Exception::factory($response->status());
    }

    // Otherwise, simply return the body.  We only get here if in TEST_MODE.
    return $response->body();
  }
}
