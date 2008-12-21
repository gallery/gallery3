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
   * @return Comment_Model
   */
  static function create($author, $email, $text, $item_id) {
    $comment = ORM::factory("comment");
    $comment->author = $author;
    $comment->email = $email;
    $comment->text = $text;
    $comment->item_id = $item_id;
    $comment->created = time();

    $comment->save();
    module::event("comment_created", $comment);

    return $comment;
  }

  static function get_add_form($item_id) {
    $form = new Forge(url::site("comments"), "", "post");
    $group = $form->group("add_comment")->label(_("Add comment"));
    $group->input("author") ->label(_("Author")) ->id("gAuthor");
    $group->input("email")  ->label(_("Email"))  ->id("gEmail");
    $group->textarea("text")->label(_("Text"))   ->id("gText");
    $group->hidden("item_id")->value($item_id);
    $group->submit(_("Add"));
    $form->add_rules_from(ORM::factory("comment"));
    return $form;
  }

  static function get_edit_form($comment) {
    $form = new Forge(
      url::site("comments/{$comment->id}?_method=put"), "", "post");
    $group = $form->group("edit_comment")->label(_("Edit comment"));
    $group->input("author") ->label(_("Author")) ->id("gAuthor") ->value($comment->author);
    $group->input("email")  ->label(_("Email"))  ->id("gEmail")  ->value($comment->email);
    $group->textarea("text")->label(_("Text"))   ->id("gText")   ->value($comment->text);
    $group->submit(_("Edit"));
    $form->add_rules_from($comment);
    return $form;
  }

  /**
   * @todo Refactor this into a more generic location
   */
  private static function _add_validation_rules($model_name, $form) {
    $rules = ORM::factory($model_name)->validation_rules;
    foreach ($form->inputs as $name => $input) {
      if (isset($input->inputs)) {
        comment::_add_validation_rules($model_name, $input);
      }
      if (isset($rules[$name])) {
        $input->rules($rules[$name]);
      }
    }
  }

  static function block($theme, $show_add_form=true) {
    $block = new Block;
    $block->id = "gComments";
    $block->title = _("Comments");
    $block->content = comment::get_comments($theme->item(), "html");

    if ($show_add_form) {
      $block->content .= comment::get_add_form($theme->item())->render("form.html");
    }
    return $block;
  }

  // @todo Set proper Content-Type in a central place (REST_Controller::dispatch?).
  static function get_comments($item_id) {
    $comments = ORM::factory('comment')->where('item_id', $item_id)
      ->orderby('created', 'asc')
      ->find_all();

    if (!$comments->count()) {
      rest::http_status(rest::BAD_REQUEST);
      return;
    }

    switch (rest::output_format()) {
    case "atom":
      rest::http_content_type(rest::XML);
      print comment::get_atom_feed($comments);
      break;

    case "xml":
      rest::http_content_type(rest::XML);
      return xml::to_xml($comments, array("comments", "comment"));
      break;

    case "json":
      rest::http_content_type(rest::JSON);
      foreach ($comments as $comment) {
        $data[] = $comment->as_array();
      }
      return json_encode($data);

    default:
      foreach ($comments as $comment) {
        $v = new View("comment.html");
        $v->comment = $comment;
        $html[] = $v;
      }
      if (!empty($html)) {
        return "<ul>\n" . implode("\n", $html) . "</ul>\n";
      }
    }
  }

  public static function get_atom_entry($comment) {
    $feed = new Gallery_Atom_Entry();
    $feed->updated($comment->created)
      ->title(sprintf(_("Comment #%d"), $comment->id))
      ->content($comment->text)
      ->author()
        ->name($comment->author)
        ->email($comment->email)
        ->uri(url::abs_site("users/$comment->id"));
    $feed->link()->related_atom("photos/$comment->item_id");
    $feed->link()->related_image("photos/$comment->item_id");

    return $feed->as_xml();
  }

  /*
   * @todo Show photo title instead of photo ID.
   * @todo Put proper user ID into author URI.
   */
  public static function get_atom_feed($comments) {
    $latest_comment = $comments[0]->created;
    $item_id = $comments[0]->item_id;

    /* Set up feed header. */
    $feed = new Gallery_Atom_Feed();
    $feed->title(sprintf(_("Comments on photo %d"), $item_id));
    $feed->updated($latest_comment);
    $feed->link()->related_atom("photos/$item_id");
    $feed->link()->related_image("photos/$item_id");

    /* Add individual comments. */
    foreach ($comments as $id => $comment) {
      $feed->entry()
        ->id(url::abs_site("comments/$comment->id"))
        ->updated($comment->created)
        ->title(sprintf(_("Comment #%d"), $comments->count() - $id))
        ->content($comment->text)
        ->author()
          ->name($comment->author)
          ->email($comment->email)
          ->uri(url::abs_site("users/$comment->id"));
    }
    return $feed->as_xml();
  }

  /**
   * Format a human-friendly message showing the amount of time elapsed since the specified
   * timestamp (e.g. 'said today', 'said yesterday', 'said 13 days ago', 'said 5 months ago').
   *
   * @todo Take into account the viewer's time zone.
   * @todo Properly pluralize strings.
   *
   * @param integer $timestamp Unix format timestamp to compare with
   * @return string user-friendly string containing the amount of time passed
   */
  static function format_elapsed_time($timestamp) {
    $now = time();
    $time_difference = $now - $timestamp;
    $date_info_now = getdate($now);

    /* Calculate the number of days, months and years elapsed since the specified timestamp. */
    $elapsed_days = round($time_difference / comment::SECONDS_IN_A_DAY);
    $elapsed_months = round($time_difference / comment::SECONDS_IN_A_MONTH);
    $elapsed_years = round($time_difference / comment::SECONDS_IN_A_YEAR);
    $seconds_since_midnight = $date_info_now['hours'] * comment::SECONDS_IN_AN_HOUR +
      $date_info_now['minutes'] * comment::SECONDS_IN_A_MINUTE + $date_info_now['seconds'];

    /* Construct message depending on how much time passed. */
    if ($elapsed_years > 0) {
      $message = sprintf(_("said %d years ago"), $elapsed_years);
    } else if ($elapsed_months > 0) {
      $message = sprintf(_("said %d months ago"), $elapsed_months);
    } else {
      if ($time_difference < $seconds_since_midnight) {
        $message = _("said today");
      } else if ($time_difference < $seconds_since_midnight + comment::SECONDS_IN_A_DAY) {
        $message = _("said yesterday");
      } else {
        $message = sprintf(_("said %d days ago"), $elapsed_days);
      }
    }
    return $message;
   }
}

