<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
  private static $_rules_cache = array();

  /**
   * Add a new graphics rule.
   *
   * Rules are applied to targets (thumbnails and resizes) in priority order.  Rules are functions
   * in the graphics class.  So for example, the following rule:
   *
   *   graphics::add_rule("gallery", "thumb", "gallery_graphics::resize",
   *                       array("width" => 200, "height" => 200, "master" => Image::AUTO), 100);
   *
   * Specifies that "gallery" is adding a rule to resize thumbnails down to a max of 200px on
   * the longest side.  The gallery module adds default rules at a priority of 100.  You can set
   * higher and lower priorities to perform operations before or after this fires.
   *
   * @param string  $module_name the module that added the rule
   * @param string  $target      the target for this operation ("thumb" or "resize")
   * @param string  $operation   the name of the operation (<defining class>::method)
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
    $rule->active = true;
    $rule->save();

    graphics::mark_dirty($target == "thumb", $target == "resize");
  }

  /**
   * Remove any matching graphics rules
   * @param string  $module_name the module that added the rule
   * @param string  $target      the target for this operation ("thumb" or "resize")
   * @param string  $operation   the name of the operation(<defining class>::method)
   */
  static function remove_rule($module_name, $target, $operation) {
    db::build()
      ->delete("graphics_rules")
      ->where("module_name", "=", $module_name)
      ->where("target", "=", $target)
      ->where("operation", "=", $operation)
      ->execute();

    graphics::mark_dirty($target == "thumb", $target == "resize");
  }

  /**
   * Remove all rules for this module
   * @param string $module_name
   */
  static function remove_rules($module_name) {
    $status = db::build()
      ->delete("graphics_rules")
      ->where("module_name", "=", $module_name)
      ->execute();
    if (count($status)) {
      graphics::mark_dirty(true, true);
    }
  }

  /**
   * Activate the rules for this module, typically done when the module itself is deactivated.
   * Note that this does not mark images as dirty so that if you deactivate and reactivate a
   * module it won't cause all of your images to suddenly require a rebuild.
   */
  static function activate_rules($module_name) {
    db::build()
      ->update("graphics_rules")
      ->set("active", true)
      ->where("module_name", "=", $module_name)
      ->execute();
  }

  /**
   * Deactivate the rules for this module, typically done when the module itself is deactivated.
   * Note that this does not mark images as dirty so that if you deactivate and reactivate a
   * module it won't cause all of your images to suddenly require a rebuild.
   */
  static function deactivate_rules($module_name) {
    db::build()
      ->update("graphics_rules")
      ->set("active", false)
      ->where("module_name", "=", $module_name)
      ->execute();
  }

  /**
   * Rebuild the thumb and resize for the given item.
   * @param Item_Model $item
   */
  static function generate($item) {
    if ($item->is_album()) {
      if (!$cover = $item->album_cover()) {
        // This album has no cover; there's nothing to generate.  Because of an old bug, it's
        // possible that there's an album cover item id that points to an invalid item.  In that
        // case, just null out the album cover item id.  It's not optimal to do that at this low
        // level, but it's not trivial to find these cases quickly in an upgrade script and if we
        // don't do this, the album may be permanently marked as "needs rebuilding"
        //
        // ref: http://sourceforge.net/apps/trac/gallery/ticket/1172
        //      http://gallery.menalto.com/node/96926
        if ($item->album_cover_item_id) {
          $item->album_cover_item_id = null;
          $item->save();
        }
        return;
      }
      $input_file = $cover->file_path();
      $input_item = $cover;
    } else {
      $input_file = $item->file_path();
      $input_item = $item;
    }

    if ($item->thumb_dirty) {
      $ops["thumb"] = $item->thumb_path();
    }
    if ($item->resize_dirty && !$item->is_album() && !$item->is_movie()) {
      $ops["resize"] = $item->resize_path();
    }

    if (empty($ops)) {
      $item->thumb_dirty = 0;
      $item->resize_dirty = 0;
      $item->save();
      return;
    }

    try {
      foreach ($ops as $target => $output_file) {
        if ($input_item->is_movie()) {
          // Convert the movie to a JPG first
          $output_file = preg_replace("/...$/", "jpg", $output_file);
          try {
            movie::extract_frame($input_file, $output_file);
          } catch (Exception $e) {
            // Assuming this is MISSING_FFMPEG for now
            copy(MODPATH . "gallery/images/missing_movie.png", $output_file);
          }
          $working_file = $output_file;
        } else {
          $working_file = $input_file;
        }

        foreach (self::_get_rules($target) as $rule) {
          $args = array($working_file, $output_file, unserialize($rule->args), $item);
          call_user_func_array($rule->operation, $args);
          $working_file = $output_file;
        }
      }

      if (!empty($ops["thumb"])) {
        if (file_exists($item->thumb_path())) {
          $item->thumb_dirty = 0;
        } else {
          copy(MODPATH . "gallery/images/missing_photo.png", $item->thumb_path());
        }
        $dims = getimagesize($item->thumb_path());
        $item->thumb_width = $dims[0];
        $item->thumb_height = $dims[1];
      }

      if (!empty($ops["resize"]))  {
        if (file_exists($item->resize_path())) {
          $item->resize_dirty = 0;
        } else {
          copy(MODPATH . "gallery/images/missing_photo.png", $item->resize_path());
        }
        $dims = getimagesize($item->resize_path());
        $item->resize_width = $dims[0];
        $item->resize_height = $dims[1];
      }
      $item->save();
    } catch (Exception $e) {
      // Something went wrong rebuilding the image.  Leave it dirty and move on.
      // @todo we should handle this better.
      Kohana_Log::add("error", "Caught exception rebuilding image: {$item->title}\n" .
                  $e->getMessage() . "\n" . $e->getTraceAsString());
      throw $e;
    }
  }

  private static function _get_rules($target) {
    if (empty(self::$_rules_cache[$target])) {
      $rules = array();
      foreach (ORM::factory("graphics_rule")
               ->where("target", "=", $target)
               ->where("active", "=", true)
               ->order_by("priority", "asc")
               ->find_all() as $rule) {
        $rules[] = (object)$rule->as_array();
      }
      self::$_rules_cache[$target] = $rules;
    }
    return self::$_rules_cache[$target];
  }

  /**
   * Return a query result that locates all items with dirty images.
   * @return Database_Result Query result
   */
  static function find_dirty_images_query() {
    return db::build()
      ->from("items")
      ->and_open()
      ->and_open()
      ->where("thumb_dirty", "=", 1)
      ->and_open()
      ->where("type", "<>", "album")
      ->or_where("album_cover_item_id", "IS NOT", null)
      ->close()
      ->or_open()
      ->where("resize_dirty", "=", 1)
      ->where("type", "=", "photo")
      ->close()
      ->close()
      ->where("id", "<>", 1)
      ->close();
  }

  /**
   * Mark thumbnails and resizes as dirty.  They will have to be rebuilt.
   */
  static function mark_dirty($thumbs, $resizes) {
    if ($thumbs || $resizes) {
      $db = db::build()
        ->update("items");
      if ($thumbs) {
        $db->set("thumb_dirty", 1);
      }
      if ($resizes) {
        $db->set("resize_dirty", 1);
      }
      $db->execute();
    }

    $count = graphics::find_dirty_images_query()->count_records();
    if ($count) {
      site_status::warning(
          t2("One of your photos is out of date. <a %attrs>Click here to fix it</a>",
             "%count of your photos are out of date. <a %attrs>Click here to fix them</a>",
             $count,
             array("attrs" => html::mark_clean(sprintf(
               'href="%s" class="g-dialog-link"',
               url::site("admin/maintenance/start/gallery_task::rebuild_dirty_images?csrf=__CSRF__"))))),
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
    $toolkits = new stdClass();
    $toolkits->gd = new stdClass();
    $toolkits->imagemagick = new stdClass();
    $toolkits->graphicsmagick = new stdClass();

    // GD is special, it doesn't use exec()
    $gd = function_exists("gd_info") ? gd_info() : array();
    $toolkits->gd->name = "GD";
    if (!isset($gd["GD Version"])) {
      $toolkits->gd->installed = false;
      $toolkits->gd->error = t("GD is not installed");
    } else {
      $toolkits->gd->installed = true;
      $toolkits->gd->version = $gd["GD Version"];
      $toolkits->gd->rotate = function_exists("imagerotate");
      $toolkits->gd->sharpen = function_exists("imageconvolution");
      $toolkits->gd->binary = "";
      $toolkits->gd->dir = "";

      if (!$toolkits->gd->rotate && !$toolkits->gd->sharpen) {
        $toolkits->gd->error =
          t("You have GD version %version, but it lacks image rotation and sharpening.",
            array("version" => $gd["GD Version"]));
      } else if (!$toolkits->gd->rotate) {
        $toolkits->gd->error =
          t("You have GD version %version, but it lacks image rotation.",
            array("version" => $gd["GD Version"]));
      } else if (!$toolkits->gd->sharpen) {
        $toolkits->gd->error =
          t("You have GD version %version, but it lacks image sharpening.",
            array("version" => $gd["GD Version"]));
      }
    }

    if (!function_exists("exec")) {
      $toolkits->imagemagick->installed = false;
      $toolkits->imagemagick->error = t("ImageMagick requires the <b>exec</b> function");

      $toolkits->graphicsmagick->installed = false;
      $toolkits->graphicsmagick->error = t("GraphicsMagick requires the <b>exec</b> function");
    } else {
      // ImageMagick & GraphicsMagick
      $magick_kits = array(
          "imagemagick" => array(
            "name" => "ImageMagick", "binary" => "convert", "version_arg" => "-v",
            "version_regex" => "/Version: \S+ (\S+)/"),
          "graphicsmagick" => array(
            "name" => "GraphicsMagick", "binary" => "gm", "version_arg" => "version",
            "version_regex" => "/\S+ (\S+)/"));
      // Loop through the kits
      foreach ($magick_kits as $index => $settings) {
        $path = system::find_binary(
          $settings["binary"], module::get_var("gallery", "graphics_toolkit_path"));
        $toolkits->$index->name = $settings["name"];
        if ($path) {
          if (@is_file($path) &&
              preg_match(
                $settings["version_regex"], shell_exec($path . " " . $settings["version_arg"]), $matches)) {
            $version = $matches[1];

            $toolkits->$index->installed = true;
            $toolkits->$index->version = $version;
            $toolkits->$index->binary = $path;
            $toolkits->$index->dir = dirname($path);
            $toolkits->$index->rotate = true;
            $toolkits->$index->sharpen = true;
          } else {
            $toolkits->$index->installed = false;
            $toolkits->$index->error =
              t("%toolkit_name is installed, but PHP's open_basedir restriction prevents Gallery from using it.",
                array("toolkit_name" => $settings["name"]));
          }
        } else {
          $toolkits->$index->installed = false;
          $toolkits->$index->error =
            t("We could not locate %toolkit_name on your system.",
              array("toolkit_name" => $settings["name"]));
        }
      }
    }

    return $toolkits;
  }

  /**
   * This needs to be run once, after the initial install, to choose a graphics toolkit.
   */
  static function choose_default_toolkit() {
    // Detect a graphics toolkit
    $toolkits = graphics::detect_toolkits();
    foreach (array("imagemagick", "graphicsmagick", "gd") as $tk) {
      if ($toolkits->$tk->installed) {
        module::set_var("gallery", "graphics_toolkit", $tk);
        module::set_var("gallery", "graphics_toolkit_path", $toolkits->$tk->dir);
        break;
      }
    }

    if (!module::get_var("gallery", "graphics_toolkit")) {
      site_status::warning(
        t("Graphics toolkit missing!  Please <a href=\"%url\">choose a toolkit</a>",
          array("url" => html::mark_clean(url::site("admin/graphics")))),
        "missing_graphics_toolkit");
    }
  }

  /**
   * Choose which driver the Kohana Image library uses.
   */
  static function init_toolkit() {
    if (self::$init) {
      return;
    }
    switch(module::get_var("gallery", "graphics_toolkit")) {
    case "gd":
      Kohana_Config::instance()->set("image.driver", "GD");
      break;

    case "imagemagick":
      Kohana_Config::instance()->set("image.driver", "ImageMagick");
      Kohana_Config::instance()->set(
        "image.params.directory", module::get_var("gallery", "graphics_toolkit_path"));
      break;

    case "graphicsmagick":
      Kohana_Config::instance()->set("image.driver", "GraphicsMagick");
      Kohana_Config::instance()->set(
        "image.params.directory", module::get_var("gallery", "graphics_toolkit_path"));
      break;
    }

    self::$init = 1;
  }

  /**
   * Verify that a specific graphics function is available with the active toolkit.
   * @param  string  $func (eg rotate, sharpen)
   * @return boolean
   */
  static function can($func) {
    if (module::get_var("gallery", "graphics_toolkit") == "gd") {
      switch ($func) {
      case "rotate":
        return function_exists("imagerotate");

      case "sharpen":
        return function_exists("imageconvolution");
      }
    }

    return true;
  }

  /**
   * Return the max file size that this graphics toolkit can handle.
   */
  static function max_filesize() {
    if (module::get_var("gallery", "graphics_toolkit") == "gd") {
      $memory_limit = trim(ini_get("memory_limit"));
      $memory_limit_bytes = num::convert_to_bytes($memory_limit);

      // GD expands images in memory and uses 4 bytes of RAM for every byte
      // in the file.
      $max_filesize = $memory_limit_bytes / 4;
      $max_filesize_human_readable = num::convert_to_human_readable($max_filesize);
      return array($max_filesize, $max_filesize_human_readable);
    }

    // Some arbitrarily large size
    return array(1000000000, "1G");
  }
}
