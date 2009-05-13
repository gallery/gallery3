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
class core_menu_Core {
  static function site($menu, $theme) {
    if (file_exists(APPPATH . "controllers/scaffold.php") && user::active()->admin) {
      $menu->append($scaffold_menu = Menu::factory("submenu")
                    ->id("scaffold")
                    ->label("Scaffold"));
      $scaffold_menu->append(Menu::factory("link")
                             ->id("scaffold_home")
                             ->label("Dashboard")
                             ->url(url::site("scaffold")));
    }

    $menu->append(Menu::factory("link")
                  ->id("home")
                  ->label(t("Home"))
                  ->url(url::site("albums/1")));

    $item = $theme->item();

    if (user::active()->admin || ($item && access::can("edit", $item))) {
      $menu->append($options_menu = Menu::factory("submenu")
                    ->id("options_menu")
                    ->label(t("Options")));

      if ($item && access::can("edit", $item)) {
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("edit_item")
                   ->label($item->is_album() ? t("Edit album") : t("Edit photo"))
                   ->url(url::site("form/edit/{$item->type}s/$item->id")));

        // @todo Move album options menu to the album quick edit pane
        // @todo Create resized item quick edit pane menu
        if ($item->is_album()) {
          $options_menu
            ->append(Menu::factory("dialog")
                     ->id("add_item")
                     ->label(t("Add a photo"))
                     ->url(url::site("simple_uploader/app/$item->id")))
            ->append(Menu::factory("dialog")
                     ->id("add_album")
                     ->label(t("Add an album"))
                     ->url(url::site("form/add/albums/$item->id?type=album")))
            ->append(Menu::factory("dialog")
                     ->id("edit_permissions")
                     ->label(t("Edit permissions"))
                     ->url(url::site("permissions/browse/$item->id")));
        }
      }
    }

    if (user::active()->admin) {
      $menu->append($admin_menu = Menu::factory("submenu")
                    ->id("admin_menu")
                    ->label(t("Admin")));
      self::admin($admin_menu, $theme);
      foreach (module::installed() as $module) {
        if ($module->name == "core") {
          continue;
        }
        $class = "{$module->name}_menu";
        if (method_exists($class, "admin")) {
          call_user_func_array(array($class, "admin"), array(&$admin_menu, $theme));
        }
      }
    }
  }

  static function album($menu, $theme) {
  }

  static function photo($menu, $theme) {
    if (access::can("view_full", $theme->item())) {
      $menu
        ->append(Menu::factory("link")
               ->id("fullsize")
               ->label(t("View full size"))
               ->url("#")
               ->css_class("gFullSizeLink"));
    }
    $menu
      ->append(Menu::factory("link")
               ->id("album")
               ->label(t("Return to album"))
               ->url($theme->item()->parent()->url("show={$theme->item->id}"))
               ->css_id("gAlbumLink"));
  }

  static function admin($menu, $theme) {
    $menu
      ->append(Menu::factory("link")
               ->id("dashboard")
               ->label(t("Dashboard"))
               ->url(url::site("admin")))
      ->append(Menu::factory("submenu")
               ->id("settings_menu")
               ->label(t("Settings"))
               ->append(Menu::factory("link")
                        ->id("graphics_toolkits")
                        ->label(t("Graphics"))
                        ->url(url::site("admin/graphics")))
               ->append(Menu::factory("link")
                        ->id("languages")
                        ->label(t("Languages"))
                        ->url(url::site("admin/languages")))
               ->append(Menu::factory("link")
                        ->id("l10n_mode")
                        ->label(Session::instance()->get("l10n_mode", false)
                                ? t("Stop translating") : t("Start translating"))
                        ->url(url::site("l10n_client/toggle_l10n_mode?csrf=" .
                                        access::csrf_token())))
               ->append(Menu::factory("link")
                        ->id("advanced")
                        ->label("Advanced")
                        ->url(url::site("admin/advanced_settings"))))
      ->append(Menu::factory("link")
               ->id("modules")
               ->label(t("Modules"))
               ->url(url::site("admin/modules")))
      ->append(Menu::factory("submenu")
               ->id("content_menu")
               ->label(t("Content")))
      ->append(Menu::factory("submenu")
               ->id("appearance_menu")
               ->label(t("Appearance"))
               ->append(Menu::factory("link")
                        ->id("themes")
                        ->label(t("Themes"))
                        ->url(url::site("admin/themes")))
               ->append(Menu::factory("link")
                        ->id("theme_details")
                        ->label(t("Theme Details"))
                        ->url(url::site("admin/theme_details"))))
      ->append(Menu::factory("link")
               ->id("maintenance")
               ->label(t("Maintenance"))
               ->url(url::site("admin/maintenance")))
      ->append(Menu::factory("submenu")
               ->id("statistics_menu")
               ->label(t("Statistics"))
               ->url("#"));
  }
}
