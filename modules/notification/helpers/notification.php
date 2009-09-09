<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class notification {
  static function get_subscription($item_id, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    return ORM::factory("subscription")
      ->where("item_id", $item_id)
      ->where("user_id", $user->id)
      ->find();
  }

  static function is_watching($item, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    return ORM::factory("subscription")
      ->where("item_id", $item->id)
      ->where("user_id", $user->id)
      ->find()
      ->loaded;
  }

  static function add_watch($item, $user=null) {
    if ($item->is_album()) {
      if (empty($user)) {
        $user = user::active();
      }
      $subscription = ORM::factory("subscription");
      $subscription->item_id = $item->id;
      $subscription->user_id = $user->id;
      $subscription->save();
    }
  }

  static function remove_watch($item, $user=null) {
    if ($item->is_album()) {
      if (empty($user)) {
        $user = user::active();
      }

      $subscription = ORM::factory("subscription")
        ->where("item_id", $item->id)
        ->where("user_id", $user->id)
        ->find()->delete();
    }
  }

  static function get_subscribers($item) {
    // @todo don't access the user table directly
    // @todo only return distinct email addresses
    $users = ORM::factory("user")
      ->join("subscriptions", "users.id", "subscriptions.user_id")
      ->join("items", "subscriptions.item_id", "items.id")
      ->where("email IS NOT", null)
      ->where("items.left_ptr <=", $item->left_ptr)
      ->where("items.right_ptr >", $item->right_ptr)
      ->find_all();

    $subscribers = array();
    foreach ($users as $user) {
      if (access::user_can($user, "view", $item)) {
        $subscribers[$user->email] = 1;
      }
    }
    return array_keys($subscribers);
  }

  static function send_item_updated($item) {
    $subscribers = self::get_subscribers($item);
    if (!$subscribers) {
      return;
    }

    $v = new View("item_updated.html");
    $v->item = $item;
    $v->subject = $item->is_album() ?
      t("Album %title updated", array("title" => $item->original("title"))) :
      ($item->is_photo() ?
       t("Photo %title updated", array("title" => $item->original("title")))
       : t("Movie %title updated", array("title" => $item->original("title"))));

    self::_notify($subscribers, $item, $v->render(), $v->subject);
  }

  static function send_item_add($item) {
    $subscribers = self::get_subscribers($item);
    if (!$subscribers) {
      return;
    }

    $parent = $item->parent();
    $v = new View("item_added.html");
    $v->item = $item;
    $v->subject = $item->is_album() ?
      t("Album %title added to %parent_title",
        array("title" => $item->title, "parent_title" => $parent->title)) :
      ($item->is_photo() ?
       t("Photo %title added to %parent_title",
         array("title" => $item->title, "parent_title" => $parent->title)) :
       t("Movie %title added to %parent_title",
           array("title" => $item->title, "parent_title" => $parent->title)));

    self::_notify($subscribers, $item, $v->render(), $v->subject);
  }

  static function send_item_deleted($item) {
    $subscribers = self::get_subscribers($item);
    if (!$subscribers) {
      return;
    }

    $parent = $item->parent();
    $v = new View("item_deleted.html");
    $v->item = $item;
    $v->subject = $item->is_album() ?
      t("Album %title removed from %parent_title",
        array("title" => $item->title, "parent_title" => $parent->title)) :
      ($item->is_photo() ?
       t("Photo %title removed from %parent_title",
         array("title" => $item->title, "parent_title" => $parent->title))
       : t("Movie %title removed from %parent_title",
           array("title" => $item->title, "parent_title" => $parent->title)));

    self::_notify($subscribers, $item, $v->render(), $v->subject);
  }

  static function send_comment_published($comment) {
    $item = $comment->item();
    $subscribers = self::get_subscribers($item);
    if (!$subscribers) {
      return;
    }

    $v = new View("comment_published.html");
    $v->comment = $comment;
    $v->subject = $item->is_album() ?
      t("A new comment was published for album %title", array("title" => $item->title)) :
      ($item->is_photo() ?
       t("A new comment was published for photo %title", array("title" => $item->title))
       : t("A new comment was published for movie %title", array("title" => $item->title)));

    self::_notify($subscribers, $item, $v->render(), $v->subject);
  }

  static function send_pending_notifications() {
    foreach (Database::instance()
             ->select("DISTINCT email")
             ->from("pending_notifications")
             ->get() as $row) {
      $email = $row->email;
      $result = ORM::factory("pending_notification")
        ->where("email", $email)
        ->find_all();
      if ($result->count() == 1) {
        $pending = $result->current();
        Sendmail::factory()
          ->to($email)
          ->subject($pending->subject)
          ->header("Mime-Version", "1.0")
          ->header("Content-type", "text/html; charset=utf-8")
          ->message($pending->body)
          ->send();
        $pending->delete();
      } else {
        $text = "";
        foreach ($result as $pending) {
          $text .= $pending->text;
          $pending->delete();
        }
        Sendmail::factory()
          ->to($email)
          ->subject(t("Multiple events have occurred")) // @todo fix this terrible subject line
          ->header("Mime-Version", "1.0")
          ->header("Content-type", "text/html; charset=utf-8")
          ->message($text)
          ->send();
      }
    }
  }

  private static function _notify($subscribers, $item, $text, $subject) {
    if (!empty($subscribers)) {
      if (!batch::in_progress()) {
        Sendmail::factory()
          ->to($subscribers)
          ->subject($subject)
          ->header("Mime-Version", "1.0")
          ->header("Content-type", "text/html; charset=utf-8")
          ->message($text)
          ->send();
      } else {
        foreach ($subscribers as $subscriber) {
          $pending = ORM::factory("pending_notification");
          $pending->subject = $subject;
          $pending->text = $text;
          $pending->email = $subscriber;
          $pending->save();
        }
      }
    }
  }
}
