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
class dashboard_Core {
  public static function get_active() {
    return unserialize(module::get_var("core", "dashboard_blocks", "a:{}"));
  }

  public static function add_block($location, $module_name, $block_id) {
    $blocks = self::get_active();
    $blocks[$location][rand()] = array($module_name, $block_id);
    module::set_var("core", "dashboard_blocks", serialize($blocks));
  }

  public static function remove_block($location, $block_id) {
    $blocks = self::get_active();
    unset($blocks[$location][$block_id]);
    unset($blocks[$location][$block_id]);
    module::set_var("core", "dashboard_blocks", serialize($blocks));
  }

  public static function get_available() {
    $blocks = array();

    foreach (module::installed() as $module) {
      if (method_exists("{$module->name}_dashboard", "get_list")) {
        foreach (call_user_func(array("{$module->name}_dashboard", "get_list")) as $id => $title) {
          $blocks["{$module->name}:$id"] = $title;
        }
      }
    }
    return $blocks;
  }

  public static function get_blocks($blocks) {
    $result = "";
    foreach ($blocks as $id => $desc) {
      if (method_exists("$desc[0]_dashboard", "get_block")) {
        $block = call_user_func(array("$desc[0]_dashboard", "get_block"), $desc[1]);
        $block->id = $id;
        $result .= $block;
      }
    }
    return $result;
  }
}

