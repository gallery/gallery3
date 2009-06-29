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
class Combined_Controller extends Controller {
  public function __call($type, $key) {
    if (empty($key)) {
      Kohana::show_404();
    }
    $key = $key[0];
    if (preg_match('/[^0-9a-f]/', $key)) {
      // The key can't contain non-hex, so just terminate early
      Kohana::show_404();
    }

    // We don't need to save the session for this request
    Session::abort_save();

    // Our data is immutable, so if they already have a copy then it needs no updating.
    if (!empty($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
      header('HTTP/1.0 304 Not Modified');
      return;
    }

    $cache = Cache::instance();
    if (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false ) {
      $content = $cache->get("{$key}_gz");
    }

    if (empty($content)) {
      $content = $cache->get($key);
    }

    if (empty($content)) {
      Kohana::show_404();
    }

    if (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false) {
      header("Content-Encoding: gzip");
      header("Cache-Control: public");
    }

    header("Content-Type: text/$type; charset=UTF-8");
    header("Expires: Tue, 19 Jan 2038 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s T", time()));

    Kohana::close_buffers(false);
    print $content;
  }

}

