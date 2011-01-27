<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Admin_Graphics_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Graphics settings");
    $view->content = new View("admin_graphics.html");
    $view->content->tk = graphics::detect_toolkits();
    $view->content->active = module::get_var("gallery", "graphics_toolkit", "none");
    print $view;
  }

  public function choose($toolkit_id) {
    access::verify_csrf();

    if ($toolkit_id != module::get_var("gallery", "graphics_toolkit")) {
      $tk = graphics::detect_toolkits();
      module::set_var("gallery", "graphics_toolkit", $toolkit_id);
      module::set_var("gallery", "graphics_toolkit_path", $tk->$toolkit_id->dir);

      site_status::clear("missing_graphics_toolkit");

      $msg = t("Changed graphics toolkit to: %toolkit", array("toolkit" => $tk->$toolkit_id->name));
      message::success($msg);
      log::success("graphics", $msg);
    }

    url::redirect("admin/graphics");
  }
}

