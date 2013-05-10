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
class Akismet_Test extends Unittest_TestCase {
  // Use the Akismet-recommended test author to guarantee a spam response,
  // which ensures that anything we send to Akismet will be flagged as a test.
  // If this unit test lasts more than 4 hours, please contact your Gallery developer.  :-)
  public static $test_author = "viagra-test-123";

  public function test_validate_key_request() {
    $request = Akismet::get_akismet_response("verify-key", "TEST_KEY", true);
    $request->execute();

    $expected_url = "http://rest.akismet.com/1.1/verify-key";
    $expected_headers = array("user-agent"   => "Gallery/3 | Akismet/1",
                              "content-type" => "application/x-www-form-urlencoded; charset=utf-8");
    $expected_post    = array("key"  => "TEST_KEY",
                              "blog" => "http://localhost/");

    $this->assertEquals($expected_headers, (array)$request->headers());
    $this->assertEquals($expected_post, $request->post());
    $this->assertEquals($expected_url, $request->uri());
  }

  // Note: comment-check, submit-spam, and submit-ham make near-identical requests.
  public function test_comment_check_request() {
    // First, we need a test comment.
    $comment = ORM::factory("Comment");
    $comment->item_id = Item::root()->id;
    $comment->author_id = Identity::guest()->id;
    $comment->text = "This is a comment";
    $comment->guest_name = static::$test_author;
    $comment->guest_email = "john@gallery2.org";
    $comment->guest_url = "http://gallery2.org";
    foreach ($comment->list_columns("comments") as $name => $field) {
      // Set the server fields to a known placeholder
      if (strpos($name, "server_") === 0) {
        $comment->$name = substr($name, strlen("server_"));
      }
    }
    $comment->save();
    $id = $comment->id;

    Module::set_var("akismet", "api_key", "TEST_KEY");
    $request = Akismet::get_akismet_response("comment-check", $comment, true);
    $request->execute();

    $expected_url = "http://TEST_KEY.rest.akismet.com/1.1/comment-check";
    $expected_headers = array("user-agent"   => "Gallery/3 | Akismet/1",
                              "content-type" => "application/x-www-form-urlencoded; charset=utf-8");
    $expected_post    = array("blog"                 => "http://localhost/",
                              "comment_author"       => static::$test_author,
                              "comment_author_email" => "john@gallery2.org",
                              "comment_author_url"   => "http://gallery2.org",
                              "comment_content"      => "This is a comment",
                              "comment_type"         => "comment",
                              "permalink"            => "http://localhost/index.php/comments/$id",
                              "SERVER_NAME"          => "name",
                              "HTTP_ACCEPT"          => "http_accept",
                              "HTTP_ACCEPT_CHARSET"  => "http_accept_charset",
                              "HTTP_ACCEPT_ENCODING" => "http_accept_encoding",
                              "HTTP_ACCEPT_LANGUAGE" => "http_accept_language",
                              "HTTP_CONNECTION"      => "http_connection",
                              "referrer"             => "http_referer",
                              "user_agent"           => "http_user_agent",
                              "QUERY_STRING"         => "query_string",
                              "user_ip"              => "remote_addr",
                              "REMOTE_HOST"          => "remote_host",
                              "REMOTE_PORT"          => "remote_port");

    $this->assertEquals($expected_headers, (array)$request->headers());
    $this->assertEquals($expected_post, $request->post());
    $this->assertEquals($expected_url, $request->uri());
  }
}
