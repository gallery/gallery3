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
class Combined_Controller extends Controller {
  const ALLOW_MAINTENANCE_MODE = true;
  const ALLOW_PRIVATE_GALLERY = true;

  /**
   * Return the combined Javascript bundle associated with the given key.
   */
  public function javascript($key) {
    return $this->_emit("javascript", $key);
  }

  /**
   * Return the combined CSS bundle associated with the given key.
   */
  public function css($key) {
    return $this->_emit("css", $key);
  }

  /**
   * Print out a cached entry.
   * @param string   the combined entry type (either "javascript" or "css")
   * @param string   the key (typically an md5 sum)
   */
  private function _emit($type, $key) {
    $input = Input::instance();

    // We don't need to save the session for this request
    Session::instance()->abort_save();

    // Our data is immutable, so if they already have a copy then it needs no updating.
    if ($input->server("HTTP_IF_MODIFIED_SINCE")) {
      header('HTTP/1.0 304 Not Modified');
      header("Expires: Tue, 19 Jan 2038 00:00:00 GMT");
      header("Cache-Control: public,max-age=2678400");
      header('Pragma: public');
      Kohana::close_buffers(false);
      return "";
    }

    if (empty($key)) {
      throw new Kohana_404_Exception();
    }

    $cache = Cache::instance();
    $use_gzip = function_exists("gzencode") &&
      stripos($input->server("HTTP_ACCEPT_ENCODING"), "gzip") !== false &&
      (int) ini_get("zlib.output_compression") === 0;

    if ($use_gzip && $content = $cache->get("{$key}_gz")) {
      header("Content-Encoding: gzip");
      header("Vary: Accept-Encoding");
    } else {
      // Fall back to non-gzipped if we have to
      $content = $cache->get($key);
    }
    if (empty($content)) {
      throw new Kohana_404_Exception();
    }

    // $type is either 'javascript' or 'css'
    if ($type == "javascript") {
      header("Content-Type: application/javascript; charset=UTF-8");
    } else {
      header("Content-Type: text/css; charset=UTF-8");
    }
    header("Expires: Tue, 19 Jan 2038 00:00:00 GMT");
    header("Cache-Control: public,max-age=2678400");
    header("Pragma: public");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s T", time()));
    header("Content-Length: " . strlen($content));

    Kohana::close_buffers(false);
    print $content;
  }

}

