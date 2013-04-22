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
class Gallery_BlockManager {
  static function get_active($location) {
    return unserialize(Module::get_var("gallery", "blocks_$location", "a:0:{}"));
  }

  static function set_active($location, $blocks) {
    Module::set_var("gallery", "blocks_$location", serialize($blocks));
  }

  static function add($location, $module_name, $block_id) {
    $blocks = BlockManager::get_active($location);
    $blocks[Random::int()] = array($module_name, $block_id);

    BlockManager::set_active($location, $blocks);
  }

  static function activate_blocks($module_name) {
    $block_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Block";
    if (class_exists($block_class) && method_exists($block_class, "get_site_list")) {
      $blocks = call_user_func(array($block_class, "get_site_list"));
      foreach (array_keys($blocks) as $block_id) {
        BlockManager::add("site_sidebar", $module_name, $block_id);
      }
    }
  }

  static function remove($location, $block_id) {
    $blocks = BlockManager::get_active($location);
    unset($blocks[$block_id]);
    BlockManager::set_active($location, $blocks);
  }

  static function remove_blocks_for_module($location, $module_name) {
    $blocks = BlockManager::get_active($location);
    foreach ($blocks as $key => $block) {
      if ($block[0] == $module_name) {
        unset($blocks[$key]);
      }
    }
    BlockManager::set_active($location, $blocks);
  }

  static function deactivate_blocks($module_name) {
    $block_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Block";
    if (class_exists($block_class) && method_exists($block_class, "get_site_list")) {
      $blocks = call_user_func(array($block_class, "get_site_list"));
      foreach  (array_keys($blocks) as $block_id) {
        BlockManager::remove_blocks_for_module("site_sidebar", $module_name);
      }
    }

    if (class_exists($block_class) && method_exists($block_class, "get_admin_list")) {
      $blocks = call_user_func(array($block_class, "get_admin_list"));
      foreach (array("dashboard_sidebar", "dashboard_center") as $location) {
        BlockManager::remove_blocks_for_module($location, $module_name);
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

    foreach (Module::active() as $module) {
      $class_name = "Hook_" . Inflector::convert_module_to_class_name($module->name) . "Block";
      if (class_exists($class_name) && method_exists($class_name, $function)) {
        foreach (call_user_func(array($class_name, $function)) as $id => $title) {
          $blocks["{$module->name}:$id"] = $title;
        }
      }
    }
    return $blocks;
  }

  static function get_html($location, $theme=null) {
    $active = BlockManager::get_active($location);
    $result = "";
    foreach ($active as $id => $desc) {
      $class_name = "Hook_" . Inflector::convert_module_to_class_name($desc[0]) . "Block";
      if (class_exists($class_name) && method_exists($class_name, "get")) {
        $block = call_user_func(array($class_name, "get"), $desc[1], $theme);
        if (!empty($block)) {
          $block->id = $id;
          $result .= $block;
        }
      }
    }
    return $result;
  }
}
