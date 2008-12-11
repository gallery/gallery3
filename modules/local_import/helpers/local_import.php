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
class local_import {
  public static function get_admin_page() {
//    @todo check for admin permission
//    if (!$user->admin) {
//      throw new Exception("@todo ACCESS DENIED");
//    }

    $template = new View("local_import_admin.html");

    $paths = unserialize(module::get_var("local_import", "authorized_paths"));
    if (!empty($paths)) {
      $template->dir_list = new View("local_import_dir_list.html");
      $template->dir_list->paths = array_keys($paths);
    } else {
      $template->dir_list = "";
    }

    $template->add_form = self::get_admin_form()->render();

    return $template;
  }

  public static function get_admin_form() {
    $form  = new Forge("admin/local_import/add_path", "", "post", array("id" => "gLocalImportAdminForm"));
    $form->input("path")->label(true);
    $form->submit(_("Add"));

    return $form;
  }
}
