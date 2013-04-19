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
class Gallery_Response extends Kohana_Response {
  /**
   * Encode an Ajax response so that it's UTF-7 safe.
   *
   * @param  string $message string to print
   */
  public function ajax($content) {
    $this->headers("Content-Type", "text/plain; charset=" . Kohana::$charset);
    $this->body("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\n" .
                $content);
  }

  /**
   * JSON Encode a reply to the browser and set the content type to specify that it's a JSON
   * payload.
   *
   * Optionally, the content type can be set as "text/plain" which helps with iframe
   * compatibility (see ticket #2022).
   *
   * @param  mixed    $message     string or object to json encode and print
   * @param  boolean  $text_plain  use content type of "text/plain" (default: false)
   */
  public function json($message, $text_plain=false) {
    if ($text_plain) {
      $this->headers("Content-Type", "text/plain; charset=" . Kohana::$charset);
    } else {
      $this->headers("Content-Type", "application/json; charset=" . Kohana::$charset);
    }
    $this->body(json_encode($message));
  }
}
