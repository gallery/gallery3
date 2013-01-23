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
class Akismet_Helper_Test extends Gallery_Unit_Test_Case {
  private $_comment;

  public function setup() {
    Input::instance()->ip_address = "1.1.1.1";
    request::set_user_agent("Akismet_Helper_Test");
    module::set_var("akismet", "api_key", "TEST_KEY");
  }

  private function _make_comment() {
    $comment = ORM::factory("comment");
    $comment->item_id = item::root()->id;
    $comment->author_id = identity::guest()->id;
    $comment->text = "This is a comment";
    $comment->guest_name = "John Doe";
    $comment->guest_email = "john@gallery2.org";
    $comment->guest_url = "http://gallery2.org";
    $comment->save();

    // Set the server fields to a known placeholder
    foreach ($comment->list_fields("comments") as $name => $field) {
      if (strpos($name, "server_") === 0) {
        $comment->$name = substr($name, strlen("server_"));
      }
    }
    return $comment->save();
  }

  public function build_verify_request_test() {
    $request = akismet::_build_verify_request("TEST_KEY");
    $expected =
      "POST /1.1/verify-key HTTP/1.0\r\n" .
      "Host: rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 27\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "key=TEST_KEY&blog=http://./";
    $this->assert_equal($expected, $request);
  }

  public function build_comment_check_request_test() {
    $comment = $this->_make_comment();
    $request = akismet::_build_request("comment-check", $comment);
    $expected = "POST /1.1/comment-check HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 645\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "HTTP_ACCEPT=http_accept&HTTP_ACCEPT_ENCODING=http_accept_encoding&" .
      "HTTP_ACCEPT_LANGUAGE=http_accept_language&HTTP_CONNECTION=http_connection&" .
      "HTTP_HOST=http_host&HTTP_USER_AGENT=http_user_agent&" .
      "QUERY_STRING=query_string&REMOTE_ADDR=remote_addr&" .
      "REMOTE_HOST=remote_host&REMOTE_PORT=remote_port&" .
      "SERVER_HTTP_ACCEPT_CHARSET=http_accept_charset&" .
      "blog=http%3A%2F%2F.%2F&comment_author=John+Doe&comment_author_email=john%40gallery2.org&" .
      "comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment&" .
      "comment_type=comment&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$comment->id}&" .
      "referrer=http_referer&user_agent=http_user_agent&user_ip=remote_addr";

    $this->assert_equal($expected, $request);
  }

  public function build_submit_spam_request_test() {
    $comment = $this->_make_comment();
    $request = akismet::_build_request("submit-spam", $comment);
    $expected =
      "POST /1.1/submit-spam HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 645\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "HTTP_ACCEPT=http_accept&HTTP_ACCEPT_ENCODING=http_accept_encoding&" .
      "HTTP_ACCEPT_LANGUAGE=http_accept_language&HTTP_CONNECTION=http_connection&" .
      "HTTP_HOST=http_host&HTTP_USER_AGENT=http_user_agent&" .
      "QUERY_STRING=query_string&REMOTE_ADDR=remote_addr&" .
      "REMOTE_HOST=remote_host&REMOTE_PORT=remote_port&" .
      "SERVER_HTTP_ACCEPT_CHARSET=http_accept_charset&" .
      "blog=http%3A%2F%2F.%2F&comment_author=John+Doe&comment_author_email=john%40gallery2.org&" .
      "comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment&" .
      "comment_type=comment&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$comment->id}&" .
      "referrer=http_referer&user_agent=http_user_agent&user_ip=remote_addr";

    $this->assert_equal($expected, $request);
  }

  public function build_submit_ham_request_test() {
    $comment = $this->_make_comment();
    $request = akismet::_build_request("submit-ham", $comment);
    $expected =
      "POST /1.1/submit-ham HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 645\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "HTTP_ACCEPT=http_accept&HTTP_ACCEPT_ENCODING=http_accept_encoding&" .
      "HTTP_ACCEPT_LANGUAGE=http_accept_language&HTTP_CONNECTION=http_connection&" .
      "HTTP_HOST=http_host&HTTP_USER_AGENT=http_user_agent&" .
      "QUERY_STRING=query_string&REMOTE_ADDR=remote_addr&" .
      "REMOTE_HOST=remote_host&REMOTE_PORT=remote_port&" .
      "SERVER_HTTP_ACCEPT_CHARSET=http_accept_charset&blog=http%3A%2F%2F.%2F&" .
      "comment_author=John+Doe&comment_author_email=john%40gallery2.org&" .
      "comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment&" .
      "comment_type=comment&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$comment->id}&" .
      "referrer=http_referer&user_agent=http_user_agent&user_ip=remote_addr";

    $this->assert_equal($expected, $request);
  }
}

