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
class Admin_Controller extends Controller {
  public function __construct() {
    if (!(user::active()->admin)) {
      throw new Exception("@todo UNAUTHORIZED", 401);
    }
    parent::__construct();
  }

  public function index() {
    $theme_name = module::get_var("core", "active_admin_theme", "admin_default");
    $template = new Admin_View("admin.html", $theme_name);
    $template->content = new View("dashboard.html");
    print $template;
  }

  public function __call($page_name, $args) {
    $theme_name = module::get_var("core", "active_admin_theme", "admin_default");
    // For now, we have only two legal pages.
    // @todo get these pages from the modules
    switch($page_name) {
    case "users":
      $view = new Admin_View("users.html", $theme_name);
      $view->users = ORM::factory("user")->find_all();
      break;

    case "dashboard":
      $view = new Admin_View("dashboard.html", $theme_name);
      break;

    default:
      Kohana::show_404();
    }

    print $view;
  }
}

