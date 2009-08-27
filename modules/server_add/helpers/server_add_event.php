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
class server_add_event_Core {
  static function admin_menu($menu, $theme) {
    $menu->get("settings_menu")
      ->append(Menu::factory("link")
               ->id("server_add")
               ->label(t("Server Add"))
               ->url(url::site("admin/server_add")));
  }

  static function site_menu($menu, $theme) {
    $item = $theme->item();
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));

    if ($item && user::active()->admin && $item->is_album() && !empty($paths)) {
      // This is a little tricky.  Normally there's an "Add Photo" menu option, but we want to
      // turn that into a dropdown if there are two different ways to add things.  Do that in a
      // portable way for now.  If we find ourselves duplicating this pattern, we should make an
      // API method for this.
      $server_add = Menu::factory("dialog")
        ->id("server_add")
        ->label(t("Add from server"))
        ->url(url::site("server_add/browse/$item->id"));
      $add_photos_item = $menu->get("add_photos_item");
      $add_photos_menu = $menu->get("add_photos_menu");

      if ($add_photos_item && !$add_photos_menu) {
        // Assuming that $add_menu is unset, create add_menu and add our item
        $menu->add_after(
          "add_photos_item",
          Menu::factory("submenu")
          ->id("add_photos_menu")
          ->label($add_photos_item->label)
          ->append(Menu::factory("dialog")
                   ->id("add_photos_submenu_item")
                   ->label(t("Simple Uploader"))
                   ->url($add_photos_item->url))
          ->append($server_add));
        $menu->remove("add_photos_item");
      } else if ($add_photos_menu) {
        // Append to the existing sub-menu
        $add_photos_menu->append($server_add);
      }
    }
  }
}
