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
    $template = new View("local_import_admin.html");

    $paths = unserialize(module::get_var("local_import", "authorized_paths", "a:0:{}"));
    $path_list = new View("local_import_dir_list.html");
    $path_list->paths = array_keys($paths);
    $template->path_list = $path_list->render();
    $template->add_form = self::get_admin_form()->render();

    return $template;
  }

  public static function get_admin_form() {
    $form  = new Forge("admin/local_import/add_path", "", "post", array("id" => "gLocalImportAdminForm"));
    $add_path = $form->group("add_path");
    $add_path->input("path")->label(t("Path"))->rules("required")
      ->error_messages("not_readable", t("The directory is not readable by the webserver"));
    $add_path->submit("add")->value(t("Add Path"));

    return $form;
  }
}
