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

class REST_Core {
  /**
   * We're expecting to run in an environment that only supports GET/POST, so expect to tunnel
   * PUT and DELETE through POST.
   *
   * Returns the HTTP request method taking into consideration PUT/DELETE tunneling.
   * @todo Move this to a MY_request helper?
   * @return string HTTP request method
   */
  public static function request_method() {
    if (request::method() == "get") {
      return "get";
    } else {
      $input = Input::instance();
      switch ($input->post("_method", $input->get("_method"))) {
      case "put":    return "put";
      case "delete": return "delete";
      default:       return "post";
      }
    }
  }

  /**
   * Choose an output format based on what the client prefers to accept.
   * @return string "html", "xml" or "json"
   */
  public static function output_format() {
    // Pick a format, but let it be overridden.
      $input = Input::instance();
    return $input->get(
      "_format", $input->post(
        "_format", request::preferred_accept(
          array("html", "xml", "json"))));
  }
}
