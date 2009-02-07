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
  private static $init;

  /**
   * Add a new graphics rule.
   *
   * Rules are applied to targets (thumbnails and resizes) in priority order.  Rules are functions
   * in the graphics class.  So for example, the following rule:
   *
   *   graphics::add_rule("core", "thumb", "resize",
   *                       array("width" => 200, "height" => 200, "master" => Image::AUTO), 100);
   *
   * Specifies that "core" is adding a rule to resize thumbnails down to a max of 200px on
   * the longest side.  The core module adds default rules at a priority of 100.  You can set
   * higher and lower priorities to perform operations before or after this fires.
   *
   * @param string  $module_name the module that added the rule
   * @param string  $target      the target for this operation ("thumb" or "resize")
   * @param string  $operation   the name of the operation
   * @param array   $args        arguments to the operation
   * @param integer $priority    the priority for this rule (lower priorities are run first)
   */
  static function add_rule($module_name, $target, $operation, $args, $priority) {
    $rule = ORM::factory("graphics_rule");
    $rule->module_name = $module_name;
    $rule->target = $target;
    $rule->operation = $operation;
    $rule->priority = $priority;
    $rule->args = serialize($args);
    $rule->save();

    self::mark_all_dirty();
  }

  /**
   * Remove all rules for this module
   * @param string $module_name
   */
  static function remove_rules($module_name) {
    $db = Database::instance();
    $result = $db->query("DELETE FROM `graphics_rules` WHERE `module_name` = '$module_name'");
    if ($result->count()) {
      self::mark_all_dirty();
    }
  }

  /**
   * Rebuild the thumb and resize for the given item.
   * @param Item_Model $item
   */
  static function generate($item) {
    if ($item->is_album()) {
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
    if ($item->resize_dirty && !$item->is_album()) {
      $ops["resize"] = $item->resize_path();
    }

    if (!$ops) {
      return;
    }

    foreach (array("thumb" => $item->thumb_path(),
                   "resize" => $item->resize_path()) as $target => $output_file) {
      $working_file = $input_file;
      foreach (ORM::factory("graphics_rule")
               ->where("target", $target)
               ->orderby("priority", "asc")
               ->find_all() as $rule) {
        $args = array($working_file, $output_file, unserialize($rule->args));
        call_user_func_array(array("graphics", $rule->operation), $args);
        $working_file = $output_file;
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
   * Resize an image.  Valid options are width, height and master.  Master is one of the Image
   * master dimension constants.
   *
   * @param string  $input_file
   * @param string  $output_file
   * @param array   $options
   */
  static function resize($input_file, $output_file, $options) {
    if (!self::$init) {
      self::init_toolkit();
    }

    Image::factory($input_file)
      ->resize($options["width"], $options["height"], $options["master"])
      ->save($output_file);
  }

  /**
   * Rotate an image.  Valid options are degrees
   *
   * @param string  $input_file
   * @param string  $output_file
   * @param array   $options
   */
  static function rotate($input_file, $output_file, $options) {
    if (!self::$init) {
      self::init_toolkit();
    }

    Image::factory($input_file)
      ->rotate($options["degrees"])
      ->save($output_file);
  }

  /**
   * Overlay an image on top of the input file.  Valid options are file, mime_type, position and
   * transparency_percent.
   * position is one of northwest, north, northeast, west, center, east, southwest, south, southeast
   *
   * @param string  $input_file
   * @param string  $output_file
   * @param array   $options
   */
  static function composite($input_file, $output_file, $options) {
    if (!self::$init) {
      self::init_toolkit();
    }

    list ($width, $height) = getimagesize($input_file);
    list ($w_width, $w_height) = getimagesize($options["file"]);

    $pad = 10;
    $top = $pad;
    $left = $pad;
    $y_center = max($height / 2 - $w_height / 2, $pad);
    $x_center = max($width / 2 - $w_width / 2, $pad);
    $bottom = max($height - $w_height - $pad, $pad);
    $right = max($width - $w_width - $pad, $pad);

    switch ($options["position"]) {
    case "northwest": $x = $left;     $y = $top;       break;
    case "north":     $x = $x_center; $y = $top;       break;
    case "northeast": $x = $right;    $y = $top;       break;
    case "west":      $x = $left;     $y = $y_center;  break;
    case "center":    $x = $x_center; $y = $y_center;  break;
    case "east":      $x = $right;    $y = $y_center;  break;
    case "southwest": $x = $left;     $y = $bottom;    break;
    case "south":     $x = $x_center; $y = $bottom;    break;
    case "southeast": $x = $right;    $y = $bottom;    break;
    }

    Image::factory($input_file)
      ->composite($options["file"], $x, $y, $options["transparency"])
      ->save($output_file);
  }

  /**
   * Return a query result that locates all items with dirty images.
   * @return Database_Result Query result
   */
  static function find_dirty_images_query() {
    return Database::instance()->query(
      "SELECT `id` FROM `items` " .
      "WHERE (`thumb_dirty` = 1 AND (`type` <> 'album' OR `right` - `left` > 1))" .
      "   OR (`resize_dirty` = 1 AND `type` = 'photo')");
  }

  /**
   * Mark all thumbnails and resizes as dirty.  They will have to be rebuilt.
   */
  static function mark_all_dirty() {
    $db = Database::instance();
    $db->query("UPDATE `items` SET `thumb_dirty` = 1, `resize_dirty` = 1");

    $count = self::find_dirty_images_query()->count();
    if ($count) {
      site_status::warning(
          t2("One of your photos is out of date. %link_startClick here to fix it%link_end",
             "%count of your photos are out of date. %link_startClick here to fix them%link_end",
             $count,
             array("link_start" => "<a href=\"" .
                   url::site(
                     "admin/maintenance/start/core_task::rebuild_dirty_images?csrf=__CSRF__") .
                   "\" class=\"gDialogLink\">",
                   "link_end" => "</a>")),
          "graphics_dirty");
    }
  }

  /**
   * Detect which graphics toolkits are available on this system.  Return an array of key value
   * pairs where the key is one of gd, imagemagick, graphicsmagick and the value is information
   * about that toolkit.  For GD we return the version string, and for ImageMagick and
   * GraphicsMagick we return the path to the directory containing the appropriate binaries.
   */
  static function detect_toolkits() {
    $gd = function_exists("gd_info") ? gd_info() : array();
    return array("gd" => $gd,
                 "imagemagick" => dirname(exec("which convert")),
                 "graphicsmagick" => dirname(exec("which gm")));
  }

  /**
   * Choose which driver the Kohana Image library uses.
   */
  static function init_toolkit() {
    switch(module::get_var("core", "graphics_toolkit")) {
    case "gd":
      Kohana::config_set("image.driver", "GD");
      break;

    case "imagemagick":
      Kohana::config_set("image.driver", "ImageMagick");
      Kohana::config_set(
        "image.params.directory", module::get_var("core", "graphics_toolkit_path"));
      break;

    case "graphicsmagick":
      Kohana::config_set("image.driver", "GraphicsMagick");
      Kohana::config_set(
        "image.params.directory", module::get_var("core", "graphics_toolkit_path"));
      break;
    }

    self::$init = 1;
  }

  /**
   * Verify that a specific graphics function is available with the active toolkit.
   * @param  string  $func (eg rotate, resize)
   * @return boolean
   */
  static function can($func) {
    if (module::get_var("core", "graphics_toolkit") == "gd" &&
        $func == "rotate" &&
        !function_exists("imagerotate")) {
      return false;
    }

    return true;
  }
}
