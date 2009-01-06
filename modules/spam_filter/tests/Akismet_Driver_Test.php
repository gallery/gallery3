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
class Akismet_Driver_Test extends Unit_Test_Case {
  private $_ip_address;
  private $_user_agent;
  private $_comment;
  private $_driver;
  private $_api_key;

  public function setup() {
    $this->_ip_address = Input::instance()->ip_address;
    $this->_user_agent = Kohana::$user_agent;

    Input::instance()->ip_address = "1.1.1.1";
    Kohana::$user_agent = "Gallery3 Unit Test";

    $this->_driver = new Akismet_Driver();
    $this->_comment = comment::create("John Doe", "John@gallery.com", "This is a comment", 0, "http://gallery.com");
    $this->_api_key = module::get_var("spam_filter", "api_key");
    if (empty($this->_api_key)) {
      $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
      for ($i = 0; $i < 10; $i++) {
        $this->_api_key .= $chars[mt_rand(0, 36)];
      }
      module::set_var("spam_filter", "api_key", $this->_api_key);
    }
  }

  public function teardown() {
    Input::instance()->ip_address = $this->_ip_address;
    Kohana::$user_agent = $this->_user_agent;
  }

  public function build_verify_key_request_test() {
    $request = $this->_driver->_build_verify_request($this->_api_key);
    $data = "key={$this->_api_key}&blog=http://./";
    $data_length = strlen($data);
    $expected = "POST /1.1/verify-key HTTP/1.0\r\n" .
                "Host: rest.akismet.com\r\n" .
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" .
                "Content-Length: {$data_length}\r\n" . 
                "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n" .
                "\r\n$data";
    $this->assert_equal($expected, $request);
  }

  public function build_check_comment_request_test() {
    $request = $this->_driver->_build_request("comment-check", $this->_comment);
    $data = "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F2&blog=http%3A%2F%2F.%2F" .
            "&user_agent=Gallery3+Unit+Test&referrer=&comment_type=comment&comment_author=John+Doe" .
            "&comment_author_email=John%40gallery.com&comment_author_url=http%3A%2F%2Fgallery.com" .
            "&comment_content=This+is+a+comment&";
    $data_length = strlen($data);
    $expected = "POST /1.1/comment-check HTTP/1.0\r\n" .
                "Host: {$this->_api_key}.rest.akismet.com\r\n" . 
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" . 
                "Content-Length: {$data_length}\r\n" . 
                "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n" . 
                "\r\n$data"; 
    $this->assert_equal($expected, $request);
  }

  public function build_submit_spam_request_test() {
    $request = $this->_driver->_build_request("submit-spam", $this->_comment);
    $data = "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F3&blog=http%3A%2F%2F.%2F" .
            "&user_agent=Gallery3+Unit+Test&referrer=&comment_type=comment&comment_author=John+Doe" .
            "&comment_author_email=John%40gallery.com&comment_author_url=http%3A%2F%2Fgallery.com" .
            "&comment_content=This+is+a+comment&";
    $data_length = strlen($data);
    $expected = "POST /1.1/submit-spam HTTP/1.0\r\n" .
                "Host: {$this->_api_key}.rest.akismet.com\r\n" . 
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" . 
                "Content-Length: {$data_length}\r\n" . 
                "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n" . 
                "\r\n$data"; 
    $this->assert_equal($expected, $request);
  }

  public function build_submit_ham_equest_test() {
    $request = $this->_driver->_build_request("submit-ham", $this->_comment);
    $data = "user_ip=1.1.1.1&permalink=http%3A%2F%2F.%2Findex.php%2Fcomments%2F4&blog=http%3A%2F%2F.%2F" .
            "&user_agent=Gallery3+Unit+Test&referrer=&comment_type=comment&comment_author=John+Doe" .
            "&comment_author_email=John%40gallery.com&comment_author_url=http%3A%2F%2Fgallery.com" .
            "&comment_content=This+is+a+comment&";
    $data_length = strlen($data);
    $expected = "POST /1.1/submit-ham HTTP/1.0\r\n" .
                "Host: {$this->_api_key}.rest.akismet.com\r\n" . 
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n" . 
                "Content-Length: {$data_length}\r\n" . 
                "User-Agent: Gallery 3.0 | Akismet/1.11 \r\n" . 
                "\r\n$data"; 
    $this->assert_equal($expected, $request);
  }
}

