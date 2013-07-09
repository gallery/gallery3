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
class Gallery_Theme {
  public static $admin_theme_name;
  public static $site_theme_name;
  public static $is_admin;
  public static $kohana_themes = array();

  /**
   * Load the active theme.  This is called at bootstrap time.  We will only ever have one theme
   * active for any given request.
   */
  static function load_themes() {
    $override = Request::initial()->query("theme");
    Theme::$is_admin = in_array(substr(Request::initial()->uri(), 0, 6), array("admin", "admin/"));
    Theme::$site_theme_name = Module::get_var("gallery", "active_site_theme");


    // If the site theme doesn't exist, fall back to wind.
    if (!file_exists(THEMEPATH . Theme::$site_theme_name . "/theme.info")) {
      SiteStatus::error(t("Theme '%name' is missing.  Falling back to the Wind theme.",
                           array("name" => Theme::$site_theme_name)), "missing_site_theme");
      Module::set_var("gallery", "active_site_theme", Theme::$site_theme_name = "wind");
    }

    if (Theme::$is_admin) {
      // Load the admin theme
      Theme::$admin_theme_name = Module::get_var("gallery", "active_admin_theme");

      // If the admin theme doesn't exist, fall back to admin_wind.
      if (!file_exists(THEMEPATH . Theme::$admin_theme_name . "/theme.info")) {
        SiteStatus::error(t("Admin theme '%name' is missing!  Falling back to the Wind theme.",
                             array("name" => Theme::$admin_theme_name)), "missing_admin_theme");
        Module::set_var("gallery", "active_admin_theme", Theme::$admin_theme_name = "admin_wind");
      }

      Theme::$kohana_themes[Theme::$admin_theme_name] = THEMEPATH . Theme::$admin_theme_name;

      // If the site theme has an admin subdir, load that as a module so that
      // themes can provide their own code.
      if (file_exists(THEMEPATH . Theme::$site_theme_name . "/admin")) {
        Theme::$kohana_themes[Theme::$site_theme_name] = THEMEPATH . Theme::$site_theme_name . "/admin";
      }
      // Admins can override the site theme, temporarily.  This lets us preview themes.
      // @todo: verify that overriding the *admin* theme like this works.
      if (Identity::active_user()->admin && $override) {
        if (file_exists(THEMEPATH . $override)) {
          Theme::$admin_theme_name = $override;
          Theme::$kohana_themes[Theme::$admin_theme_name] = THEMEPATH . Theme::$admin_theme_name;
        } else {
          Log::instance()->add(Log::ERROR, "Missing override admin theme: '$override'");
        }
      }
    } else {
      // Admins can override the site theme, temporarily.  This lets us preview themes.
      if (Identity::active_user()->admin && $override) {
        if (file_exists(THEMEPATH . $override)) {
          Theme::$site_theme_name = $override;
        } else {
          Log::instance()->add(Log::ERROR, "Missing override site theme: '$override'");
        }
      }
      Theme::$kohana_themes[Theme::$site_theme_name] = THEMEPATH . Theme::$site_theme_name;
    }

    Theme::$kohana_themes = array_reverse(Theme::$kohana_themes, true);
    $kohana_modules = Kohana::modules();
    Kohana::modules(array_merge(Theme::$kohana_themes, $kohana_modules));
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

