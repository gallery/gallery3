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
  const OK = "200 OK";
  const CREATED = "201 Created";
  const ACCEPTED = "202 Accepted";
  const NO_CONTENT = "204 No Content";
  const PARTIAL_CONTENT = "206 Partial Content";
  const MOVED_PERMANENTLY = "301 Moved Permanently";
  const SEE_OTHER = "303 See Other";
  const NOT_MODIFIED = "304 Not Modified";
  const TEMPORARY_REDIRECT = "307 Temporary Redirect";
  const BAD_REQUEST = "400 Bad Request";
  const UNAUTHORIZED = "401 Unauthorized";
  const FORBIDDEN = "403 Forbidden";
  const NOT_FOUND = "404 Not Found";
  const METHOD_NOT_ALLOWED = "405 Method Not Allowed";
  const NOT_ACCEPTABLE = "406 Not Acceptable";
  const CONFLICT = "409 Conflict";
  const GONE = "410 Gone";
  const LENGTH_REQUIRED = "411 Length Required";
  const PRECONDITION_FAILED = "412 Precondition Failed";
  const UNSUPPORTED_MEDIA_TYPE = "415 Unsupported Media Type";
  const EXPECTATION_FAILED = "417 Expectation Failed";
  const INTERNAL_SERVER_ERROR = "500 Internal Server Error";
  const SERVICE_UNAVAILABLE = "503 Service Unavailable";

  const XML = "application/xml";
  const ATOM = "application/atom+xml";
  const RSS = "application/rss+xml";
  const JSON = "application/json";
  const HTML = "text/html";

  /**
   * We're expecting to run in an environment that only supports GET/POST, so expect to tunnel
   * PUT and DELETE through POST.
   *
   * Returns the HTTP request method taking into consideration PUT/DELETE tunneling.
   * @return string HTTP request method
   */
  public static function request_method() {
    if (request::method() == "get") {
      return "get";
    } else {
      $input = Input::instance();
      switch (strtolower($input->post("_method", $input->get("_method")))) {
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

  /**
   * Set HTTP response code.
   * @param string Use one of status code constants defined in this class.
   */
  public static function http_status($status_code) {
    header("HTTP 1.1 " . $status_code);
  }

  /**
   * Set HTTP Location header.
   * @param string URL
   */
  public static function http_location($url) {
    header("Location: " . $url);
  }

  /**
   * Set HTTP Content-Type header.
   * @param string content type
   */
  public static function http_content_type($type) {
    header("Content-Type: " . $type);
  }
}
