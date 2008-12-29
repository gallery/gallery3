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
class Admin_View_Core extends View {
  private $theme_name = null;

  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name) {
    parent::__construct($name);

    $this->theme_name = module::get_var("core", "active_admin_theme");
    $this->set_global('theme', $this);
    $this->set_global('user', user::active());
  }

  public function url($path) {
    return url::file("themes/{$this->theme_name}/$path");
  }

  public function display($page_name, $view_class="View") {
    return new $view_class($page_name);
  }

  public function admin_menu() {
    $menu = new Menu(true);
    core_menu::admin($menu, $this);

    foreach (module::installed() as $module) {
      if ($module->name == "core") {
        continue;
      }
      $class = "{$module->name}_menu";
      if (method_exists($class, "admin")) {
        call_user_func_array(array($class, "admin"), array(&$menu, $this));
      }
    }

    print $menu;
  }

  /**
   * Print out any site wide status information.  This is for admins only.
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
    case "admin_dashboard_blocks":
    case "admin_footer":
    case "admin_header_top":
    case "admin_header_bottom":
    case "admin_page_bottom":
    case "admin_page_top":
    case "admin_sidebar_blocks":
    case "admin_head":
      $blocks = array();
      foreach (module::installed() as $module) {
        $helper_class = "{$module->name}_block";
        if (method_exists($helper_class, $function)) {
          $blocks[] = call_user_func_array(
            array($helper_class, $function),
            array_merge(array($this), $args));
        }
      }
      return implode("\n", $blocks);

    default:
      throw new Exception("@todo UNKNOWN_THEME_FUNCTION: $function");
    }
  }
}