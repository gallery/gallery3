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
class developer_menu_Core {
  static function admin($menu, $theme) {
    $developer_menu = Menu::factory("submenu")
        ->id("developer_menu")
      ->label(t("Developer Tools"));
    $menu->append($developer_menu);
    
    $developer_menu
      ->append(Menu::factory("link")
          ->id("generate_menu")
          ->label(t("Generate"))
          ->url(url::site("admin/developer")));
    if (Session::instance()->get("profiler", false)) {
      $developer_menu->append(Menu::factory("link")
                              ->id("scaffold_profiler")
                              ->label("Profiling off")
                              ->url(url::site("admin/developer/session/profiler?value=0")));
    } else {
      $developer_menu->append(Menu::factory("link")
                              ->id("scaffold_profiler")
                              ->label("Profiling on")
                              ->url(url::site("admin/developer/session/profiler?value=1")));
    }

    if (Session::instance()->get("debug", false)) {
      $developer_menu->append(Menu::factory("link")
                              ->id("scaffold_debugger")
                              ->label("Debugging off")
                              ->url(url::site("admin/developer/session/debug?value=0")));
    } else {
      $developer_menu->append(Menu::factory("link")
                              ->id("scaffold_debugger")
                              ->label("Debugging on")
                              ->url(url::site("admin/developer/session/debug?value=1")));
    }
  }
}
