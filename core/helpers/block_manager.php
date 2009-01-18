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
class block_manager_Core {
  static function get_active() {
    return unserialize(module::get_var("core", "blocks", "a:0:{}"));
  }

  static function add($location, $module_name, $block_id) {
    $blocks = self::get_active();
    $blocks[$location][rand()] = array($module_name, $block_id);
    module::set_var("core", "blocks", serialize($blocks));
  }

  static function remove($location, $block_id) {
    $blocks = self::get_active();
    unset($blocks[$location][$block_id]);
    module::set_var("core", "blocks", serialize($blocks));
  }

  static function get_available() {
    $blocks = array();

    foreach (module::installed() as $module) {
      $class_name = "{$module->name}_block";
      if (method_exists($class_name, "get_list")) {
        foreach (call_user_func(array($class_name, "get_list")) as $id => $title) {
          $blocks["{$module->name}:$id"] = $title;
        }
      }
    }
    return $blocks;
  }

  static function get_html($location) {
    $active = self::get_active();
    if (empty($active[$location])) {
      return;
    }

    foreach ($active[$location] as $id => $desc) {
      if (method_exists("$desc[0]_block", "get")) {
        $block = call_user_func(array("$desc[0]_block", "get"), $desc[1]);
        $block->id = $id;
        $result .= $block;
      }
    }
    return $result;
  }
}

