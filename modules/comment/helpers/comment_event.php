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
class comment_event_Core {
  static function item_deleted($item) {
    db::build()
      ->delete("comments")
      ->where("item_id", "=", $item->id)
      ->execute();
  }

  static function user_deleted($user) {
    $guest = identity::guest();
    if (!empty($guest)) {          // could be empty if there is not identity provider
      db::build()
        ->update("comments")
        ->set("author_id", $guest->id)
        ->set("guest_email", null)
        ->set("guest_name", "guest")
        ->set("guest_url", null)
        ->where("author_id", "=", $user->id)
        ->execute();
    }
  }

  static function identity_provider_changed($old_provider, $new_provider) {
    $guest = identity::guest();
    db::build()
      ->update("comments")
      ->set("author_id", $guest->id)
      ->set("guest_email", null)
      ->set("guest_name", "guest")
      ->set("guest_url", null)
      ->execute();
  }

  static function admin_menu($menu, $theme) {
    $menu->get("settings_menu")
      ->append(Menu::factory("link")
               ->id("comment")
               ->label(t("Comments"))
               ->url(url::site("admin/comments")));

    $menu->get("content_menu")
      ->append(Menu::factory("link")
               ->id("comments")
               ->label(t("Comments"))
               ->url(url::site("admin/manage_comments")));
  }

  static function photo_menu($menu, $theme) {
    $menu
      ->append(Menu::factory("link")
               ->id("comments")
               ->label(t("View comments on this item"))
               ->url("#comments")
               ->css_id("g-comments-link"));
  }

  static function item_index_data($item, $data) {
    foreach (db::build()
             ->select("text")
             ->from("comments")
             ->where("item_id", "=", $item->id)
             ->execute() as $row) {
      $data[] = $row->text;
    }
  }

  static function show_user_profile($data) {
    $view = new View("user_profile_comments.html");
    $view->comments = ORM::factory("comment")
      ->order_by("created", "DESC")
      ->where("state", "=", "published")
      ->where("author_id", "=", $data->user->id)
      ->find_all();
    if ($view->comments->count()) {
      $data->content[] = (object)array("title" => t("Comments"), "view" => $view);
    }
  }
}
