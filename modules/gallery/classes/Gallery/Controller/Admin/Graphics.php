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
class Gallery_Controller_Admin_Graphics extends Controller_Admin {
  public function index() {
    $view = new View_Admin("admin.html");
    $view->page_title = t("Graphics settings");
    $view->content = new View("admin/graphics.html");
    $view->content->tk = Graphics::detect_toolkits();
    $view->content->active = Module::get_var("gallery", "graphics_toolkit", "none");
    print $view;
  }

  public function choose($toolkit_id) {
    Access::verify_csrf();

    if ($toolkit_id != Module::get_var("gallery", "graphics_toolkit")) {
      $tk = Graphics::detect_toolkits();
      Module::set_var("gallery", "graphics_toolkit", $toolkit_id);
      Module::set_var("gallery", "graphics_toolkit_path", $tk->$toolkit_id->dir);

      SiteStatus::clear("missing_graphics_toolkit");

      $msg = t("Changed graphics toolkit to: %toolkit", array("toolkit" => $tk->$toolkit_id->name));
      Message::success($msg);
      Log::success("graphics", $msg);

      Module::event("graphics_toolkit_change", $toolkit_id);
    }

    URL::redirect("admin/graphics");
  }
}

