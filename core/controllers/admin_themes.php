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
    $view->content->admin = module::get_var("core", "active_admin_theme");
    $view->content->site = module::get_var("core", "active_site_theme");
    $view->content->themes = $this->_get_themes();
    print $view;
  }

  private function _get_themes() {
    $themes = array();
    foreach (scandir(THEMEPATH) as $theme_name) {
      if ($theme_name[0] == ".") {
        continue;
      }

      $file = THEMEPATH . "$theme_name/theme.info";
      $theme_info = new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
      $themes[$theme_name] = $theme_info;
    }
    return $themes;
  }

  public function preview($type, $theme_name) {
    $view = new View("admin_themes_preview.html");
    $theme_name = preg_replace("/[^\w]/", "", $theme_name);
    $view->info = new ArrayObject(
      parse_ini_file(THEMEPATH . "$theme_name/theme.info"), ArrayObject::ARRAY_AS_PROPS);
    $view->theme_name = $theme_name;
    $view->type = $type;
    if ($type == "admin") {
      $view->url = url::site("admin?theme=$theme_name");
    } else {
      $view->url = url::site("albums/1?theme=$theme_name");
    }
    $view->csrf = access::csrf_token();
    print $view;
  }

  public function choose($type, $theme_name) {
    access::verify_csrf();

    $theme_name = preg_replace("/[^\w]/", "", $theme_name);
    $info = new ArrayObject(
      parse_ini_file(THEMEPATH . "$theme_name/theme.info"), ArrayObject::ARRAY_AS_PROPS);

    if ($type == "admin" && $info->admin) {
      module::set_var("core", "active_admin_theme", $theme_name);
      message::success(t("Successfully changed your admin theme to <b>%theme_name</b>",
                         array("theme_name" => $info->name)));
    } else if ($type == "site" && $info->site) {
      module::set_var("core", "active_site_theme", $theme_name);
      message::success(t("Successfully changed your Gallery theme to <b>%theme_name</b>",
                         array("theme_name" => $info->name)));
    }

    url::redirect("admin/themes");
  }
}

