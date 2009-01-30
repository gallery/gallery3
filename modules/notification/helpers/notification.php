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
  static function get_subscriptions($item_id, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    return ORM::factory("subscription")
      ->where("item_id", $item_id)
      ->where("user_id", $user->id)
      ->find_all();
  }

  static function is_watching($item_id, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    $count = ORM::factory("subscription")
      ->where("item_id", $item_id)
      ->where("user_id", $user->id)
      ->count_all();

    return $count > 0;
  }

  static function add_watch($item, $watch_children=false, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }
    $subscription = ORM::factory("subscription");
    $subscription->item_id = $item->id;
    $subscription->apply_to_children = $watch_children;
    $subscription->user_id = $user->id;
    $subscription->save();

    if ($watch_children && $item->is_album()) {
      $children = ORM::factory("item")
      ->viewable()
      ->where("parent_id", $item->id)
      ->find_all();
      foreach ($children as $child) {
        self::add_watch($child, $watch_children, $user);
      }
    }
  }

  static function remove_watch($item, $watch_children=false, $user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    $subscription = ORM::factory("subscription")
      ->where("item_id", $item->id)
      ->where("user_id", $user->id)
      ->find();
    $subscription->delete();

    if ($watch_children && $item->is_album()) {
      $children = ORM::factory("item")
      ->viewable()
      ->where("parent_id", $item->id)
      ->find_all();
      foreach ($children as $child) {
        self::remove_watch($child, $watch_children, $user);
      }
    }
  }

  static function get_subscribers($item_id) {
    return ORM::factory("subscription")
      ->where("item_id", $item_id)
      ->find_all();
  }
    
  static function count_subscribers($item_id) {
    return ORM::factory("subscription")
      ->where("item_id", $item_id)
      ->count_all();
  }
    
  static function get_watched_items($user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    return ORM::factory("subscription")
      ->where("user_id", $item_id)
      ->find_all();
  }

  static function count_watched_items($user=null) {
    if (empty($user)) {
      $user = user::active();
    }

    return ORM::factory("subscription")
      ->where("user_id", $item_id)
      ->count_all();
  }
}
