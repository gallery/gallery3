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
class akismet_Core {
  public static $test_mode = TEST_MODE;

  static function get_configure_form() {
    $form = new Forge("admin/akismet", "", "post");
    $group = $form->group("configure_akismet")->label(t("Configure Akismet"));
    $group->input("api_key")->label(t("API Key"))->value(module::get_var("akismet", "api_key"));
    $group->api_key->error_messages("invalid", t("The API key you provided is invalid."));
    $group->submit("")->value(t("Save"));
    return $form;
  }

  /**
   * Check a comment against Akismet and return "spam", "ham" or "unknown".
   * @param  Comment_Model  $comment  A comment to check
   * @return $string "spam", "ham" or "unknown"
   */
  static function check_comment($comment) {
    if (akismet::$test_mode) {
      return;
    }

    $request = self::_build_request("comment-check", $comment);
    $response = self::_http_post($request);
    $answer = $response->body[0];
    if ($answer == "true") {
      return "spam";
    } else if ($answer == "false") {
      return "ham";
    } else {
      return "unknown";
    }
  }

  /**
   * Tell Akismet that this comment is spam
   * @param  Comment_Model  $comment  A comment to check
   */
  static function submit_spam($comment) {
    if (akismet::$test_mode) {
      return;
    }

    $request = self::_build_request("submit-spam", $comment);
    self::_http_post($request);
  }

  /**
   * Tell Akismet that this comment is ham
   * @param  Comment_Model  $comment  A comment to check
   */
  static function submit_ham($comment) {
    if (akismet::$test_mode) {
      return;
    }

    $request = self::_build_request("submit-ham", $comment);
    self::_http_post($request);
  }

  /**
   * Check an API Key against Akismet to make sure that it's valid
   * @param  string   $api_key the API key
   * @return boolean
   */
  static function validate_key($api_key) {
    $request = self::_build_verify_request($api_key);
    $response = self::_http_post($request, "rest.akismet.com");
    return "valid" == $response->body[0];
  }


  static function check_config() {
    $api_key = module::get_var("akismet", "api_key");
    if (empty($api_key)) {
      site_status::warning(
        t("Akismet is not quite ready!  Please provide an <a href=\"%url\">API Key</a>",
          array("url" => url::site("admin/akismet"))),
        "akismet_config");
    } else {
      site_status::clear("akismet_config");
    }
  }


  static function _build_verify_request($api_key) {
    $base_url = url::base(false, "http");
    $query_string = "key={$api_key}&blog=$base_url";

    $version = module::get_version("akismet");
    $http_request  = "POST /1.1/verify-key HTTP/1.0\r\n";
    $http_request .= "Host: rest.akismet.com\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
    $http_request .= "Content-Length: " . strlen($query_string) . "\r\n";
    $http_request .= "User-Agent: Gallery/3 | Akismet/$version\r\n";
    $http_request .= "\r\n";
    $http_request .= $query_string;

    return $http_request;
  }

  static function _build_request($function, $comment) {
    $comment_data = array();
    $comment_data["HTTP_ACCEPT"] = $comment->server_http_accept;
    $comment_data["HTTP_ACCEPT_ENCODING"] = $comment->server_http_accept_encoding;
    $comment_data["HTTP_ACCEPT_LANGUAGE"] = $comment->server_http_accept_language;
    $comment_data["HTTP_CONNECTION"] = $comment->server_http_connection;
    $comment_data["HTTP_HOST"] = $comment->server_http_host;
    $comment_data["HTTP_USER_AGENT"] = $comment->server_http_user_agent;
    $comment_data["QUERY_STRING"] = $comment->server_query_string;
    $comment_data["REMOTE_ADDR"] = $comment->server_remote_addr;
    $comment_data["REMOTE_HOST"] = $comment->server_remote_host;
    $comment_data["REMOTE_PORT"] = $comment->server_remote_port;
    $comment_data["SERVER_HTTP_ACCEPT_CHARSET"] = $comment->server_http_accept_charset;
    $comment_data["blog"] = url::base(false, "http");
    $comment_data["comment_author"] = $comment->author_name();
    $comment_data["comment_author_email"] = $comment->author_email();
    $comment_data["comment_author_url"] = $comment->author_url();
    $comment_data["comment_content"] = $comment->text;
    $comment_data["comment_type"] = "comment";
    $comment_data["permalink"] = url::site("comments/{$comment->id}");
    $comment_data["referrer"] = $comment->server_http_referer;
    $comment_data["user_agent"] = $comment->server_http_user_agent;
    $comment_data["user_ip"] = $comment->server_remote_addr;

    $query_string = array();
    foreach ($comment_data as $key => $data) {
      $query_string[] = "$key=" . urlencode($data);
    }
    $query_string = join("&", $query_string);

    $version = module::get_version("akismet");
    $http_request  = "POST /1.1/$function HTTP/1.0\r\n";
    $http_request .= "Host: " . module::get_var("akismet", "api_key") . ".rest.akismet.com\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
    $http_request .= "Content-Length: " . strlen($query_string) . "\r\n";
    $http_request .= "User-Agent: Gallery/3 | Akismet/$version\r\n";
    $http_request .= "\r\n";
    $http_request .= $query_string;

    return $http_request;
  }

  private static function _http_post($http_request, $host=null) {
    if (!$host) {
      $host = module::get_var("akismet", "api_key") . ".rest.akismet.com";
    }
    $response = "";

    Kohana::log("debug", "Send request\n" . print_r($http_request, 1));
    if (false !== ($fs = @fsockopen($host, 80, $errno, $errstr, 5))) {
      fwrite($fs, $http_request);
      while ( !feof($fs) ) {
        $response .= fgets($fs, 1160); // One TCP-IP packet
      }
      fclose($fs);
      list($headers, $body) = explode("\r\n\r\n", $response);
      $headers = explode("\r\n", $headers);
      $body = explode("\r\n", $body);
      $response = new ArrayObject(
        array("headers" => $headers, "body" => $body), ArrayObject::ARRAY_AS_PROPS);
    } else {
      throw new Exception("@todo CONNECTION TO SPAM SERVICE FAILED");
    }
    Kohana::log("debug", "Received response\n" . print_r($response, 1));

    return $response;
  }
}
