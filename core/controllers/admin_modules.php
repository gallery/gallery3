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
class Admin_Modules_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_modules.html");
    $view->content->available = module::available();
    print $view;
  }

  public function save() {
    $changes->install = array();
    $changes->uninstall = array();
    foreach (module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      $desired = $this->input->post($module_name) == 1;
      if ($info->installed && !$desired) {
        $changes->uninstall[] = $module_name;
        module::uninstall($module_name);
        message::success(t("Uninstalled %module_name module", array("module_name" => $info->name)));
      } else if (!$info->installed && $desired) {
        $changes->install[] = $module_name;
        module::install($module_name);
        message::success(t("Installed %module_name module", array("module_name" => $info->name)));
      }
    }

    module::event("module_change", $changes);
    url::redirect("admin/modules");
  }
}

