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
  public $theme_name = null;

  public function __construct() {
    if (!(user::active()->admin)) {
      throw new Exception("@todo UNAUTHORIZED", 401);
    }
    // giving default is probably overkill
    $this->theme_name = module::get_var("core", "active_admin_theme", "default_admin");
    parent::__construct();
  }

  public function index() {
    // For now, in order not to duplicate js and css, keep the regular ("item")
    // theme in addition to admin theme.
    $item_theme_name = module::get_var("core", "active_theme", "default");
    $item_theme = new Theme_View("album.html", "album", $item_theme_name);

    $template = new Theme_View("admin.html", "admin", $this->theme_name);
    $template->item_theme = $item_theme;
    $template->subpage = "dashboard.html";
    print $template;
  }

  public function subpage() {
    $template = new Theme_View($_REQUEST["name"] . ".html", "admin", $this->theme_name);
    switch ($_REQUEST["name"]) {
      case "list_users":
      $template->set_global("users", ORM::factory("user")->find_all());
    }
    print $template;
  }
}

