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
class Admin_Advanced_Settings_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Advanced settings");
    $view->content = new View("admin_advanced_settings.html");
    $view->content->vars = ORM::factory("var")
      ->order_by("module_name")
      ->order_by("name")
      ->find_all();
    print $view;
  }

  public function edit($module_name, $var_name) {
    if (module::is_installed($module_name)) {
      $value = module::get_var($module_name, $var_name);
      $form = new Forge("admin/advanced_settings/save/$module_name/$var_name", "", "post");
      $group = $form->group("edit_var")->label(t("Edit setting"));
      $group->input("module_name")->label(t("Module"))->value($module_name)->disabled(1);
      $group->input("var_name")->label(t("Setting"))->value($var_name)->disabled(1);
      $group->textarea("value")->label(t("Value"))->value($value);
      $group->submit("")->value(t("Save"));
      print $form;
    }
  }

  public function save($module_name, $var_name) {
    access::verify_csrf();

    if (module::is_installed($module_name)) {
      module::set_var($module_name, $var_name, Input::instance()->post("value"));
      message::success(
        t("Saved value for %var (%module_name)",
          array("var" => $var_name, "module_name" => $module_name)));

      json::reply(array("result" => "success"));
    }
  }
}
