<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class organize_event_Core {
  static function site_menu($menu, $theme) {
    $item = $theme->item();

    if ($item && $item->is_album() && access::can("edit", $item)) {
      $menu->get("options_menu")
        ->append(Menu::factory("dialog")
                 ->id("organize")
                 ->label(t("Organize album"))
                 ->css_id("g-menu-organize-link")
                 ->url(url::site("organize/dialog/{$item->id}")));
    }
  }

  static function context_menu($menu, $theme, $item) {
    if (access::can("edit", $item)) {
      if ($item->is_album()) {
        $menu->get("options_menu")
          ->append(Menu::factory("dialog")
                   ->id("organize")
                   ->label(t("Organize album"))
                   ->css_class("ui-icon-folder-open g-organize-link")
                   ->url(url::site("organize/dialog/{$item->id}")));
      } else {
        $parent = $item->parent();
        $menu->get("options_menu")
          ->append(Menu::factory("dialog")
                   ->id("move")
                   ->label(t("Move to another album"))
                   ->css_class("ui-icon-folder-open g-organize-link")
                   ->url(url::site("organize/dialog/{$parent->id}?selected_id={$item->id}")));
      }
    }
  }

  static function pre_deactivate($data) {
    if ($data->module == "rest") {
      $data->messages["warn"][] = t("The Organize module requires the Rest module.");
    }
  }

  static function module_change($changes) {
    if (!module::is_active("rest") || in_array("rest", $changes->deactivate)) {
      site_status::warning(
        t("The Organize module requires the Rest module.  <a href=\"%url\">Activate the Rest module now</a>",
          array("url" => html::mark_clean(url::site("admin/modules")))),
        "organize_needs_rest");
    } else {
      site_status::clear("organize_needs_rest");
    }
  }

}
