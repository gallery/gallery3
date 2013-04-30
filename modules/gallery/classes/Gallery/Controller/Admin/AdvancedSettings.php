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
    $this->response->body($view);
  }

  public function action_edit() {
    $module_name = $this->request->arg(0, "alpha_dash");
    $var_name = $this->request->arg(1);
    $value = Module::get_var($module_name, $var_name);

    if (!Module::is_installed($module_name)) {
      throw HTTP_Exception::factory(400);
    }

    $form = Formo::form()
      ->add("edit_var", "group");
    $form->edit_var
      ->add("module_name", "input", $module_name)
      ->add("var_name", "input", $var_name)
      ->add("value", "textarea", $value)
      ->add("submit", "input|submit", t("Save"));

    $form->edit_var
      ->set("label", t("Edit setting"));
    $form->edit_var->module_name
      ->attr("disabled", "disabled")
      ->set("label", t("Module"));
    $form->edit_var->var_name
      ->attr("disabled", "disabled")
      ->set("label", t("Setting"));
    $form->edit_var->value
      ->set("label", t("Value"));

    if ($form->load()->validate()) {
      Module::set_var($module_name, $var_name, $form->edit_var->value->val());
      Message::success(
        t("Saved value for %var (%module_name)",
          array("var" => $var_name, "module_name" => $module_name)));

      $this->response->json(array("result" => "success"));
      return;
    }

    $this->response->body($form);
  }
}
