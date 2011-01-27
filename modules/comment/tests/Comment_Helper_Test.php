<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Comment_Helper_Test extends Gallery_Unit_Test_Case {
  private $_ip_address;
  private $_user_agent;

  public function setup() {
    $this->_ip_address = Input::instance()->ip_address;
    $this->_user_agent = request::user_agent();
    $this->_save = $_SERVER;

    $_SERVER["HTTP_ACCEPT"] = "HTTP_ACCEPT";
    $_SERVER["HTTP_ACCEPT_CHARSET"] = "HTTP_ACCEPT_CHARSET";
    $_SERVER["HTTP_ACCEPT_ENCODING"] = "HTTP_ACCEPT_ENCODING";
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "HTTP_ACCEPT_LANGUAGE";
    $_SERVER["HTTP_CONNECTION"] = "HTTP_CONNECTION";
    $_SERVER["HTTP_HOST"] = "HTTP_HOST";
    $_SERVER["HTTP_REFERER"] = "HTTP_REFERER";
    $_SERVER["QUERY_STRING"] = "QUERY_STRING";
    $_SERVER["REMOTE_ADDR"] = "REMOTE_ADDR";
    $_SERVER["REMOTE_HOST"] = "REMOTE_HOST";
    $_SERVER["REMOTE_PORT"] = "REMOTE_PORT";

    request::set_user_agent("HTTP_USER_AGENT");
  }

  public function teardown() {
    Input::instance()->ip_address = $this->_ip_address;
    request::set_user_agent($this->_user_agent);
    $_SERVER = $this->_save;
  }

  public function create_comment_for_guest_test() {
    $comment = ORM::factory("comment");
    $comment->item_id = item::root()->id;
    $comment->text = "text";
    $comment->author_id = identity::guest()->id;
    $comment->guest_name = "name";
    $comment->guest_email = "email@email.com";
    $comment->guest_url = "http://url.com";
    $comment->save();

    $this->assert_equal("name", $comment->author_name());
    $this->assert_equal("email@email.com", $comment->author_email());
    $this->assert_equal("http://url.com", $comment->author_url());
    $this->assert_equal("text", $comment->text);
    $this->assert_equal(1, $comment->item_id);

    $this->assert_equal("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assert_equal("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assert_equal("HTTP_ACCEPT", $comment->server_http_accept);
    $this->assert_equal("HTTP_ACCEPT_CHARSET", $comment->server_http_accept_charset);
    $this->assert_equal("HTTP_ACCEPT_ENCODING", $comment->server_http_accept_encoding);
    $this->assert_equal("HTTP_ACCEPT_LANGUAGE", $comment->server_http_accept_language);
    $this->assert_equal("HTTP_CONNECTION", $comment->server_http_connection);
    $this->assert_equal("HTTP_HOST", $comment->server_http_host);
    $this->assert_equal("HTTP_REFERER", $comment->server_http_referer);
    $this->assert_equal("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assert_equal("QUERY_STRING", $comment->server_query_string);
    $this->assert_equal("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assert_equal("REMOTE_HOST", $comment->server_remote_host);
    $this->assert_equal("REMOTE_PORT", $comment->server_remote_port);

    $this->assert_true(!empty($comment->created));
  }

  public function create_comment_for_user_test() {
    $admin = identity::admin_user();

    $comment = ORM::factory("comment");
    $comment->item_id = item::root()->id;
    $comment->text = "text";
    $comment->author_id = $admin->id;
    $comment->save();

    $this->assert_equal($admin->full_name, $comment->author_name());
    $this->assert_equal($admin->email, $comment->author_email());
    $this->assert_equal($admin->url, $comment->author_url());
    $this->assert_equal("text", $comment->text);
    $this->assert_equal(1, $comment->item_id);

    $this->assert_equal("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assert_equal("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assert_equal("HTTP_ACCEPT", $comment->server_http_accept);
    $this->assert_equal("HTTP_ACCEPT_CHARSET", $comment->server_http_accept_charset);
    $this->assert_equal("HTTP_ACCEPT_ENCODING", $comment->server_http_accept_encoding);
    $this->assert_equal("HTTP_ACCEPT_LANGUAGE", $comment->server_http_accept_language);
    $this->assert_equal("HTTP_CONNECTION", $comment->server_http_connection);
    $this->assert_equal("HTTP_HOST", $comment->server_http_host);
    $this->assert_equal("HTTP_REFERER", $comment->server_http_referer);
    $this->assert_equal("HTTP_USER_AGENT", $comment->server_http_user_agent);
    $this->assert_equal("QUERY_STRING", $comment->server_query_string);
    $this->assert_equal("REMOTE_ADDR", $comment->server_remote_addr);
    $this->assert_equal("REMOTE_HOST", $comment->server_remote_host);
    $this->assert_equal("REMOTE_PORT", $comment->server_remote_port);

    $this->assert_true(!empty($comment->created));
  }
}
