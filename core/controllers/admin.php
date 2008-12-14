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
  public $template = null;
  
  public function __construct() {
    if (!(user::active()->admin)) {
      throw new Exception("Unauthorized", 401);      
    }
    // For now, in order not to duplicate js and css, keep the regular ("item")
    // theme in addition to admin theme.
    $item_theme_name = module::get_var("core", "active_theme", "default");
    $item_theme = new Theme_View("album.html", "album", $item_theme_name);

    // giving default is probably overkill
    $theme_name = module::get_var("core", "active_admin_theme", "default_admin");
    $this->template = new Theme_View("admin.html", "admin", $theme_name);
    $this->template->item_theme = $item_theme;
    parent::__construct();
  }
  
  public function dashboard() {
    $this->template->subpage = "dashboard.html";
    print $this->template;
  }
  
  public function list_users() {
    $this->template->set_global('users', ORM::factory("user")->find_all());
    
    $this->template->subpage = "list_users.html";
    print $this->template;    
  }
}

