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
abstract class SpamFilter_Driver {
  public abstract function check_comment($comment);

  public abstract function submit_spam($comment);

  public abstract function submit_ham($comment);

  public abstract function get_statistics();

  public abstract function get_admin_fields($post);

  public abstract function get_validation_rules($post);

  public abstract function set_api_data($post);

  protected function _http_post($host, $http_request, $port=80, $timeout=5) {
    $response = "";
    if (false !== ($fs = @fsockopen($host, $port, $errno, $errstr, $timeout))) {
      fwrite($fs, $http_request);
      while ( !feof($fs) ) {
        $response .= fgets($fs, 1160); // One TCP-IP packet
      }
      fclose($fs);
      list($headers, $body) = explode("\r\n\r\n", $response);
      $headers = explode("\r\n", $headers);
      $body = explode("\r\n", $body);
      $response = array("headers" => $headers, "body" => $body);
    } else {
      throw new Exception("@todo CONNECTION TO SPAM SERVICE FAILED");
    }
    return $response;
  }
}