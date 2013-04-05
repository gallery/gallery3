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
class ServerAdd_Controller_Admin_ServerAdd extends Controller_Admin {
  public function action_index() {
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Add from server");
    $view->content = new View("admin/server_add.html");
    $view->content->form = $this->_get_admin_form();
    $paths = unserialize(Module::get_var("server_add", "authorized_paths", "a:0:{}"));
    $view->content->paths = array_keys($paths);

    print $view;
  }

  public function action_add_path() {
    Access::verify_csrf();

    $form = $this->_get_admin_form();
    $paths = unserialize(Module::get_var("server_add", "authorized_paths", "a:0:{}"));
    if ($form->validate()) {
      $path = html_entity_decode($form->add_path->path->value);
      if (is_link($path)) {
        $form->add_path->path->add_error("is_symlink", 1);
      } else if (!is_readable($path)) {
        $form->add_path->path->add_error("not_readable", 1);
      } else {
        $paths[$path] = 1;
        Module::set_var("server_add", "authorized_paths", serialize($paths));
        Message::success(t("Added path %path", array("path" => $path)));
        ServerAdd::check_config($paths);
        HTTP::redirect("admin/server_add");
      }
    }

    $view = new View_Admin("required/admin.html");
    $view->content = new View("admin/server_add.html");
    $view->content->form = $form;
    $view->content->paths = array_keys($paths);
    print $view;
  }

  public function action_remove_path() {
    Access::verify_csrf();

    $path = Input::instance()->get("path");
    $paths = unserialize(Module::get_var("server_add", "authorized_paths"));
    if (isset($paths[$path])) {
      unset($paths[$path]);
      Message::success(t("Removed path %path", array("path" => $path)));
      Module::set_var("server_add", "authorized_paths", serialize($paths));
      ServerAdd::check_config($paths);
    }
    HTTP::redirect("admin/server_add");
  }

  public function action_autocomplete() {
    $directories = array();

    $path_prefix = Input::instance()->get("term");
    foreach (glob("{$path_prefix}*") as $file) {
      if (is_dir($file) && !is_link($file)) {
        $directories[] = (string)HTML::clean($file);
      }
    }

    Ajax::response(json_encode($directories));
  }

  private function _get_admin_form() {
    $form = new Forge("admin/server_add/add_path", "", "post",
                      array("id" => "g-server-add-admin-form", "class" => "g-short-form"));
    $add_path = $form->group("add_path");
    $add_path->input("path")->label(t("Path"))->rules("required")->id("g-path")
      ->error_messages("not_readable", t("This directory is not readable by the webserver"))
      ->error_messages("is_symlink", t("Symbolic links are not allowed"));
    $add_path->submit("add")->value(t("Add Path"));

    return $form;
  }
}
