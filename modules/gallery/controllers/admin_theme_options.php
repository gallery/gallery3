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
class Admin_Theme_Options_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_theme_options.html");

    $theme_name = theme::$site;
    $info = theme::get_info($theme_name);

    // Don't use the Kohana cascading file system because we don't want to mess up the admin theme
    $theme_helper = THEMEPATH . "$theme_name/helpers/{$theme_name}.php";
    @require_once($theme_helper);
    $view->content->form = call_user_func_array(array(theme::$site, "get_admin_form"),
                                                array("admin/theme_options/save/"));

    $view->content->title = t("%name options", array("name" => $info->name));

    print $view;
  }

  public function save() {
    access::verify_csrf();

    // Don't use the Kohana cascading file system because we don't want to mess up the admin theme
    $theme_name = theme::$site;
    $theme_helper = THEMEPATH . "$theme_name/helpers/{$theme_name}.php";
    @require_once($theme_helper);

    $info = theme::get_info($theme_name);

    $form = call_user_func_array(array(theme::$site, "get_admin_form"),
                                 array("admin/theme_options/save/"));
    if ($form->validate()) {

      $view->content->form = call_user_func_array(array(theme::$site, "update_options"),
                                                  array($form));

      message::success(t("Updated %name options", array("name" => $info->name)));
      url::redirect("admin/theme_options");
    } else {
      $view = new Admin_View("admin.html");
      $view->content = $form;
      $view->content->title = t("%name options", array("name" => $info->name));
      print $view;
    }
  }
}

