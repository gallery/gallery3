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
      ->where("items.left <=", $item->left)
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
    $body->subject = $old->is_album() ?
      t("Album %title updated", array("title" => $old->title)) :
      t("Photo %title updated", array("title" => $old->title));
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
    $body->subject = $item->is_album() ?
      t("Album %title added to %parent_title",
        array("title" => $item->title, "parent_title" => $item->parent()->title)) :
      t("Photo %title added to %parent_title",
        array("title" => $item->title, "parent_title" => $item->parent()->title));
    $body->parent_title = $item->parent()->title;
    $body->type = $item->type;
    $body->item_title = $item->title;
    $body->description = $item->description;
    $body->url = url::site("{$item->type}s/$item->id", "http");

    self::_send_message($item, $body);
  }

  static function send_batch_add($parent_id) {
    $parent = ORM::factory("item", $parent_id);
    if ($parent->loaded) {
      $body = new View("batch_add.html");
      $body->subject = t("%parent_title Updated",
          array("parent_title" => $parent->title));
      $body->parent_title = $parent->title;
      $body->url = url::site("{$parent->type}s/$parent->id", "http");

      self::_send_message($parent, $body);
    }
  }

  static function send_item_deleted($item) {
    $parent = $item->parent();
    $body = new View("item_deleted.html");
    $body->subject =
      $item->is_album() ?
      t("Album %title removed from %parent_title",
        array("title" => $item->title, "parent_title" => $item->parent()->title)) :
      t("Photo %title removed from %parent_title",
        array("title" => $item->title, "parent_title" => $item->parent()->title));
    $body->parent_title = $parent->title;
    $body->type = $item->type;
    $body->item_title = $item->title;
    $body->url = url::site("albums/$parent->id", "http");

    self::_send_message($item, $body);
  }

  static function send_comment_published($comment) {
    $item = $comment->item();
    $body = new View("comment_published.html");
    $body->subject = $item->is_album() ?
      t("A new comment was published for album %title", array("title" => $item->title)) :
      t("A new comment was published for photo %title", array("title" => $item->title));
    $body->text = $comment->text;
    if (!empty($comment->author_id)) {
      $author = ORM::factory("user", $comment->author_id);
      $body->author = empty($author->full_name) ? $author->name : $author->full_name;
    } else {
      $body->author = $comment->guest_name;
    }
    $body->url = url::site("albums/$item->id#comments", "http");

    self::_send_message($item, $body);
  }

  static function process_notifications() {
    Kohana::log("error", "processing notifications in shutdown");
  }
  
  private static function _send_message($item, $body) {
    $users = self::get_subscribers($item);
    if (!empty($users)) {
      Sendmail::factory()
        ->to($users)
        ->subject($body->subject)
        ->header("Mime-Version", "1.0")
        ->header("Content-type", "text/html; charset=utf-8")
        ->message($body->render())
        ->send();
    }
  }
}
