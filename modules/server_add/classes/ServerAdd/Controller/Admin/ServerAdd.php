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
    $form = Formo::form()
      ->attr("id", "g-server-add-admin-form")
      ->add_class("g-short-form")
      ->add("add_path", "group");
    $form->add_path
      ->set("label", t("Add Path"))
      ->add("path", "input")
      ->add("add", "input|submit", t("Add Path"));
    $form->add_path->path
      ->attr("id", "g-path")
      ->set("label", t("Path"))
      ->add_rule("Controller_Admin_ServerAdd::validate_readable_path", array(":value"),
                 t("This directory is not readable by the webserver"))
      ->add_rule("Controller_Admin_ServerAdd::validate_non_symlink_path", array(":value"),
                 t("Symbolic links are not allowed"));

    $paths = unserialize(Module::get_var("server_add", "authorized_paths", "a:0:{}"));

    if ($form->load()->validate()) {
      $path = html_entity_decode($form->add_path->path->val());
      $paths[$path] = 1;
      Module::set_var("server_add", "authorized_paths", serialize($paths));

      Message::success(t("Added path %path", array("path" => $path)));
      ServerAdd::check_config($paths);
    }

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Add from server");
    $view->content = new View("admin/server_add.html");
    $view->content->form = $form;
    $view->content->paths = array_keys($paths);

    $this->response->body($view);
  }

  public function action_remove_path() {
    Access::verify_csrf();

    $path = $this->request->query("path");
    $paths = unserialize(Module::get_var("server_add", "authorized_paths"));
    if (isset($paths[$path])) {
      unset($paths[$path]);
      Message::success(t("Removed path %path", array("path" => $path)));
      Module::set_var("server_add", "authorized_paths", serialize($paths));
      ServerAdd::check_config($paths);
    }
    $this->redirect("admin/server_add");
  }

  public function action_autocomplete() {
    $directories = array();

    $path_prefix = $this->request->query("term");
    foreach (glob("{$path_prefix}*") as $file) {
      if (is_dir($file) && !is_link($file)) {
        $directories[] = (string)HTML::clean($file);
      }
    }

    $this->response->ajax(json_encode($directories));
  }

  public static function validate_readable_path($path) {
    return is_readable(html_entity_decode($path));
  }

  public static function validate_non_symlink_path($path) {
    return !is_link(html_entity_decode($path));
  }
}
