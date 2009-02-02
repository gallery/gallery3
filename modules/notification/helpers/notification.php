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
    
  static function send_item_updated($old, $new) {
    $body = new View("item_updated.html");
    $body->subject = sprintf(t("Item %s updated"), $old->title);
    $body->type = ucfirst($old->type);
    $body->item_title = $old->title;
    $body->description = $item->description;
    $body->new_title = $old->title != $new->title ? $new->title : null;
    $body->new_description = $old->title != $new->description ? $new->description : null;
    $body->url = url::site("{$old->type}s/$old->id", "http");
    
    self::_send_message($old, $body);
  }

  static function send_item_add($item) {
    $body = new View("item_added.html");
    $body->subject = sprintf(t("Item added to %s"), $item->parent()->title);
    $body->parent_title = $item->parent()->title;
    $body->type = $item->type;
    $body->item_title = $item->title;
    $body->description = $item->description;
    $body->url = url::site("{$item->type}s/$item->id", "http");

    self::_send_message($item, $body);
  }

  static function send_item_delete($item) {
    $body = new View("item_deleted.html");
    $body->subject = sprintf(t("Item deleted from %s"), $item->parent()->title);
    
    self::_send_message($item, $body);
  }

  static function send_comment_added($comment) {
    $body = new View("comment_added.html");
    $body->subject = sprintf(t("Comment added to %s"), $comment->item()->title);
    
    self::_send_message($comment->item(), $body);
  }

  static function send_comment_changed($old, $new) {
    $body = new View("comment_changed.html");
    $body->subject = sprintf(t("Comment changed on %s"), $old->item()->title);

    self::_send_message($old->item(), $body);
  }

  private function _send_message($item, $body) {
    $users = self::get_subscribers($item);
    Sendmail::factory()
      ->to($users)
      ->subject($body->subject)
      ->header("Mime-Version", "1.0")
      ->header("Content-type", "text/html; charset=iso-8859-1")
      ->message($body->render())
      ->send();
  }
}
