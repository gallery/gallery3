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
  // @todo provide an admin option to send or not send this information
  private static $white_list = array("HTTP_USER_AGENT", 
            "HTTP_ACCEPT", "HTTP_ACCEPT_CHARSET", "HTTP_ACCEPT_ENCODING",
            "HTTP_ACCEPT_LANGUAGE", "HTTP_CONNECTION", "HTTP_HOST",
            "HTTP_KEEP_ALIVE", "HTTP_REFERER", "HTTP_USER_AGENT", "QUERY_STRING",
            "REMOTE_ADDR", "REMOTE_HOST", "REMOTE_PORT" );

  public function check_comment($comment) {
    $request = $this->_build_request("comment-check", $comment);
    $response = $this->_http_post($this->_get_host_url(), $request);

    Kohana::log("debug", print_r($response, 1));
    if ($response["body"][0] != "true" && $response["body"][0] != "false") {
      Kohana::log("alert", $response["body"][0]);
    }
    return $response["body"][0] == "true";
  }

  public function submit_spam($comment) {
    $request = $this->_build_request("submit-spam", $comment);
    $response = $this->_http_post($this->_get_host_url(), $request);
    if ($response["body"][0] != "true" && $response["body"][0] != "false") {
      Kohana::log("alert", $response["body"][0]);
    }
    return $response["body"][0] == "true";
  }

  public function submit_ham($comment) {
    $request = $this->_build_request("submit-ham", $comment);
    $response = $this->_http_post($this->_get_host_url(), $request);
    if ($response["body"][0] != "true" && $response["body"][0] != "false") {
      Kohana::log("alert", $response["body"][0]);
    }
    return $response["body"][0] == "true";
  }

  public function get_statistics() {
    throw new Exception("@todo GET_STATISTICS NOT SUPPORTED");
  }

  public function get_admin_fields($post) {
    $view = new View("admin_spam_filter_akismet.html");
    $view->api_key = empty($post) ? module::get_var("spam_filter", "api_key") :
      $post->api_key;

    $view->errors = $post ? $post->errors() : null;
    return $view;
  }

  public function get_validation_rules($post) {
    $post->add_rules("api_key", "required");
    $post->add_callbacks("api_key", array($this, "validate_key"));
  }

  public function validate_key(Validation $post, $field) {
    $request = $this->_build_verify_request($post->api_key);
    $response = $this->_http_post("rest.akismet.com", $request);
    Kohana::log("debug", print_r($response, 1));
    if ("valid" != $response["body"][0]) {
      $post->add_error("api_key", "invalid");
      Kohana::log("alert", "Failed to verify Akismet Key:\n" . print_r($response["headers"], 1));
    }
  }

  public function _build_verify_request($api_key) {
    $base_url = url::base(true, true);
    $query_string = "key={$api_key}&blog=$base_url";

    $http_request  = "POST /1.1/verify-key HTTP/1.0\r\n";
    $http_request .= "Host: rest.akismet.com\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
    $http_request .= "Content-Length: " . strlen($query_string) . "\r\n";
    $http_request .= "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n";
    $http_request .= "\r\n";
    $http_request .= $query_string;

    return $http_request;
  }

  public function set_api_data($post) {
    module::set_var("spam_filter", "api_key", $post->api_key);
  }

  public function _build_request($function, $comment) {
    $comment_data = array();
    $comment_data["user_ip"] = $comment->ip_addr;
    $comment_data["permalink"] = url::site("comments/{$comment->id}");
    $comment_data["blog"] = url::base(true, true);
    $comment_data["user_agent"] = $comment->user_agent;
    $comment_data["referrer"] = $_SERVER["HTTP_REFERER"];
    $comment_data["comment_type"] = "comment";
    $comment_data["comment_author"] = $comment->author;
    $comment_data["comment_author_email"] = $comment->email;
    $comment_data["comment_author_url"] = str_replace(array("http://", "https://"), "", $comment->url);
    $comment_data["comment_content"] = $comment->text;

    foreach($_SERVER as $key => $value) {
      if(in_array($key, self::$white_list)) {
        $comment_data[$key] = $value;
      }
    }

    $query_string = array();
    foreach($comment_data as $key => $data) {
      if(!is_array($data)) {
//        $query_string .= $key . "=" . urlencode(stripslashes($data)) . "&";
        $query_string[] = "$key=" . urlencode($data);
      }
    }
    $query_string = join("&", $query_string);

    $host = $this->_get_host_url();
    $http_request  = "POST /1.1/$function HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
    $http_request .= "Content-Length: " . strlen($query_string) . "\r\n";
    $http_request .= "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n";
    $http_request .= "\r\n";
    $http_request .= $query_string;

    Kohana::log("debug", $http_request);

    return $http_request;
  }

  private function _get_host_url() {
    $api_key = module::get_var("spam_filter", "api_key");
    return "$api_key.rest.akismet.com";
  }
}
