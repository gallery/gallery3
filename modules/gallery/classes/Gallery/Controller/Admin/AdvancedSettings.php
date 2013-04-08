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
class Gallery_Controller_Admin_AdvancedSettings extends Controller_Admin {
  public function action_index() {
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Advanced settings");
    $view->content = new View("admin/advanced_settings.html");
    $view->content->vars = ORM::factory("Var")
      ->order_by("module_name")
      ->order_by("name")
      ->find_all();
    print $view;
  }

  public function action_edit($module_name, $var_name) {
    if (Module::is_installed($module_name)) {
      $value = Module::get_var($module_name, $var_name);
      $form = new Forge("admin/advanced_settings/save/$module_name/$var_name", "", "post");
      $group = $form->group("edit_var")->label(t("Edit setting"));
      $group->input("module_name")->label(t("Module"))->value($module_name)->disabled(1);
      $group->input("var_name")->label(t("Setting"))->value($var_name)->disabled(1);
      $group->textarea("value")->label(t("Value"))->value($value);
      $group->submit("")->value(t("Save"));
      print $form;
    }
  }

  public function action_save($module_name, $var_name) {
    Access::verify_csrf();

    if (Module::is_installed($module_name)) {
      Module::set_var($module_name, $var_name, Request::$current->post("value"));
      Message::success(
        t("Saved value for %var (%module_name)",
          array("var" => $var_name, "module_name" => $module_name)));

      JSON::reply(array("result" => "success"));
    }
  }
}
