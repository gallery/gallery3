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
class Akismet_Helper_Test extends Unit_Test_Case {
  private $_comment;

  public function setup() {
    Input::instance()->ip_address = "1.1.1.1";
    Kohana::$user_agent = "Akismet_Helper_Test";

    $root = ORM::factory("item", 1);
    $this->_comment = comment::create(
      $root, user::guest(), "This is a comment",
      "John Doe", "john@gallery2.org", "http://gallery2.org");
    module::set_var("akismet", "api_key", "TEST_KEY");
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
    $request = akismet::_build_request("comment-check", $this->_comment);
    $expected = "POST /1.1/comment-check HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 296\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$this->_comment->id}" .
      "&blog=http%3A%2F%2F.%2F&user_agent=Akismet_Helper_Test&referrer=&comment_type=comment" .
      "&comment_author=John+Doe&comment_author_email=John%40gallery2.org" .
      "&comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment";
    $this->assert_equal($expected, $request);
  }

  public function build_submit_spam_request_test() {
    $request = akismet::_build_request("submit-spam", $this->_comment);
    $expected =
      "POST /1.1/submit-spam HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 296\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$this->_comment->id}" .
      "&blog=http%3A%2F%2F.%2F&user_agent=Akismet_Helper_Test&referrer=&comment_type=comment" .
      "&comment_author=John+Doe&comment_author_email=John%40gallery2.org" .
      "&comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment";

    $this->assert_equal($expected, $request);
  }

  public function build_submit_ham_request_test() {
    $request = akismet::_build_request("submit-ham", $this->_comment);
    $expected =
      "POST /1.1/submit-ham HTTP/1.0\r\n" .
      "Host: TEST_KEY.rest.akismet.com\r\n" .
      "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
      "Content-Length: 296\r\n" .
      "User-Agent: Gallery/3 | Akismet/1\r\n\r\n" .
      "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F{$this->_comment->id}" .
      "&blog=http%3A%2F%2F.%2F&user_agent=Akismet_Helper_Test&referrer=&comment_type=comment" .
      "&comment_author=John+Doe&comment_author_email=John%40gallery2.org" .
      "&comment_author_url=http%3A%2F%2Fgallery2.org&comment_content=This+is+a+comment";

    $this->assert_equal($expected, $request);
  }
}

