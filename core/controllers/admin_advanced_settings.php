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
class Admin_Advanced_Settings_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_advanced_settings.html");
    $view->content->vars = ORM::factory("var")
      ->orderby("module_name", "name")
      ->find_all();
    print $view;
  }

  public function edit($module_name, $var_name) {
    $value = module::get_var($module_name, $var_name);
    $form = new Forge("admin/advanced_settings/save/$module_name/$var_name", "", "post");
    $group = $form->group("edit_var")->label(
      t("Edit %var (%module_name)",
        array("module_name" => $module_name, "var" => $var_name)));
    $group->input("module_name")->label(t("Module"))->value($module_name)->disabled(1);
    $group->input("var_name")->label(t("Setting"))->value($var_name)->disabled(1);
    $group->textarea("value")->label(t("Value"))->value($value);
    $group->submit("")->value(t("Save"));
    print $form;
  }

  public function save($module_name, $var_name) {
    access::verify_csrf();

    module::set_var($module_name, $var_name, Input::instance()->post("value"));
    message::success(
      t("Saved value for %var (%module_name)",
        array("var" => $var->name, "module_name" => $var->module_name)));

    print json_encode(array("result" => "success"));
  }
}
