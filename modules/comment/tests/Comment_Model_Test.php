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
class Comment_Model_Test extends Unittest_TestCase {
  protected $_user_agent;
  protected $_save;

  public function setup() {
    parent::setup();

    $this->_user_agent = Request::$user_agent;  // Use this instead of user_agent() for exact reset.
    $this->_save = $_SERVER;

    $_SERVER["HTTP_ACCEPT"] = "HTTP_ACCEPT";
    $_SERVER["HTTP_ACCEPT_CHARSET"] = "HTTP_ACCEPT_CHARSET";
    $_SERVER["HTTP_ACCEPT_ENCODING"] = "HTTP_ACCEPT_ENCODING";
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "HTTP_ACCEPT_LANGUAGE";
    $_SERVER["HTTP_CONNECTION"] = "HTTP_CONNECTION";
    $_SERVER["HTTP_REFERER"] = "HTTP_REFERER";
    $_SERVER["HTTP_USER_AGENT"] = "HTTP_USER_AGENT";
    $_SERVER["QUERY_STRING"] = "QUERY_STRING";
    $_SERVER["REMOTE_ADDR"] = "REMOTE_ADDR";
    $_SERVER["REMOTE_HOST"] = "REMOTE_HOST";
    $_SERVER["REMOTE_PORT"] = "REMOTE_PORT";
    $_SERVER["SERVER_NAME"] = "SERVER_NAME";

    Request::$user_agent = "HTTP_USER_AGENT";
  }

  public function teardown() {
    Request::$user_agent = $this->_user_agent;
    $_SERVER = $this->_save;

    parent::teardown();
  }

  public function test_guest_name_and_email_is_required() {
    try {
      $comment = ORM::factory("Comment");
      $comment->item_id = Item::root()->id;
      $comment->author_id = Identity::guest()->id;
      $comment->text = "text";
      $comment->server_name = "server_name";
      $comment->save();
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("not_empty", $errors["guest_name"][0]);
      $this->assertEquals("not_empty", $errors["guest_email"][0]);
      return;
    }
  }

  public function test_guest_email_must_be_well_formed() {
    try {
      $comment = ORM::factory("Comment");
      $comment->item_id = Item::root()->id;
      $comment->author_id = Identity::guest()->id;
      $comment->guest_name = "guest";
      $comment->guest_email = "bogus";
      $comment->text = "text";
      $comment->server_name = "server_name";
      $comment->save();
    } catch (ORM_Validation_Exception $e) {
      $errors = $e->errors();
      $this->assertEquals("email", $errors["guest_email"][0]);
      return;
    }
  }

  public function test_cant_view_comments_for_unviewable_items() {
    $album = Test::random_album();

    $comment = ORM::factory("Comment");
    $comment->item_id = $album->id;
    $comment->author_id = Identity::admin_user()->id;
    $comment->text = "text";
    $comment->server_name = "server_name";
    $comment->save();

    Identity::set_active_user(Identity::guest());

    // We can see the comment when permissions are granted on the album
    Access::allow(Identity::everybody(), "view", $album);
    $this->assertTrue((bool)
      ORM::factory("Comment")->viewable()->where("comment.id", "=", $comment->id)->count_all());

    // We can't see the comment when permissions are denied on the album
    Access::deny(Identity::everybody(), "view", $album);
    $this->assertFalse((bool)
      ORM::factory("Comment")->viewable()->where("comment.id", "=", $comment->id)->count_all());
  }

  public function test_create_comment_for_guest() {
    $comment = ORM::factory("Comment");
    $comment->item_id = Item::root()->id;
    $comment->text = "text";
    $comment->author_id = Identity::guest()->id;
    $comment->guest_name = "name";
    $comment->guest_email = "email@email.com";
    $comment->guest_url = "http://url.com";
    $comment->save();

    $this->assertEquals("name", $comment->author_name());
    $this->assertEquals("email@email.com", $comment->author_email());
    $this->assertEquals("http://url.com", $comment->author_url());
    $this->assertEquals("text", $comment->text);
    $this->assertEquals(1, $comment->item_id);

    $this->assertEquals("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assertEquals("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assertEquals("HTTP_ACCEPT", $comment->server_http_accept);
    $this->assertEquals("HTTP_ACCEPT_CHARSET", $comment->server_http_accept_charset);
    $this->assertEquals("HTTP_ACCEPT_ENCODING", $comment->server_http_accept_encoding);
    $this->assertEquals("HTTP_ACCEPT_LANGUAGE", $comment->server_http_accept_language);
    $this->assertEquals("HTTP_CONNECTION", $comment->server_http_connection);
    $this->assertEquals("HTTP_REFERER", $comment->server_http_referer);
    $this->assertEquals("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assertEquals("SERVER_NAME", $comment->server_name);
    $this->assertEquals("QUERY_STRING", $comment->server_query_string);
    $this->assertEquals("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assertEquals("REMOTE_HOST", $comment->server_remote_host);
    $this->assertEquals("REMOTE_PORT", $comment->server_remote_port);

    $this->assertTrue(!empty($comment->created));
  }

  public function test_create_comment_for_user() {
    $admin = Identity::admin_user();

    $comment = ORM::factory("Comment");
    $comment->item_id = Item::root()->id;
    $comment->text = "text";
    $comment->author_id = $admin->id;
    $comment->save();

    $this->assertEquals($admin->full_name, $comment->author_name());
    $this->assertEquals($admin->email, $comment->author_email());
    $this->assertEquals($admin->url, $comment->author_url());
    $this->assertEquals("text", $comment->text);
    $this->assertEquals(1, $comment->item_id);

    $this->assertEquals("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assertEquals("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assertEquals("HTTP_ACCEPT", $comment->server_http_accept);
    $this->assertEquals("HTTP_ACCEPT_CHARSET", $comment->server_http_accept_charset);
    $this->assertEquals("HTTP_ACCEPT_ENCODING", $comment->server_http_accept_encoding);
    $this->assertEquals("HTTP_ACCEPT_LANGUAGE", $comment->server_http_accept_language);
    $this->assertEquals("HTTP_CONNECTION", $comment->server_http_connection);
    $this->assertEquals("HTTP_REFERER", $comment->server_http_referer);
    $this->assertEquals("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assertEquals("SERVER_NAME", $comment->server_name);
    $this->assertEquals("QUERY_STRING", $comment->server_query_string);
    $this->assertEquals("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assertEquals("REMOTE_HOST", $comment->server_remote_host);
    $this->assertEquals("REMOTE_PORT", $comment->server_remote_port);

    $this->assertTrue(!empty($comment->created));
  }
}
