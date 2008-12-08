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
class menus_Core {
  public static function get_menu_items($theme) {
    $menu = new Menu();

    //  Call core menus first to establish the basic menu
    self::_get_module_menu_items("core", $menu, $theme);
    foreach (module::installed() as $module) {
      if ($module->name == "core") {
        continue;
      }
      self::_get_module_menu_items($module->name, $menu, $theme);
    }

    return $menu;
  }

  private static function _get_module_menu_items($module_name, $menu, $theme) {
    $class = "{$module_name}_menu";
    if (method_exists($class, "items")) {
      call_user_func_array(array($class, "items"), array(&$menu, $theme));
    }
  }
}