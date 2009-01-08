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
class akismet_event_Core {
  public static function comment_created($comment) {
    if (TEST_MODE) {
      return;
    }

    switch(akismet::check_comment($comment)) {
    case "spam":
      $comment->state = "spam";
      module::incr_var("comment", "spam_caught");
      break;

    case "ham":
      $comment->state = "published";
      break;

    case "unknown":
      $comment->state = "unpublished";
      break;
    }
    $comment->save();
  }

  public static function comment_changed($old, $new) {
    if (TEST_MODE) {
      return;
    }

    if ($old->state != "spam" && $new->state == "spam") {
      akismet::submit_spam($new);
    } else if ($old->state == "spam" && $new->state != "spam") {
      akismet::submit_ham($new);
    }
  }
}
