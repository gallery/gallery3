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
class Akismet_Driver extends SpamFilter_Driver {
  // Lets not send everything to Akismet
  // @todo change to a white list
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

//  public function verify_key($api_key) {
////    $url = url::base();
////    $response = $this->_http_post("rest.akismet.com", "key={$api_key}&blog=$url");
////    if ("valid" != $response[1]) {
////      throw new Exception("@todo INVALID AKISMET KEY");
////    }
//    return true;
//  }

  public function check_comment($comment) {
//    $request = $this->_build_request("comment-check", $comment);
//    $response = $this->_http_post($this->_get_host_url(), $request);
//    return $reponse[1] == "true";
    return true;
  }

  public function submit_spam($comment) {
//    $request = $this->_build_request("submit-spam", $comment);
//    $response = $this->_http_post($this->_get_host_url(), $request);
//    return $response[1] == "true";
    return true;
  }

  public function submit_ham($comment) {
//    $request = $this->_build_request("submit-ham", $comment);
//    $response = $this->_http_post($this->_get_host_url(), $request);
//    return $reponse[1] == "true";
    return true;
  }

  public function get_statistics() {
    throw new Exception("@todo GET_STATISTICS NOT SUPPORTED");
  }

  public function get_admin_fields($post) {
    $view = new View("spam_filter_admin_akismet.html");
    $view->api_key = empty($post) ? module::get_var("spam_filter", "api_key") :
      $post->api_key;

    $view->errors = $post ? $post->errors() : null;
    return $view;
  }

  public function get_validation_rules($post) {
    $post->add_rules("api_key", "required");
    $post->add_callbacks("api_key", array($this, "validate_key"));
  }

  public function validate_key(Validation $array, $field) {
    // @todo verify key values
    Kohana::log("debug", "Akismet::validate_key");
    Kohana::log("debug", print_r($array, 1));
    Kohana::log("debug", "field: $field");
  }

  public function set_api_data($post) {
    module::set_var("spam_filter", "api_key", $post->api_key);
  }

  private function _build_request($function, $comment) {
    $comment_data = array();
    foreach($_SERVER as $key => $value) {
      if(!in_array($key, $this->ignore)) {
        $comment_data[$key] = $value;
      }
    }

    $query_string = "";
    foreach($comment_data as $key => $data) {
      if(!is_array($data)) {
        $query_string .= $key . "=" . urlencode(stripslashes($data)) . "&";
      }
    }

    $host = $this->_get_host_url();
    $http_request  = "POST /1.1/$function HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
    $http_request .= "Content-Length: " . strlen($query_string) . "\r\n";
    $http_request .= "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n";
    $http_request .= "\r\n";
    $http_request .= $query_string;

    return $http_request;
  }

  private function _get_host_url() {
    $api_key = module::get_var("spam_filter", "api_key");
    return "$api_key.rest.akismet.com";
  }
}