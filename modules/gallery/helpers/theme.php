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

/**
 * This is the API for handling themes.
 *
 * Note: by design, this class does not do any permission checking.
 */
class theme_Core {
  public static $admin_theme_name;
  public static $site_theme_name;
  public static $is_admin;

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

    $config = Kohana_Config::instance();
    $modules = $config->get("core.modules");

    // Normally Router::find_uri() strips off the url suffix for us, but we're working off of the
    // PATH_INFO here so we need to strip it off manually
    if ($suffix = Kohana::config("core.url_suffix")) {
      $path = preg_replace("#" . preg_quote($suffix) . "$#u", "", $path);
    }

    self::$is_admin = $path == "/admin" || !strncmp($path, "/admin/", 7);
    self::$site_theme_name = module::get_var("gallery", "active_site_theme");

    // If the site theme doesn't exist, fall back to wind.
    if (!file_exists(THEMEPATH . self::$site_theme_name . "/theme.info")) {
      site_status::error(t("Theme '%name' is missing.  Falling back to the Wind theme.",
                           array("name" => self::$site_theme_name)), "missing_site_theme");
      module::set_var("gallery", "active_site_theme", self::$site_theme_name = "wind");
    }

    if (self::$is_admin) {
      // Load the admin theme
      self::$admin_theme_name = module::get_var("gallery", "active_admin_theme");

      // If the admin theme doesn't exist, fall back to admin_wind.
      if (!file_exists(THEMEPATH . self::$admin_theme_name . "/theme.info")) {
        site_status::error(t("Admin theme '%name' is missing!  Falling back to the Wind theme.",
                             array("name" => self::$admin_theme_name)), "missing_admin_theme");
        module::set_var("gallery", "active_admin_theme", self::$admin_theme_name = "admin_wind");
      }

      array_unshift($modules, THEMEPATH . self::$admin_theme_name);

      // If the site theme has an admin subdir, load that as a module so that
      // themes can provide their own code.
      if (file_exists(THEMEPATH . self::$site_theme_name . "/admin")) {
        array_unshift($modules, THEMEPATH . self::$site_theme_name . "/admin");
      }
      // Admins can override the site theme, temporarily.  This lets us preview themes.
      if (identity::active_user()->admin && $override = $input->get("theme")) {
        if (file_exists(THEMEPATH . $override)) {
          self::$admin_theme_name = $override;
          array_unshift($modules, THEMEPATH . self::$admin_theme_name);
        } else {
          Kohana_Log::add("error", "Missing override admin theme: '$override'");
        }
      }
    } else {
      // Admins can override the site theme, temporarily.  This lets us preview themes.
      if (identity::active_user()->admin && $override = $input->get("theme")) {
        if (file_exists(THEMEPATH . $override)) {
          self::$site_theme_name = $override;
        } else {
          Kohana_Log::add("error", "Missing override site theme: '$override'");
        }
      }
      array_unshift($modules, THEMEPATH . self::$site_theme_name);
    }

    $config->set("core.modules", $modules);
  }

  static function get_info($theme_name) {
    $theme_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $theme_name);
    $file = THEMEPATH . "$theme_name/theme.info";
    $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
    $theme_info->description = t($theme_info->description);
    $theme_info->name = t($theme_info->name);

    return $theme_info;
  }
}

