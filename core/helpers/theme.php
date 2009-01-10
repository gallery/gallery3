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

/**
 * This is the API for handling themes.
 *
 * Note: by design, this class does not do any permission checking.
 */
class theme_Core {
  /**
   * Load the active theme.  This is called at bootstrap time.  We will only ever have one theme
   * active for any given request.
   */
  public static function load_themes() {
    $modules = Kohana::config('core.modules');
    if (Router::$controller == "admin") {
      array_unshift($modules, THEMEPATH . 'admin_default');
    } else {
      array_unshift($modules, THEMEPATH . 'default');
    }
    Kohana::config_set('core.modules', $modules);
  }
  
  public static function get_edit_form_admin($theme) {
    $form = new Forge("admin/themes/edit/{$theme->id}",
                      '', null, array("id" =>"gThemeDetailsForm"));
    $group = $form->group("edit_theme")->label($theme->description);
    $group->input("page_size")->label(t("Items per page"))->id("gPageSize")->
      value(self::_get_var($theme->id, "page_size", 90));
    $group->input("thumb_size")->label(t("Thumbnail size (in pixels)"))->id("gThumbSize")->
      value(self::_get_var($theme->id, "thumb_size", 300));
    $group->input("resize_size")->label(t("Resized image size (in pixels)"))->id("gResizeSize")->
      value(self::_get_var($theme->id, "resize_size", 600));
    $group->submit(t("Modify Theme"));
    return $form;
  }
  
  public static function get_edit_form_content($theme_name) {
    $file = THEMEPATH . $theme_name . "/theme.info"; 
    $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);  
  }
  
  public static function _get_var($theme_id, $name, $default_value = null) {
    return module::get_var($theme_id, $name, module::get_var("core", $name, $default_value));
  }
}

