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
class notification_event_Core {
  static function item_updated($old, $new) {
    notification::send_item_updated($old, $new);
  }

  static function item_created($item) {
    notification::send_item_add($item);
  }

  static function item_before_delete($item) {
    notification::send_item_deleted($item);

    if (notification::is_watching($item)) {
      notification::remove_watch($item);
    }
  }

  static function comment_created($comment) {
    if ($comment->state == "published") {
      notification::send_comment_published($comment);
    }
  }

  static function comment_updated($old, $new) {
    if ($new->state == "published" && $old->state != "published") {
      notification::send_comment_published($new);
    }
  }

  static function user_before_delete($user) {
    ORM::factory("subscription")
      ->where("user_id", $user->id)
      ->delete_all();
  }

  static function operation($name, $item) {
    if ($name == "add") {
      $id = Session::instance()->get("notification_batch_item_id");
      if ($id && $item->id != $id) {
        notification::send_batch_add($id);
      }
      Session::instance()->set("notification_batch_item_id", $item->id);
    }
  }

  static function end_operation($name) {
    if ($name == "add") {
      $id = Session::instance()->get_once("notification_batch_item_id");
      if ($id) {
        notification::send_batch_add($id);
      }
    }
  }

}
