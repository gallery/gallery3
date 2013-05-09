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
class Akismet_Akismet {
  // Note: many of the Akismet functions are disabled/altered when in TEST_MODE.

  const URL             = "rest.akismet.com/1.1/";
  const VERIFY_KEY      = "verify-key";
  const COMMENT_CHECK   = "comment-check";
  const SUBMIT_SPAM     = "submit-spam";
  const SUBMIT_HAM      = "submit-ham";
  const REPLY_SPAM      = "true";
  const REPLY_HAM       = "false";
  const REPLY_KEY_VALID = "valid";

  /**
   * Check a comment against Akismet and return "spam", "ham" or "unknown".
   * @param  Model_Comment  $comment  A comment to check
   * @return $string "spam", "ham" or "unknown"
   */
  static function check_comment($comment) {
    if (TEST_MODE) {
      return;
    }
    switch (static::get_akismet_response(static::SUBMIT_HAM, $comment)) {
      case static::REPLY_SPAM: return "spam";    break;
      case static::REPLY_HAM:  return "ham";     break;
      default:                 return "unknown"; break;
    }
  }

  /**
   * Tell Akismet that this comment is spam
   * @param  Model_Comment  $comment  A comment to check
   */
  static function submit_spam($comment) {
    if (TEST_MODE) {
      return;
    }
    return static::get_akismet_response(static::SUBMIT_SPAM, $comment);
  }

  /**
   * Tell Akismet that this comment is ham
   * @param  Model_Comment  $comment  A comment to check
   */
  static function submit_ham($comment) {
    if (TEST_MODE) {
      return;
    }
    return static::get_akismet_response(static::SUBMIT_HAM, $comment);
  }

  /**
   * Check an API Key against Akismet to make sure that it's valid.  Blank passes, too.
   * @param  string   $api_key the API key
   * @return boolean
   */
  static function validate_key($api_key) {
    if (!$api_key) {
      return true;
    }
    return (static::get_akismet_response(static::VERIFY_KEY, $api_key) == static::REPLY_KEY_VALID);
  }

  /**
   * Check to see if we have the Akismet key set, then set/clear the site message as needed.
   */
  static function check_config() {
    $api_key = Module::get_var("akismet", "api_key");
    if (empty($api_key)) {
      SiteStatus::warning(
        t("Akismet is not quite ready!  Please provide an <a href=\"%url\">API Key</a>",
          array("url" => HTML::mark_clean(URL::site("admin/akismet")))),
        "akismet_config");
    } else {
      SiteStatus::clear("akismet_config");
    }
  }

  /**
   * Get a response from Akismet, and return the code.
   *
   * @see http://akismet.com/development/api
   * @param  string  $function       one of the function constants
   * @param  mixed   $comment        string if function is VERIFY_KEY, Model_Comment otherwise
   * @param  boolean $return_request return un-executed Request object instead (used in TEST_MODE)
   * @return mixed                   response string normally, Request object if $return_request
   */
  static function get_akismet_response($function, $comment, $return_request=false) {
    if ($function == static::VERIFY_KEY) {
      $sub = "";
      $post = array(
        "blog" => URL::base("http", false),
        "key"  => $comment
      );
    } else {
      $sub = Module::get_var("akismet", "api_key") . ".";
      $post = array(
        "blog"                 => URL::base("http", false),
        "comment_author"       => $comment->author_name(),
        "comment_author_email" => $comment->author_email(),
        "comment_author_url"   => $comment->author_url(),
        "comment_content"      => $comment->text,
        "comment_type"         => "comment",
        "permalink"            => URL::abs_site("comments/{$comment->id}"),
        "SERVER_NAME"          => $comment->server_name,
        "HTTP_ACCEPT"          => $comment->server_http_accept,
        "HTTP_ACCEPT_CHARSET"  => $comment->server_http_accept_charset,
        "HTTP_ACCEPT_ENCODING" => $comment->server_http_accept_encoding,
        "HTTP_ACCEPT_LANGUAGE" => $comment->server_http_accept_language,
        "HTTP_CONNECTION"      => $comment->server_http_connection,
        "referrer"             => $comment->server_http_referer,  // Note: "rr" vs "r" is not a typo
        "user_agent"           => $comment->server_http_user_agent,
        "QUERY_STRING"         => $comment->server_query_string,
        "user_ip"              => $comment->server_remote_addr,
        "REMOTE_HOST"          => $comment->server_remote_host,
        "REMOTE_PORT"          => $comment->server_remote_port
      );
    }

    $request = Request::factory("http://$sub" . static::URL . $function)
                 ->headers("user-agent", "Gallery/3 | Akismet/" . Module::get_version("akismet"))
                 ->headers("content-type", "application/x-www-form-urlencoded; charset=UTF-8")
                 ->method(Request::POST)
                 ->post($post);

    if ($return_request) {
      return $request;
    }

    return trim($request->execute()->body());
  }
}
