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
class Javascript_Controller extends Controller {
  public function combined($key) {
    if (preg_match('/[^0-9a-f]/', $key)) {
      /* The key can't contain non-hex, so just terminate early */
      Kohana::show_404();
    }

    // We don't need to save the session for this request
    Session::abort_save();

    Kohana::log("error", Kohana::debug($_SERVER));
    // Dump out the javascript file
    $ext = strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false ? "_gzip" : "";
    $file = VARPATH . "tmp/CombinedJavascript_$key{$ext}";

    if (!file_exists($file)) {
      Kohana::show_404();
    }

    $stats = stat($file);
    if (!empty($_SERVER["HTTP_IF_MODIFIED_SINCE"]) &&
        $stats[9] <= $_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
      header("HTTP/1.0 304 Not Modified");
      return;
    }

    Kohana::log("error", Kohana::debug($_SERVER));
    if (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false) {
      header("Content-Encoding: gzip");
      header("Cache-Control: private, x-gzip-ok=\"public\"");
    }

    header("Content-Type: text/javascript; charset=UTF-8");

    header("Expires: " . gmdate(21474383647));
    header("Last-Modified: " . gmdate($stats[9]));

    Kohana::close_buffers(false);

    $fd = fopen($file, "rb");
    fpassthru($fd);
    fclose($fd);
  }
}

