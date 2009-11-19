<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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

/**
 * This is the API for handling themes.
 *
 * Note: by design, this class does not do any permission checking.
 */
class theme_Core {
  public static $site;
  public static $admin;

  /**
   * Load the active theme.  This is called at bootstrap time.  We will only ever have one theme
   * active for any given request.
   */
  static function load_themes() {
    $input = Input::instance();
    $path = $input->server("PATH_INFO");
    if (empty($path)) {
      $path = "/" . $input->get("kohana_uri");
    }

    self::$site = module::get_var("gallery", "active_site_theme");
    self::$admin = module::get_var("gallery", "active_admin_theme");
    if (!(identity::active_user()->admin && $theme_name = $input->get("theme"))) {
      $theme_name = $path == "/admin" || !strncmp($path, "/admin/", 7) ? self::$admin : self::$site;
    }
    $modules = Kohana::config("core.modules");
    array_unshift($modules, THEMEPATH . $theme_name);
    Kohana::config_set("core.modules", $modules);
  }

  static function get_info($theme_name) {
    $theme_name = preg_replace("/[^\w]/", "", $theme_name);
    $file = THEMEPATH . "$theme_name/theme.info";
    $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
    $theme_info->description = t($theme_info->description);
    $theme_info->name = t($theme_info->name);

    return $theme_info;
  }
}
