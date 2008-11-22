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

  static function get_add_form($item_id) {
    $form = new Forge(url::site("comments"), "", "post");
    $group = $form->group(_("Add Comment"));
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
    $group = $form->group(_("Edit Comment"));
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
      ->orderby('datetime', 'asc')
      ->find_all();

    if (!$comments->count()) {
      rest::http_status(rest::BAD_REQUEST);
      return;
    }

    switch (rest::output_format()) {
    case "atom":
      rest::http_content_type(rest::ATOM);
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

  }

  /*
   * This is way too complicated and needs lots of cleanup, but it provides a valid feed.
   * @todo Fix double slashes in some URIs.
   * @todo Write Unix -> RFC3339 datetime conversion helper.
   * @todo Abstract away as much code as possible.
   * @todo Show photo title instead of photo ID.
   * @todo Show comment numbers relative to current item, not its database ID.
   */
  public static function get_atom_feed($comments) {
    $latest_comment = 0;
    $base_url = sprintf("http://%s%s", $_SERVER["HTTP_HOST"], url::base(true));
    $absoluteUrl = html::specialchars($base_url . url::current(true));

    $feed = new Atom_Feed("feed");
    $feed->link()
      ->rel("self")
      ->href($absoluteUrl);

    foreach ($comments as $comment) {
      if ($comment->datetime > $latest_comment) {
        $latest_comment = $comment->datetime;
      }

      $feed->entry()
        ->id(sprintf("%s%scomments/%s", $base_url, url::site(), $comment->id))
        ->updated(date("Y-m-d\TH:i:s", $comment->datetime) . "Z")
        ->title(sprintf(_("Comment #%d"), $comment->id))
        ->content($comment->text, "html")
        ->author()
          ->name($comment->author)
          ->email($comment->email)
          ->uri(sprintf("%s%susers/%s", $base_url, url::site(), $comment->id));
    }
    $item_id = $comment->item_id;

    $feed->id($absoluteUrl)
      ->title(sprintf(_("Comments on photo %d"), $item_id), "text")
      ->updated(date("Y-m-d\TH:i:s", $latest_comment) . "Z");
    $feed->link()
      ->rel("related")
      ->type("application/atom+xml")
      ->title(_("Get photo meta data"))
      ->href(sprintf("%s%sphotos/%s", $base_url, url::site(), $item_id));
    $feed->link()
      ->rel("related")
      ->type("image/jpeg")
      ->title("Download photo")
      ->href(sprintf("%s%sphotos/%s", $base_url, url::site(), $item_id));

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
    $seconds_since_midnight = $date_info_now['hours'] * comment::SECONDS_IN_AN_HOUR
      + $date_info_now['minutes'] * comment::SECONDS_IN_A_MINUTE + $date_info_now['seconds'];

    /* Construct message depending on how much time passed. */
    if ($elapsed_years > 0) {
      $message = sprintf(_('said %d years ago'), $elapsed_years);
    } else if ($elapsed_months > 0) {
      $message = sprintf(_('said %d months ago'), $elapsed_months);
    } else {
      if ($time_difference < $seconds_since_midnight) {
        $message = _('said today');
      } else if ($time_difference < $seconds_since_midnight + comment::SECONDS_IN_A_DAY) {
        $message = _('said yesterday');
      } else {
        $message = sprintf(_('said %d days ago'), $elapsed_days);
      }
    }
    return $message;
   }
}

