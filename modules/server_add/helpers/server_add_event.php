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
class server_add_event_Core {
  static function admin_menu($menu, $theme) {
    $menu->get("settings_menu")
      ->append(Menu::factory("link")
               ->id("server_add")
               ->label(t("Server add"))
               ->url(url::site("admin/server_add")));
  }

  static function site_menu($menu, $theme) {
    $item = $theme->item();
    $paths = unserialize(module::get_var("server_add", "authorized_paths"));

    if ($item && identity::active_user()->admin && $item->is_album() && !empty($paths) &&
        is_writable($item->is_album() ? $item->file_path() : $item->parent()->file_path())) {
      $menu->get("add_menu")
        ->append(Menu::factory("dialog")
                 ->id("server_add")
                 ->label(t("Server add"))
                 ->url(url::site("server_add/browse/$item->id")));
    }
  }
}
