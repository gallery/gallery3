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
class block_manager_Core {
  static function get_active($location) {
    return unserialize(module::get_var("gallery", "blocks_$location", "a:0:{}"));
  }

  static function set_active($location, $blocks) {
    module::set_var("gallery", "blocks_$location", serialize($blocks));
  }

  static function add($location, $module_name, $block_id) {
    $blocks = block_manager::get_active($location);
    $blocks[random::int()] = array($module_name, $block_id);

    block_manager::set_active($location, $blocks);
  }

  static function activate_blocks($module_name) {
    $block_class = "{$module_name}_block";
    if (class_exists($block_class) && method_exists($block_class, "get_site_list")) {
      $blocks = call_user_func(array($block_class, "get_site_list"));
      foreach (array_keys($blocks) as $block_id) {
        block_manager::add("site_sidebar", $module_name, $block_id);
      }
    }
  }

  static function remove($location, $block_id) {
    $blocks = block_manager::get_active($location);
    unset($blocks[$block_id]);
    block_manager::set_active($location, $blocks);
  }

  static function remove_blocks_for_module($location, $module_name) {
    $blocks = block_manager::get_active($location);
    foreach ($blocks as $key => $block) {
      if ($block[0] == $module_name) {
        unset($blocks[$key]);
      }
    }
    block_manager::set_active($location, $blocks);
  }

  static function deactivate_blocks($module_name) {
    $block_class = "{$module_name}_block";
    if (class_exists($block_class) && method_exists($block_class, "get_site_list")) {
      $blocks = call_user_func(array($block_class, "get_site_list"));
      foreach  (array_keys($blocks) as $block_id) {
        block_manager::remove_blocks_for_module("site_sidebar", $module_name);
      }
    }

    if (class_exists($block_class) && method_exists($block_class, "get_admin_list")) {
      $blocks = call_user_func(array($block_class, "get_admin_list"));
      foreach (array("dashboard_sidebar", "dashboard_center") as $location) {
        block_manager::remove_blocks_for_module($location, $module_name);
      }
    }
  }

  static function get_available_admin_blocks() {
    return self::_get_blocks("get_admin_list");
  }

  static function get_available_site_blocks() {
    return self::_get_blocks("get_site_list");
  }

  private static function _get_blocks($function) {
    $blocks = array();

    foreach (module::active() as $module) {
      $class_name = "{$module->name}_block";
      if (class_exists($class_name) && method_exists($class_name, $function)) {
        foreach (call_user_func(array($class_name, $function)) as $id => $title) {
          $blocks["{$module->name}:$id"] = $title;
        }
      }
    }
    return $blocks;
  }

  static function get_html($location, $theme=null) {
    $active = block_manager::get_active($location);
    $result = "";
    foreach ($active as $id => $desc) {
      if (class_exists("$desc[0]_block") && method_exists("$desc[0]_block", "get")) {
        $block = call_user_func(array("$desc[0]_block", "get"), $desc[1], $theme);
        if (!empty($block)) {
          $block->id = $id;
          $result .= $block;
        }
      }
    }
    return $result;
  }
}
