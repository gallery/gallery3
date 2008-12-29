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
class Comment_Helper_Test extends Unit_Test_Case {
  private $_ip_address;
  private $_user_agent;

  public function setup() {
    $this->_ip_address = Input::instance()->ip_address;
    $this->_user_agent = Kohana::$user_agent;
  }

  public function teardown() {
    Input::instance()->ip_address = $this->_ip_address;
    Kohana::$user_agent = $this->_user_agent;
  }

  public function create_comment_test() {
    $rand = rand();

    Input::instance()->ip_address = "1.1.1.1";
    Kohana::$user_agent = "Gallery3 Unit Test";

    $comment = comment::create($rand, $rand, $rand, $rand, $rand, $rand);

    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal($rand, $comment->url);
    $this->assert_equal("1.1.1.1", $comment->ip_addr);
    $this->assert_equal("Gallery3 Unit Test", $comment->user_agent);
    $this->assert_true(!empty($comment->created));
  }

  public function update_comment_test() {
    $rand = rand();
    Input::instance()->ip_address = "1.1.1.1";
    Kohana::$user_agent = "Gallery3 Unit Test";
    $comment = comment::create($rand, $rand, $rand, $rand, $rand, $rand);

    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal($rand, $comment->url);
    $this->assert_equal("1.1.1.1", $comment->ip_addr);
    $this->assert_equal("Gallery3 Unit Test", $comment->user_agent);
    $this->assert_true(!empty($comment->created));

    $rand2 = rand();
    Input::instance()->ip_address = "1.1.1.2";
    Kohana::$user_agent = "Gallery3 Unit Test New Agent";
    comment::update($comment, $rand2, $rand2, $rand2, $rand2, $rand2);
    $this->assert_equal($rand2, $comment->author);
    $this->assert_equal($rand2, $comment->email);
    $this->assert_equal($rand2, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal("1.1.1.2", $comment->ip_addr);
    $this->assert_equal($rand2, $comment->url);
    $this->assert_equal("Gallery3 Unit Test New Agent", $comment->user_agent);
  }

  public function update_comment_no_change_test() {
    $rand = rand();
    Input::instance()->ip_address = "1.1.1.1";
    Kohana::$user_agent = "Gallery3 Unit Test";
    $comment = comment::create($rand, $rand, $rand, $rand, $rand, $rand);

    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal($rand, $comment->url);
    $this->assert_true(!empty($comment->created));
    $this->assert_equal("1.1.1.1", $comment->ip_addr);
    $this->assert_equal("Gallery3 Unit Test", $comment->user_agent);

    comment::update($comment, $rand, $rand, $rand, $rand, $rand);
    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal($rand, $comment->url);
    $this->assert_equal("1.1.1.1", $comment->ip_addr);
    $this->assert_equal("Gallery3 Unit Test", $comment->user_agent);
  }
}
