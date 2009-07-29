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
class gallery_Core {
  const VERSION = "3.0 git (pre-beta3)";

  /**
   * If Gallery is in maintenance mode, then force all non-admins to get routed to a "This site is
   * down for maintenance" page.
   */
  static function maintenance_mode() {
    $maintenance_mode = Kohana::config("core.maintenance_mode", false, false);

    if (Router::$controller != "login" && !empty($maintenance_mode) && !user::active()->admin) {
      Router::$controller = "maintenance";
      Router::$controller_path = MODPATH . "gallery/controllers/maintenance.php";
      Router::$method = "index";
    }
  }

  /**
   * This function is called when the Gallery is fully initialized.  We relay it to modules as the
   * "gallery_ready" event.  Any module that wants to perform an action at the start of every
   * request should implement the <module>_event::gallery_ready() handler.
   */
  static function ready() {
    module::event("gallery_ready");
  }

  /**
   * This function is called right before the Kohana framework shuts down.  We relay it to modules
   * as the "gallery_shutdown" event.  Any module that wants to perform an action at the start of
   * every request should implement the <module>_event::gallery_shutdown() handler.
   */
  static function shutdown() {
    module::event("gallery_shutdown");
  }

  /**
   * Return a unix timestamp in a user specified format including date and time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date_time($timestamp) {
    return date(module::get_var("gallery", "date_time_format", "Y-M-d H:i:s"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the date.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function date($timestamp) {
    return date(module::get_var("gallery", "date_format", "Y-M-d"), $timestamp);
  }

  /**
   * Return a unix timestamp in a user specified format that's just the time.
   * @param $timestamp unix timestamp
   * @return string
   */
  static function time($timestamp) {
    return date(module::get_var("gallery", "time_format", "H:i:s"), $timestamp);
  }

  static function site_menu($menu, $theme) {
    if ($theme->page_type != "login") {
      $menu->append(Menu::factory("link")
                    ->id("home")
                    ->label(t("Home"))
                    ->url(url::site("albums/1")));

      $item = $theme->item();

      $can_edit = $item && access::can("edit", $item);
      $can_add = $item && access::can("add", $item);

      if ($can_add) {
        $menu->append(Menu::factory("dialog")
                      ->id("add_photos_item")
                      ->label(t("Add photos"))
                      ->url(url::site("simple_uploader/app/$item->id")));
      }

      $menu->append($options_menu = Menu::factory("submenu")
                    ->id("options_menu")
                    ->label(t("Options")));
      if ($item && ($can_edit || $can_add)) {
        if ($can_edit) {
          $options_menu
            ->append(Menu::factory("dialog")
                     ->id("edit_item")
                     ->label($item->is_album() ? t("Edit album") : t("Edit photo"))
                     ->url(url::site("form/edit/{$item->type}s/$item->id")));
        }

        // @todo Move album options menu to the album quick edit pane
        if ($item->is_album()) {
          if ($can_add) {
            $options_menu
              ->append(Menu::factory("dialog")
                       ->id("add_album")
                       ->label(t("Add an album"))
                       ->url(url::site("form/add/albums/$item->id?type=album")));
          }

          if ($can_edit) {
            $options_menu
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
        gallery::admin_menu($admin_menu, $theme);
        module::event("admin_menu", $admin_menu, $theme);
      }

      module::event("site_menu", $menu, $theme);
    }
  }

  static function admin_menu($menu, $theme) {
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
                        ->label(t("Advanced"))
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
                        ->label(t("Theme Choice"))
                        ->url(url::site("admin/themes")))
               ->append(Menu::factory("link")
                        ->id("theme_options")
                        ->label(t("Theme Options"))
                        ->url(url::site("admin/theme_options"))))
      ->append(Menu::factory("submenu")
               ->id("statistics_menu")
               ->label(t("Statistics")))
      ->append(Menu::factory("link")
               ->id("maintenance")
               ->label(t("Maintenance"))
               ->url(url::site("admin/maintenance")));
    return $menu;
  }
}