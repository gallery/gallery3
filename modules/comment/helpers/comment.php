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
class Comment_Core {
  /**
   * Create a new photo.
   * @param string  $author author's name
   * @param string  $email author's email
   * @param string  $text comment body
   * @param integer $item_id id of parent item
   * @param integer $datetime optional comment date and time in Unix format
   * @return Comment_Model
   */
  static function create($author, $email, $text, $item_id, $datetime=NULL) {
    if (is_null($datetime)) {
      $datetime = time();
    }

    $comment = ORM::factory("comment");
    $comment->author = $author;
    $comment->email = $email;
    $comment->text = $text;
    $comment->datetime = $datetime;
    $comment->item_id = $item_id;

    return $comment->save();
  }

  static function show_comments($item_id) {
    $v = new View('show_comments.html');
    $v->comment_list = Comment::show_comment_list($item_id);
    $v->comment_form = Comment::show_comment_form($item_id);
    $v->render(true);
  }

  static function show_comment_list($item_id) {
    $v = new View('comment_list.html');
    $v->item_id = $item_id;
    $v->comments = ORM::factory('comment')->where('item_id', $item_id)
      ->orderby('datetime', 'desc')
      ->find_all()->as_array();
    return $v;
  }

  static function show_comment_form($item_id) {
    $v = new View('comment_form.html');
    $v->item_id = $item_id;
    return $v;
  }
}

