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
class Admin_Graphics_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_graphics.html");
    $view->content->available = "";

    $tk = new ArrayObject(graphics::detect_toolkits(), ArrayObject::ARRAY_AS_PROPS);
    $active = module::get_var("core", "graphics_toolkit", "none");
    foreach (array("gd", "imagemagick", "graphicsmagick", "none") as $id) {
      if ($id == $active) {
        $view->content->active = new View("admin_graphics_$id.html");
        $view->content->active->tk = $tk;
        $view->content->active->is_active = true;
      } else if ($id != "none") {
        $v = new View("admin_graphics_$id.html");
        $v->tk = $tk;
        $v->is_active = false;
        $view->content->available .= $v;
      }
    }

    print $view;
  }

  public function choose($toolkit) {
    access::verify_csrf();
    if ($toolkit != module::get_var("core", "graphics_toolkit")) {
      module::set_var("core", "graphics_toolkit", $toolkit);

      $toolkit_info = graphics::detect_toolkits();
      if ($toolkit == "graphicsmagick" || $toolkit == "imagemagick") {
        module::set_var("core", "graphics_toolkit_path", $toolkit_info[$toolkit]);
      }

      site_status::clear("missing_graphics_toolkit");
      message::success(t("Updated Graphics Toolkit"));
      log::success("graphics", t("Changed graphics toolkit to: %toolkit",
                                 array("toolkit" => $toolkit)));
    }

    url::redirect("admin/graphics");
  }
}

