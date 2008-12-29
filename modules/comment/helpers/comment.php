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

/**
 * This is the API for handling comments.
 *
 * Note: by design, this class does not do any permission checking.
 */
class comment_Core {
  const SECONDS_IN_A_MINUTE = 60;
  const SECONDS_IN_AN_HOUR = 3600;
  const SECONDS_IN_A_DAY = 86400;
  const SECONDS_IN_A_MONTH = 2629744;
  const SECONDS_IN_A_YEAR = 31556926;

  /**
   * Create a new comment.
   * @param string  $author author's name
   * @param string  $email author's email
   * @param string  $text comment body
   * @param integer $item_id id of parent item
   * @param string  $url author's url
   * @return Comment_Model
   */
  static function create($author, $email, $text, $item_id, $url) {
    $comment = ORM::factory("comment");
    $comment->author = $author;
    $comment->email = $email;
    $comment->text = $text;
    $comment->item_id = $item_id;
    $comment->url = $url;
    $comment->ip_addr = Input::instance()->ip_address();
    $comment->user_agent = Kohana::$user_agent;
    $comment->created = time();

    // @todo Figure out how to mock up the test of the spam_filter
    if (module::is_installed("spam_filter") && !TEST_MODE) {
      spam_filter::verify_comment($comment);
    } else {
      $comment->published = true;
    }

    $comment->save();
    module::event("comment_created", $comment);

    return $comment;
  }

  /**
   * Update an existing comment.
   * @param Comment_Model $comment
   * @param string  $author author's name
   * @param string  $email author's email
   * @param string  $text comment body
   * @param string  $url author's url
   * @return Comment_Model
   */
  static function update($comment, $author, $email, $text, $url) {
    $comment->author = $author;
    $comment->email = $email;
    $comment->text = $text;
    $comment->url = $url;
    $comment->ip_addr = Input::instance()->ip_address();
    $comment->user_agent = Kohana::$user_agent;

    // @todo Figure out how to mock up the test of the spam_filter
    if (module::is_installed("spam_filter") && !TEST_MODE) {
      spam_filter::verify_comment($comment);
    }

    $comment->save();
    if ($comment->saved) {
      module::event("comment_updated", $comment);
    }

    return $comment;
  }

  static function get_add_form($item) {
    $form = new Forge("comments", "", "post");
    $group = $form->group("add_comment")->label(_("Add comment"));
    $group->input("author")  ->label(_("Author"))          ->id("gAuthor");
    $group->input("email")   ->label(_("Email"))           ->id("gEmail");
    $group->input("url")     ->label(_("Website (hidden)"))->id("gUrl");
    $group->textarea("text") ->label(_("Text"))            ->id("gText");
    $group->hidden("item_id")->value($item->id);
    $group->submit(_("Add"));
    $form->add_rules_from(ORM::factory("comment"));
    return $form;
  }

  static function get_edit_form($comment) {
    $form = new Forge("comments/{$comment->id}?_method=put", "", "post");
    $group = $form->group("edit_comment")->label(_("Edit comment"));
    $group->input("author") ->label(_("Author"))          ->id("gAuthor")->value($comment->author);
    $group->input("email")  ->label(_("Email"))           ->id("gEmail") ->value($comment->email);
    $group->input("url")    ->label(_("Website (hidden)"))->id("gUrl")   ->value($comment->url);
    $group->textarea("text")->label(_("Text"))            ->id("gText")  ->value($comment->text);
    $group->submit(_("Edit"));
    $form->add_rules_from($comment);
    return $form;
  }
}

