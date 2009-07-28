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
class Admin_View_Core extends Gallery_View {
  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name) {
    $theme_name = module::get_var("gallery", "active_site_theme");
    if (!file_exists("themes/$theme_name")) {
      module::set_var("gallery", "active_site_theme", "admin_default");
      theme::load_themes();
      Kohana::log("error", "Unable to locate theme '$theme_name', switching to default theme.");
    }
    parent::__construct($name);

    $this->theme_name = module::get_var("gallery", "active_admin_theme");
    if (user::active()->admin) {
      $this->theme_name = Input::instance()->get("theme", $this->theme_name);
    }
    $this->sidebar = "";
    $this->set_global("theme", $this);
    $this->set_global("user", user::active());
  }

  public function admin_menu($menu=null) {
    if (!$menu) {
      $menu = Menu::factory("root");
    }

    $menu
      ->append(Menu::factory("link")
               ->id("dashboard")
               ->label(t("Dashboard"))
               ->url(url::site("admin")))
      ->append(Menu::factory("submenu")
               ->id("settings_menu")
               ->label(t("Settings"))
               ->append(Menu::factory("link")
                        ->id("graphics_toolkits")
                        ->label(t("Graphics"))
                        ->url(url::site("admin/graphics")))
               ->append(Menu::factory("link")
                        ->id("languages")
                        ->label(t("Languages"))
                        ->url(url::site("admin/languages")))
               ->append(Menu::factory("link")
                        ->id("l10n_mode")
                        ->label(Session::instance()->get("l10n_mode", false)
                                ? t("Stop translating") : t("Start translating"))
                        ->url(url::site("l10n_client/toggle_l10n_mode?csrf=" .
                                        access::csrf_token())))
               ->append(Menu::factory("link")
                        ->id("advanced")
                        ->label(t("Advanced"))
                        ->url(url::site("admin/advanced_settings"))))
      ->append(Menu::factory("link")
               ->id("modules")
               ->label(t("Modules"))
               ->url(url::site("admin/modules")))
      ->append(Menu::factory("submenu")
               ->id("content_menu")
               ->label(t("Content")))
      ->append(Menu::factory("submenu")
               ->id("appearance_menu")
               ->label(t("Appearance"))
               ->append(Menu::factory("link")
                        ->id("themes")
                        ->label(t("Theme Choice"))
                        ->url(url::site("admin/themes")))
               ->append(Menu::factory("link")
                        ->id("theme_options")
                        ->label(t("Theme Options"))
                        ->url(url::site("admin/theme_options"))))
      ->append(Menu::factory("submenu")
               ->id("statistics_menu")
               ->label(t("Statistics")))
      ->append(Menu::factory("link")
               ->id("maintenance")
               ->label(t("Maintenance"))
               ->url(url::site("admin/maintenance")));

    module::event("admin_menu", $menu, $this);
    $menu->compact();
    return $menu;
  }

  /**
   * Print out any site wide status information.
   */
  public function site_status() {
    return site_status::get();
  }

  /**
   * Print out any messages waiting for this user.
   */
  public function messages() {
    return message::get();
  }

 /**
   * Handle all theme functions that insert module content.
   */
  public function __call($function, $args) {
    switch ($function) {
    case "admin_credits";
    case "admin_footer":
    case "admin_header_top":
    case "admin_header_bottom":
    case "admin_page_bottom":
    case "admin_page_top":
    case "admin_head":
      $blocks = array();
      foreach (module::active() as $module) {
        $helper_class = "{$module->name}_theme";
        if (method_exists($helper_class, $function)) {
          $blocks[] = call_user_func_array(
            array($helper_class, $function),
            array_merge(array($this), $args));
        }
      }

      if ($function == "admin_head") {
        array_unshift($blocks, $this->combine_files($this->css, "css"));
        array_unshift($blocks, $this->combine_files($this->scripts, "javascript"));
      }

      if (Session::instance()->get("debug")) {
        if ($function != "admin_head") {
          array_unshift(
            $blocks, "<div class=\"gAnnotatedThemeBlock gAnnotatedThemeBlock_$function\">" .
            "<div class=\"title\">$function</div>");
          $blocks[] = "</div>";
        }
      }

      return implode("\n", $blocks);

    default:
      throw new Exception("@todo UNKNOWN_THEME_FUNCTION: $function");
    }
  }
}