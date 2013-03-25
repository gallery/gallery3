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
class Gallery_Controller_Admin_Themes extends Controller_Admin {
  public function index() {
    $view = new View_Admin("admin.html");
    $view->page_title = t("Theme choice");
    $view->content = new View("admin/themes.html");
    $view->content->admin = Module::get_var("gallery", "active_admin_theme");
    $view->content->site = Module::get_var("gallery", "active_site_theme");
    $view->content->themes = $this->_get_themes();

    SiteStatus::clear("missing_site_theme");
    SiteStatus::clear("missing_admin_theme");
    print $view;
  }

  private function _get_themes() {
    $themes = array();
    foreach (scandir(THEMEPATH) as $theme_name) {
      if ($theme_name[0] == ".") {
        continue;
      }
      $theme_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $theme_name);
      if (file_exists(THEMEPATH . "$theme_name/theme.info")) {

        $themes[$theme_name] = Theme::get_info($theme_name);
      }
    }
    return $themes;
  }

  public function preview($type, $theme_name) {
    $view = new View("admin/themes_preview.html");
    $view->info = Theme::get_info($theme_name);
    $view->theme_name = t($theme_name);
    $view->type = $type;
    if ($type == "admin") {
      $view->url = URL::site("admin?theme=$theme_name");
    } else {
      $view->url = Item::root()->url("theme=$theme_name");
    }
    print $view;
  }

  public function choose($type, $theme_name) {
    Access::verify_csrf();

    $info = Theme::get_info($theme_name);

    if ($type == "admin" && $info->admin) {
      Module::set_var("gallery", "active_admin_theme", $theme_name);
      Message::success(t("Successfully changed your admin theme to <b>%theme_name</b>",
                         array("theme_name" => $info->name)));
    } else if ($type == "site" && $info->site) {
      Module::set_var("gallery", "active_site_theme", $theme_name);
      Message::success(t("Successfully changed your Gallery theme to <b>%theme_name</b>",
                         array("theme_name" => $info->name)));
    }

    URL::redirect("admin/themes");
  }
}

