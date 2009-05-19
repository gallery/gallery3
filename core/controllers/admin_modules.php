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
class Admin_Modules_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_modules.html");
    $view->content->available = module::available();
    print $view;
  }

  public function save() {
    access::verify_csrf();

    $changes->install = array();
    $changes->uninstall = array();
    foreach (module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      $desired = $this->input->post($module_name) == 1;
      if ($info->installed && !$desired && module::is_installed($module_name)) {
        $changes->uninstall[] = $module_name;
        $uninstalled_names[] = $info->name;
        module::uninstall($module_name);
      } else if (!$info->installed && $desired && !module::is_installed($module_name)) {
        $changes->install[] = $module_name;
        $installed_names[] = $info->name;
        module::install($module_name);
      }
    }

    module::event("module_change", $changes);

    // @todo this type of collation is questionable from a i18n perspective
    if (isset($installed_names)) {
      message::success(t("Installed: %names", array("names" => join(", ", $uninstalled_names))));
    }
    if (isset($uninstalled_names)) {
      message::success(t("Uninstalled: %names", array("names" => join(", ", $installed_names))));
    }
    url::redirect("admin/modules");
  }
}

