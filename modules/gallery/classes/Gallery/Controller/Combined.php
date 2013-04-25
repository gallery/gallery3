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
class Gallery_Controller_Combined extends Controller {
  public $allow_maintenance_mode = true;
  public $allow_private_gallery = true;

  /**
   * Return the combined Javascript bundle associated with the given key.
   */
  public function action_javascript() {
    $key = $this->request->arg(0, "alpha_dash");
    return $this->_emit("javascript", $key);
  }

  /**
   * Return the combined CSS bundle associated with the given key.
   */
  public function action_css() {
    $key = $this->request->arg(0, "alpha_dash");
    return $this->_emit("css", $key);
  }

  /**
   * Print out a cached entry.
   * @param string   the combined entry type (either "javascript" or "css")
   * @param string   the key (typically an md5 sum)
   */
  protected function _emit($type, $key) {
    // We don't need to save the session for this request
    Session::instance()->abort_save();

    // Since our data is immutable, we set the etag to be the key (i.e. it never changes).
    // That way, if anything is found in the cache with this URL, it won't be refreshed.
    $this->check_cache($key);

    if (empty($key)) {
      throw HTTP_Exception::factory(404);
    }

    $cache = Cache::instance();
    $use_gzip = function_exists("gzencode") &&
      $this->request->headers()->accepts_encoding_at_quality("gzip") &&
      (int) ini_get("zlib.output_compression") === 0;

    if ($use_gzip && $content = $cache->get("{$key}_gz")) {
      $this->response->headers(array("Content-Encoding" => "gzip", "Vary" => "Accept-Encoding"));
    } else {
      // Fall back to non-gzipped if we have to
      $content = $cache->get($key);
    }
    if (empty($content)) {
      throw HTTP_Exception::factory(404);
    }

    // $type is either 'javascript' or 'css'
    if ($type == "javascript") {
      $mime_type = "application/javascript; charset=UTF-8";
    } else {
      $mime_type = "text/css; charset=UTF-8";
    }

    // Send the content as the response.  This sets the filename as $key, which matches the URL.
    $this->response->body($content);
    $this->response->send_file(true, $key, array("inline" => "true", "mime_type" => $mime_type));
  }
}

