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
class graphics_Core {
  /**
   * Add a new graphics rule.
   *
   * Rules are applied to targets (thumbnails and resizes) in priority order.  Rules are functions
   * in the graphics class.  So for example, the following rule:
   *
   *   graphics::add_rule("core", "thumb", "resize", array(200, 200, Image::AUTO), 100);
   *
   * Specifies that "core" is adding a rule to resize thumbnails down to a max of 200px on
   * the longest side.  The core module adds default rules at a priority of 100.  You can set
   * higher and lower priorities to perform operations before or after this fires.
   *
   * @param string  $module_name the module that added the rule
   * @param string  $target      the target for this operation ("thumb" or "resize")
   * @param string  $operation   the name of the operation
   * @param array   $args        arguments to the operation
   * @param integer $priority    the priority for this function (lower priorities are run first)
   */
  public static function add_rule($module_name, $target, $operation, $args, $priority) {
    $rule = ORM::factory("graphics_rule");
    $rule->module_name = $module_name;
    $rule->target = $target;
    $rule->operation = $operation;
    $rule->priority = $priority;
    $rule->args = serialize($args);
    $rule->save();
  }

  /**
   * Remove all rules for this module
   * @param string $module_name
   */
  public static function remove_rules($module_name) {
    $db = Database::instance();
    $db->query("DELETE FROM `graphics_rules` WHERE `module_name` = '$module_name'");
  }

  /**
   * Rebuild the thumb and resize for the given item.
   * @param Item_Model $item
   */
  public static function generate($item) {
    if ($item->type == "album") {
      $cover = $item->album_cover();
      if (!$cover) {
        return;
      }
      $input_file = $cover->file_path();
    } else {
      $input_file = $item->file_path();
    }

    $ops = array();
    if ($item->thumb_dirty) {
      $ops["thumb"] = $item->thumb_path();
    }
    if ($item->resize_dirty && $item->type != "album") {
      $ops["resize"] = $item->resize_path();
    }

    if (!$ops) {
      return;
    }

    foreach (array("thumb" => $item->thumb_path(),
                   "resize" => $item->resize_path()) as $target => $output_file) {
      foreach (ORM::factory("graphics_rule")
               ->where("target", $target)
               ->orderby("priority", "asc")
               ->find_all() as $rule) {
        $args = array_merge(array($input_file, $output_file), unserialize($rule->args));
        call_user_func_array(array("graphics", $rule->operation), $args);
      }
    }

    if (!empty($ops["thumb"])) {
      $dims = getimagesize($item->thumb_path());
      $item->thumb_width = $dims[0];
      $item->thumb_height = $dims[1];
      $item->thumb_dirty = 0;
    }

    if (!empty($ops["resize"]))  {
      $dims = getimagesize($item->resize_path());
      $item->resize_width = $dims[0];
      $item->resize_height = $dims[1];
      $item->resize_dirty = 0;
    }
    $item->save();
  }

  /**
   * Wrapper around Image::resize
   * @param string  $input_file
   * @param string  $output_file
   * @param integer $width
   * @param integer $height
   * @param integer $master Master Dimension constant from the Image class
   */
  public static function resize($input_file, $output_file, $width, $height, $master) {
    Image::factory($input_file)
      ->resize($width, $height, $master)
      ->save($output_file);
  }
}
