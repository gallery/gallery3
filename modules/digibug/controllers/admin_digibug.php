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
class Admin_Digibug_Controller extends Admin_Controller {
  public function index() {
    print $this->_get_view();
  }

  public function basic() {
    access::verify_csrf();

    module::set_var("digibug", "mode", "basic");
    message::success(t("Successfully set Digibug mode to basic"));

    url::redirect("admin/digibug");
  }

  public function advanced() {
    access::verify_csrf();

    $form = $this->_get_form();
    if ($form->validate()) {
      module::set_var("digibug", "company_id", $form->group->company_id->value);
      module::set_var("digibug", "event_id", $form->group->event_id->value);
      module::set_var("digibug", "mode", "advanced");
      message::success(t("Successfully set Digibug mode to advanced"));

      url::redirect("admin/digibug");
    }

    print $this->_get_view($form);
  }

  private function _get_view($form=null) {
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_digibug.html");
    $v->content->mode = module::get_var("digibug", "mode", "basic");
    $v->content->form = empty($form) ? $this->_get_form() : $form;
    return $v;
  }

  private function _get_form() {
    $form = new Forge("admin/digibug/advanced", "", "post",
                      array("id" => "gAdminForm"));
    $group = $form->group("group");
    $group->input("company_id")
      ->label(t("Company Id"))
      ->rules("required")
      ->value(module::get_var("digibug", "company_id", ""));
    $group->input("event_id")
      ->label(t("Event Id"))
      ->rules("required")
      ->value(module::get_var("digibug", "event_id", ""));
    $group->submit("submit")->value(t("Submit"));

    return $form;
  }
}