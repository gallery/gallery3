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
class View extends View_Core {
  private static $block_helpers = null;

  /**
   * Override View_Core::render so that we trap errors stemming from bad PHP includes and show a
   * visible stack trace to help developers.
   *
   * @see View_Core::render
   */
  public function render($print=false, $renderer=false) {
    try {
      return parent::render($print, $renderer);
    } catch (Exception $e) {
      if (!IN_PRODUCTION) {
        print $e->getTraceAsString();
        return $e->getMessage();
      }

      Kohana::Log('error', $e->getTraceAsString());
      Kohana::Log('debug', $e->getMessage());
      return "";
    }
  }

  public static function admin($theme) {
    return self::_get_block_helpers("admin", $theme);
  }

  public static function head($theme) {
    return self::_get_block_helpers("head", $theme);
  }

  public static function top($theme) {
    return self::_get_block_helpers("top", $theme);
  }

  public static function bottom($theme) {
    return self::_get_block_helpers("bottom", $theme);
  }

  public static function sidebar($theme) {
    return self::_get_block_helpers("sidebar", $theme);
  }

  public static function album($theme) {
    return self::_get_block_helpers("album", $theme);
  }

  public static function album_header($theme) {
    return self::_get_block_helpers("album_header", $theme);
  }

  public static function photo($theme) {
    return self::_get_block_helpers("photo", $theme);
  }

  private static function _get_block_helpers($method, $theme) {
    if (empty(self::$block_helpers[$method])) {
      foreach (module::get_list() as $module) {
        $helper_path = MODPATH . "$module->name/helpers/{$module->name}_block.php";
        $helper_class = "{$module->name}_block";
        if (file_exists($helper_path) && method_exists($helper_class, $method)) {
          self::$block_helpers[$method][] = "$helper_class";
        }
      }
    }

    $blocks = "";
    if (!empty(self::$block_helpers[$method])) {
      foreach (self::$block_helpers[$method] as $helper_class) {
        $blocks .= call_user_func_array(array($helper_class, $method), array($theme)) . "\n";
      }
    }
    return $blocks;
  }
}
