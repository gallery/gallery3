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
  /**
   * Load the active theme.  This is called at bootstrap time.  We will only ever have one theme
   * active for any given request.
   */
  static function load_themes() {
    $path = Input::instance()->server("PATH_INFO");
    $input = Input::instance();
    if (empty($path)) {
      $path = "/" . $input->get("kohana_uri");
    }

    if (!(identity::active_user()->admin && $theme_name = $input->get("theme"))) {
      $theme_name = module::get_var(
        "gallery",
        !strncmp($path, "/admin", 6) ? "active_admin_theme" : "active_site_theme");
    }
    $modules = Kohana::config("core.modules");
    array_unshift($modules, THEMEPATH . $theme_name);
    Kohana::config_set("core.modules", $modules);
  }

  static function get_edit_form_admin() {
    $form = new Forge("admin/theme_options/save/", "", null, array("id" =>"g-theme-options-form"));
    $group = $form->group("edit_theme");
    $group->input("page_size")->label(t("Items per page"))->id("g-page-size")
      ->rules("required|valid_digit")
      ->value(module::get_var("gallery", "page_size"));
    $group->input("thumb_size")->label(t("Thumbnail size (in pixels)"))->id("g-thumb-size")
      ->rules("required|valid_digit")
      ->value(module::get_var("gallery", "thumb_size"));
    $group->input("resize_size")->label(t("Resized image size (in pixels)"))->id("g-resize-size")
      ->rules("required|valid_digit")
      ->value(module::get_var("gallery", "resize_size"));
    $group->textarea("header_text")->label(t("Header text"))->id("g-header-text")
      ->value(module::get_var("gallery", "header_text"));
    $group->textarea("footer_text")->label(t("Footer text"))->id("g-footer-text")
      ->value(module::get_var("gallery", "footer_text"));
    $group->checkbox("show_credits")->label(t("Show site credits"))->id("g-footer-text")
      ->checked(module::get_var("gallery", "show_credits"));
    $group->submit("")->value(t("Save"));
    return $form;
  }
}

