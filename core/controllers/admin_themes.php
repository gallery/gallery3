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
class Admin_Themes_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_themes.html");
    $themeDir = scandir(THEMEPATH);
    $themes = array();
    foreach ($themeDir as $theme_name) {
      if (substr($theme_name, 0, 1) == ".") continue;
      $file = THEMEPATH . $theme_name . "/theme.info"; 
      $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
      $details = theme::get_edit_form_admin($theme_info);
      $theme_info['details'] = $details;
      $themes[$theme_name] = $theme_info;
    }
    $view->content->themes = $themes;
    $view->content->active = module::get_var("core", "active_theme");
    print $view;
  }

  public function edit($theme_name) {
    $file = THEMEPATH . $theme_name . "/theme.info"; 
    $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
    print theme::get_edit_form_admin($theme_info);
  }
  
  public function save() {
    access::verify_csrf();
    $theme = $this->input->post("themes");
    if ($theme != module::get_var("core", "active_theme")) {
      module::set_var("core", "active_theme", $theme);
      message::success(t("Updated Theme"));
      log::success("graphics", t("Changed theme to {{theme_name}}", array("theme_name" => $theme)));
    }
    url::redirect("admin/themes");
  }
}

