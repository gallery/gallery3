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

  /**
   * Format a human-friendly message showing the amount of time elapsed since the specified
   * timestamp (e.g. 'said today', 'said yesterday', 'said 13 days ago', 'said 5 months ago').
   *
   * @todo Take into account the viewer's time zone.
   * @todo Properly pluralize strings.
   * @todo Write test once we settle on the final forms of the messages.
   *
   * @param integer $timestamp Unix format timestamp to compare with
   * @return string user-friendly string containing the amount of time passed
   */
  static function format_elapsed_time($timestamp) {
    $now = time();
    $date_info_comment = getdate($timestamp);
    $date_info_now = getdate($now);

    /* Check if the comment was posted today or yesterday. */
    if ($date_info_comment['year'] == $date_info_now['year']
        && $date_info_comment['mon'] == $date_info_now['mon']) {
      if ($date_info_comment['mday'] == $date_info_now['mday']) {
        $message = _('said today');
      } else if ($date_info_comment['mday'] == $date_info_now['mday'] - 1) {
        $message = _('said yesterday');
      }
      return $message;
    }

    /* Calculate the number of days, months or years elapsed since the specified timestamp. */
    $seconds_in_a_day = 86400;
    $seconds_in_a_month = 2629744;
    $seconds_in_a_year = 31556926;
    $time_difference = $now - $timestamp;

    $elapsed_days = round($time_difference / $seconds_in_a_day);
    $elapsed_months = round($time_difference / $seconds_in_a_month);
    $elapsed_years = round($time_difference / $seconds_in_a_year);

    if ($elapsed_years > 0) {
      $message = sprintf(_('said %d years ago'), $elapsed_years);
    } else if ($elapsed_months > 0) {
      $message = sprintf(_('said %d months ago'), $elapsed_months);
    } else {
      $message = sprintf(_('said %d days ago'), $elapsed_days);
    }
    return $message;
  }
}

