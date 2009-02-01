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
    $users = ORM::factory("user")
      ->join("subscriptions", "users.id", "subscriptions.user_id")
      ->join("items", "subscriptions.item_id", "items.id")
      ->where("email IS NOT", null)
      ->where("items.left <", $item->left)
      ->where("items.right >", $item->right)
      ->find_all();

    $subscribers = array();
    foreach ($users as $user) {
      $subscribers[] = $user->email;
    }
    return $subscribers;
  }
    
  static function send_item_changed($old, $new=null) {
    $users = self::get_subscribers($item);
  }

  static function send_item_add($item) {
    $users = self::get_subscribers($item);
    Sendmail::factory()
      ->to($users)
      ->from("from@gallery3.com")
      ->subject(t("Item added to Gallery3"))
      ->message($item->title)
      ->send();
  }

  static function send_item_delete($item) {
    $users = self::get_subscribers($item);
    Sendmail::factory()
      ->to($users)
      ->from("from@gallery3.com")
      ->subject("Item deleted: $item->title")
      ->message("It was deleted")
      ->send();
  }

  static function send_comment_added($comment) {
    $users = self::get_subscribers($comment->item());
    Sendmail::factory()
      ->to($users)
      ->from("from@gallery3.com")
      ->subject("Comment added to $comment->$item->title")
      ->message($comment->text)
      ->send();
  }

  static function send_comment_changed($old, $new) {
    $users = self::get_subscribers($comment->item());
    Sendmail::factory()
      ->to($users)
      ->from("from@gallery3.com")
      ->subject("Comment updated on item: $comment->$item-title")
      ->message($new->text)
      ->send();
  }

}
