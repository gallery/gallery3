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
class Mollon_Driver extends SpamFilter_Driver {
  // Lets not send everything to Akismet
  private $ignore = array("HTTP_COOKIE",
              "HTTP_USER_AGENT",
              "HTTP_X_FORWARDED_FOR",
              "HTTP_X_FORWARDED_HOST",
              "HTTP_MAX_FORWARDS",
              "HTTP_X_FORWARDED_SERVER",
              "REDIRECT_STATUS",
              "SERVER_PORT",
              "PATH",
              "DOCUMENT_ROOT",
              "REMOTE_ADDR",
              "SERVER_ADMIN",
              "QUERY_STRING",
              "PHP_SELF" );

  protected $_api_key;

  public function verify_key($api_key) {
    return true;
  }

  public function check_comment($comment_data) {
    return true;
  }

  public function submit_spam($comment_data) {
    return $response[1] == "true";
  }

  public function submit_ham($comment_data) {
  }

  public function get_statistics() {
    throw new Exception("@todo GET_STATISTICS NOT IMPLEMENTED");
  }

  private function _build_request($function, $host,$comment_data) {
    return "";
  }
}