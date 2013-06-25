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
    $blocks = Gallery::module_hook($module_name, "Block", "get_site_list");
    if (!empty($blocks)) {
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
    foreach (array("site_sidebar", "dashboard_sidebar", "dashboard_center") as $location) {
      BlockManager::remove_blocks_for_module($location, $module_name);
    }
  }

  static function get_available_admin_blocks() {
    return static::_get_blocks("get_admin_list");
  }

  static function get_available_site_blocks() {
    return static::_get_blocks("get_site_list");
  }

  protected static function _get_blocks($function) {
    $blocks = array();

    foreach (Gallery::hook("Block", $function) as $module_name => $data) {
      foreach ($data as $id => $title) {
        $blocks["$module_name:$id"] = $title;
      }
    }

    return $blocks;
  }

  static function get_html($location, $theme=null) {
    $active = BlockManager::get_active($location);
    $result = "";
    foreach ($active as $id => $desc) {
      $block = Gallery::module_hook($desc[0], "Block", "get", array($desc[1], $theme));
      if (!empty($block)) {
        $block->id = $id;
        $result .= $block;
      }
    }
    return $result;
  }
}
