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
class core_menu_Core {
  public static function site($menu, $theme) {
    $menu
      ->append(Menu::factory("link")
               ->id("home")
               ->label(_("Home"))
               ->url(url::base()))
      ->append(Menu::factory("link")
               ->id("browse")
               ->label(_("Browse"))
               ->url(url::site("albums/1")));

    $item = $theme->item();

    if (!user::active()->guest) {
      $menu->append($admin_menu = Menu::factory("submenu")
                    ->id("admin_menu")
                    ->label(_("Admin")));
    }

    if ($item && access::can("edit", $item)) {
      $menu->append($options_menu = Menu::factory("submenu")
        ->id("options_menu")
        ->label(_("Options"))
        ->append(Menu::factory("dialog")
                 ->id("edit_item")
                 ->label($item->type == "album" ? _("Edit album") : _("Edit photo"))
                 ->url(url::site("form/edit/{$item->type}s/$item->id"))));

      if ($item->type == "album") {
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("add_item")
                   ->label(_("Add a photo"))
                   ->url(url::site("form/add/albums/$item->id?type=photo")))
          ->append(Menu::factory("dialog")
                   ->id("add_album")
                   ->label(_("Add an album"))
                   ->url(url::site("form/add/albums/$item->id?type=album")));
      }
    }

    if (user::active()->admin) {
      $admin_menu->append(Menu::factory("link")
                          ->id("site_admin")
                          ->label(_("Site Admin"))
                          ->url(url::site("admin")));
    }
  }

  public static function admin($menu, $theme) {
    $menu
      ->append(Menu::factory("link")
               ->id("dashboard")
               ->label(_("Dashboard"))
               ->url(url::site("admin")))
      ->append(Menu::factory("submenu")
               ->id("general_settings_menu")
               ->label(_("General Settings"))
               ->append(Menu::factory("link")
                        ->id("graphics_toolkits")
                        ->label(_("Graphics"))
                        ->url(url::site("admin/graphics"))))
      ->append(Menu::factory("link")
               ->id("modules")
               ->label(_("Modules"))
               ->url(url::site("admin/modules")))
      ->append(Menu::factory("submenu")
               ->id("content_menu")
               ->label(_("Content")))
      ->append(Menu::factory("submenu")
               ->id("presentation_menu")
               ->label(_("Presentation"))
               ->append(Menu::factory("link")
                        ->id("themes")
                        ->label(_("Themes"))
                        ->url("#"))
               ->append(Menu::factory("link")
                        ->id("image_sizes")
                        ->label(_("Image Sizes"))
                        ->url("#")))
      ->append(Menu::factory("submenu")
               ->id("users_groups_menu")
               ->label(_("Users/Groups")))
      ->append(Menu::factory("link")
               ->id("maintenance")
               ->label(_("Maintenance"))
               ->url(url::site("admin/maintenance")))
      ->append(Menu::factory("link")
               ->id("statistics")
               ->label(_("Statistics"))
               ->url("#"));
  }
}
