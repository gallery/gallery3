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
class Local_Import_Admin_Controller extends Controller {
  public function add_path() {
    $form = local_import::get_admin_form();
    $paths = unserialize(module::get_var("local_import", "authorized_paths"));
    if ($form->validate()) {
      $paths[$form->path->value] = 1;
      module::set_var("local_import", "authorized_paths", serialize($paths));
    }
    $view = new View("local_import_dir_list.html");
    $view->paths = array_keys($paths);

    print $view;
  }

  public function remove() {
    $path = $this->input->post("path");
    $paths = unserialize(module::get_var("local_import", "authorized_paths"));
    unset($paths[$path]);
    module::set_var("local_import", "authorized_paths", serialize($paths));

    $view = new View("local_import_dir_list.html");
    $view->paths = array_keys($paths);

    print $view;
  }

  public function autocomplete() {
    $files = array();

    $path_prefix = $this->input->get("q");
    foreach (glob("{$path_prefix}*") as $file) {
      $files[] = $file;
    }

    print implode("\n", $files);
  }
}