<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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
  // The assumption is that the exception was logged at a lower level, but we
  // don't want to screw up the processing that was generating the notification
  // so we don't pass the exception up the call stack
  static function item_created($item) {
    try {
      notification::send_item_add($item);
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::item_created() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function item_deleted($item) {
    try {
      notification::send_item_deleted($item);

      if (notification::is_watching($item)) {
        notification::remove_watch($item);
      }
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::item_deleted() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function user_deleted($user) {
    db::build()
      ->delete("subscriptions")
      ->where("user_id", "=", $user->id)
      ->execute();
  }

  static function identity_provider_changed($old_provider, $new_provider) {
    db::build()
      ->delete("subscriptions")
      ->execute();
  }

  static function comment_created($comment) {
    try {
      if ($comment->state == "published") {
        notification::send_comment_published($comment);
      }
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::comment_created() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function comment_updated($original, $new) {
    try {
      if ($new->state == "published" && $original->state != "published") {
        notification::send_comment_published($new);
      }
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::comment_updated() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function user_before_delete($user) {
    try {
      db::build()
        ->delete("subscriptions")
        ->where("user_id", "=", $user->id)
        ->execute();
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::user_before_delete() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function batch_complete() {
    try {
      notification::send_pending_notifications();
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo notification_event::batch_complete() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function site_menu($menu, $theme) {
    if (!identity::active_user()->guest) {
      $item = $theme->item();

      if ($item && $item->is_album() && access::can("view", $item)) {
        $watching = notification::is_watching($item);

        $label = $watching ? t("Remove notifications") : t("Enable notifications");

        $menu->get("options_menu")
          ->append(Menu::factory("link")
                   ->id("watch")
                   ->label($label)
                   ->css_id("g-notify-link")
                   ->url(url::site("notification/watch/$item->id?csrf=" . access::csrf_token())));
      }
    }
  }

  static function show_user_profile($data) {
    // Guests don't see comment listings
    if (identity::active_user()->guest) {
      return;
    }

    // Only logged in users can see their comment listings
    if (identity::active_user()->id != $data->user->id) {
      return;
    }

    $view = new View("user_profile_notification.html");
    $view->subscriptions = array();
    foreach(ORM::factory("subscription")
            ->where("user_id", "=", $data->user->id)
            ->find_all() as $subscription) {
      $item = ORM::factory("item")
          ->where("id", "=", $subscription->item_id)
          ->find();
      if ($item->loaded()) {
        $view->subscriptions[] = (object)array("id" => $subscription->id, "title" => $item->title,
                                               "url" => $item->url());
      }
    }
    if (count($view->subscriptions) > 0) {
      $data->content[] = (object)array("title" => t("Watching"), "view" => $view);
    }
  }
}