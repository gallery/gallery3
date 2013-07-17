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

    // Translate REST's "size" into FileProxy's "type".
    $type = Arr::get(array("thumb" => "thumbs", "resize" => "resizes", "full" => "albums"), $size);
    if (!$type) {
      throw Rest_Exception::factory(400, array("size" => "invalid"));
    }

    // Build up our FileProxy subrequest.
    $request = Request::factory("file_proxy/show")
      ->query(array(
          "item"     => $item,
          "type"     => $type,
          "encoding" => $encoding
        ));

    // Collect some headers we want to pass through.
    $headers = Request::current()->headers();
    foreach (array("If-None-Match", "If-Range", "Range", "Cache-Control") as $key) {
      if (isset($headers[$key])) {
        $request->headers($key, $headers[$key]);
      }
    }

    // Execute the request.  If sending content, FileProxy will dump the file to the browser and
    // halt script execution with a status of either 200 (full file) or 206 (partial file).
    $response = $request->execute();

    // If not, we likely have an HTTP_Exception (typically a 304 or 404).
    if ($response->status() >= 300) {
      $e = Rest_Exception::factory($response->status());

      // Collect some headers we want to pass through.
      $headers = $response->headers();
      foreach (array("Etag", "Cache-Control") as $key) {
        if (isset($headers[$key])) {
          $e->headers($key, $headers[$key]);
        }
      }

      throw $e;
    }

    // Otherwise, simply return the body.  We only get here if in TEST_MODE.
    return $response->body();
  }
}
