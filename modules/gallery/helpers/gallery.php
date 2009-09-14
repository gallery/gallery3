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
  const VERSION = "3.0 beta 3";

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
                    ->url(item::root()->url()));

      $item = $theme->item();

      $can_edit = $item && access::can("edit", $item);
      $can_add = $item && access::can("add", $item);

      if ($can_add) {
        $menu->append($add_menu = Menu::factory("submenu")
                      ->id("add_menu")
                      ->label(t("Add")));
        $add_menu->append(Menu::factory("dialog")
                          ->id("add_photos_item")
                          ->label(t("Add photos"))
                          ->url(url::site("simple_uploader/app/$item->id")));
        if ($item->is_album()) {
          $add_menu->append(Menu::factory("dialog")
                            ->id("add_album_item")
                            ->label(t("Add an album"))
                            ->url(url::site("form/add/albums/$item->id?type=album")));
        }
      }

      $menu->append($options_menu = Menu::factory("submenu")
                    ->id("options_menu")
                    ->label(t("Photo options")));
      if ($item && ($can_edit || $can_add)) {
        if ($can_edit) {
          $options_menu->append(Menu::factory("dialog")
                                ->id("edit_item")
                                ->label($item->is_album() ? t("Edit album") : t("Edit photo"))
                                ->url(url::site("form/edit/{$item->type}s/$item->id")));
        }

        if ($item->is_album()) {
          $options_menu->label(t("Album options"));
          if ($can_edit) {
            $options_menu->append(Menu::factory("dialog")
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

  static function context_menu($menu, $theme, $item, $thumb_css_selector) {
    $menu->append($options_menu = Menu::factory("submenu")
                  ->id("options_menu")
                  ->label(t("Options"))
                  ->css_class("ui-icon-carat-1-n"));

    if (access::can("edit", $item)) {
      $page_type = $theme->page_type();
      switch ($item->type) {
      case "movie":
        $edit_title = t("Edit this movie");
        $delete_title = t("Delete this movie");
        break;

      case "album":
        $edit_title = t("Edit this album");
        $delete_title = t("Delete this album");
        break;

      default:
        $edit_title = t("Edit this photo");
        $delete_title = t("Delete this photo");
        break;
      }
      $cover_title = t("Choose as the album cover");
      $move_title = t("Move to another album");

      $csrf = access::csrf_token();

      $options_menu->append(Menu::factory("dialog")
                            ->id("edit")
                            ->label($edit_title)
                            ->css_class("ui-icon-pencil")
                            ->url(url::site("quick/form_edit/$item->id?page_type=$page_type")));


      if ($item->is_photo() && graphics::can("rotate")) {
        $options_menu
          ->append(
            Menu::factory("ajax_link")
            ->id("rotate_ccw")
            ->label(t("Rotate 90&deg; counter clockwise"))
            ->css_class("ui-icon-rotate-ccw")
            ->ajax_handler("function(data) { " .
                           "\$.gallery_replace_image(data, \$('$thumb_css_selector')) }")
            ->url(url::site("quick/rotate/$item->id/ccw?csrf=$csrf&page_type=$page_type")))
          ->append(
            Menu::factory("ajax_link")
            ->id("rotate_cw")
            ->label(t("Rotate 90&deg; clockwise"))
            ->css_class("ui-icon-rotate-cw")
            ->ajax_handler("function(data) { " .
                           "\$.gallery_replace_image(data, \$('$thumb_css_selector')) }")
            ->url(url::site("quick/rotate/$item->id/cw?csrf=$csrf&page_type=$page_type")));
      }

      // Don't move photos from the photo page; we don't yet have a good way of redirecting after
      // move
      if ($page_type == "album") {
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("move")
                   ->label($move_title)
                   ->css_class("ui-icon-folder-open")
                   ->url(url::site("move/browse/$item->id")));
      }

      $parent = $item->parent();
      if (access::can("edit", $parent)) {
        // We can't make this item the highlight if it's an album with no album cover, or if it's
        // already the album cover.
        if (($item->type == "album" && empty($item->album_cover_item_id)) ||
            ($item->type == "album" && $parent->album_cover_item_id == $item->album_cover_item_id) ||
            $parent->album_cover_item_id == $item->id) {
          $disabledState = " ui-state-disabled";
        } else {
          $disabledState = " ";
        }
        if ($item->parent()->id != 1) {
          $options_menu
            ->append(Menu::factory("ajax_link")
                     ->id("make_album_cover")
                     ->label($cover_title)
                     ->css_class("ui-icon-star")
                     ->ajax_handler("function(data) { window.location.reload() }")
                     ->url(url::site("quick/make_album_cover/$item->id?csrf=$csrf")));
        }
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("delete")
                   ->label($delete_title)
                   ->css_class("ui-icon-trash")
                   ->css_id("gQuickDelete")
                   ->url(url::site("quick/form_delete/$item->id?csrf=$csrf&page_type=$page_type")));
      }

      if ($item->is_album()) {
        $options_menu
          ->append(Menu::factory("dialog")
                   ->id("add_item")
                   ->label(t("Add a photo"))
                   ->css_class("ui-icon-plus")
                   ->url(url::site("simple_uploader/app/$item->id")))
          ->append(Menu::factory("dialog")
                   ->id("add_album")
                   ->label(t("Add an album"))
                   ->css_class("ui-icon-note")
                   ->url(url::site("form/add/albums/$item->id?type=album")))
          ->append(Menu::factory("dialog")
                   ->id("edit_permissions")
                   ->label(t("Edit permissions"))
                   ->css_class("ui-icon-key")
                   ->url(url::site("permissions/browse/$item->id")));
      }
    }
  }
}