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
class Admin_Local_Import_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("local_import_admin.html");
    $view->content->add_form = $this->_get_admin_form();
    $view->content->path_list = new View("local_import_dir_list.html");
    $paths = unserialize(module::get_var("local_import", "authorized_paths", "a:0:{}"));
    $view->content->path_list->paths = array_keys($paths);

    print $view;
  }

  public function add_path() {
    access::verify_csrf();

    $form = $this->_get_admin_form();
    $paths = unserialize(module::get_var("local_import", "authorized_paths", "a:0:{}"));
    if ($form->validate()) {
      if (is_readable($form->add_path->path->value)) {
        $paths[$form->add_path->path->value] = 1;
        module::set_var("local_import", "authorized_paths", serialize($paths));
        $view = new View("local_import_dir_list.html");
        $view->paths = array_keys($paths);
        $form->add_path->inputs["path"]->value("");
        print json_encode(
          array("result" => "success",
                "paths" => $view->__toString(),
                "form" => $form->__toString()));
      } else {
        $form->add_path->inputs["path"]->error("not_readable");
        print json_encode(array("result" => "error", "form" => $form->__toString()));
      }
    } else {
      print json_encode(array("result" => "error", "form" => $form->__toString()));
    }

  }

  public function remove_path() {
    access::verify_csrf();

    $path = $this->input->post("path");
    $paths = unserialize(module::get_var("local_import", "authorized_paths"));
    unset($paths[$path]);
    module::set_var("local_import", "authorized_paths", serialize($paths));

    $view = new View("local_import_dir_list.html");
    $view->paths = array_keys($paths);

    print $view;
  }

  public function autocomplete() {
    $directories = array();
    $path_prefix = $this->input->get("q");
    foreach (glob("{$path_prefix}*") as $file) {
      if (is_dir($file)) {
        $directories[] = $file;
      }
    }

    print implode("\n", $directories);
  }

  private function _get_admin_form() {
    $form  = new Forge("admin/local_import/add_path", "", "post", array("id" => "gLocalImportAdminForm"));
    $add_path = $form->group("add_path");
    $add_path->input("path")->label(t("Path"))->rules("required")
      ->error_messages("not_readable", t("The directory is not readable by the webserver"));
    $add_path->submit("add")->value(t("Add Path"));

    return $form;
  }
}