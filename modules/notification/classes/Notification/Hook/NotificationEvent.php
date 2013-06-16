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
class Notification_Hook_NotificationEvent {
  /**
   * Setup the relationship between Model_Item and Model_Subscription.
   */
  static function model_relationships($relationships) {
    $relationships["item"]["has_many"]["subscriptions"] = array();
    $relationships["subscription"]["belongs_to"]["item"] = array();
  }

  // The assumption is that the exception was logged at a lower level, but we
  // don't want to screw up the processing that was generating the notification
  // so we don't pass the exception up the call stack
  static function item_created($item) {
    try {
      Notification::send_item_add($item);
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::item_created() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function item_deleted($item) {
    try {
      Notification::send_item_deleted($item);

      if (Notification::is_watching($item)) {
        Notification::remove_watch($item);
      }
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::item_deleted() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function user_deleted($user) {
    DB::delete("subscriptions")
      ->where("user_id", "=", $user->id)
      ->execute();
  }

  static function identity_provider_changed($old_provider, $new_provider) {
    DB::delete("subscriptions")
      ->execute();
  }

  static function comment_created($comment) {
    try {
      if ($comment->state == "published") {
        Notification::send_comment_published($comment);
      }
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::comment_created() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function comment_updated($original, $new) {
    try {
      if ($new->state == "published" && $original->state != "published") {
        Notification::send_comment_published($new);
      }
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::comment_updated() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function user_before_delete($user) {
    try {
      DB::delete("subscriptions")
        ->where("user_id", "=", $user->id)
        ->execute();
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::user_before_delete() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function batch_complete() {
    try {
      Notification::send_pending_notifications();
    } catch (Exception $e) {
      Log::instance()->add(Log::ERROR, "Hook_NotificationEvent::batch_complete() failed");
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }

  static function site_menu($menu, $theme) {
    if (!Identity::active_user()->guest) {
      $item = $theme->item();

      if ($item && $item->is_album() && Access::can("view", $item)) {
        $watching = Notification::is_watching($item);

        $label = $watching ? t("Remove notifications") : t("Enable notifications");

        $menu->get("options_menu")
          ->append(Menu::factory("link")
                   ->id("watch")
                   ->label($label)
                   ->css_id("g-notify-link")
                   ->url(URL::site("notification/watch/$item->id?csrf=" . Access::csrf_token())));
      }
    }
  }

  static function show_user_profile($data) {
    // Guests don't see comment listings
    if (Identity::active_user()->guest) {
      return;
    }

    // Only logged in users can see their comment listings
    if (Identity::active_user()->id != $data->user->id) {
      return;
    }

    $view = new View("notification/user_profile.html");
    $view->subscriptions = array();
    foreach(ORM::factory("Subscription")
            ->where("user_id", "=", $data->user->id)
            ->find_all() as $subscription) {
      $item = $subscription->item;
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
