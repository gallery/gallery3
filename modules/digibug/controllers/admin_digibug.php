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

  public function update() {
    access::verify_csrf();

    $form = $this->_get_form();
    if ($form->validate()) {
      module::set_var("digibug", "company_id", $form->group->company_id->value);
      module::set_var("digibug", "event_id", $form->group->event_id->value);
      message::success(t("Successfully updated Digibug company and event id's"));

      url::redirect("admin/digibug");
    }

    print $this->_get_view($form);
  }

  public function default_settings() {
    access::verify_csrf();

    module::set_var("digibug", "company_id", null);
    module::set_var("digibug", "event_id", null);
    message::success(t("Successfully set Digibug company and event id's to default"));

    url::redirect("admin/digibug");
  }

  private function _get_view($form=null) {
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_digibug.html");
    $v->content->form = empty($form) ? $this->_get_form() : $form;
    return $v;
  }

  private function _get_form() {
    $form = new Forge("admin/digibug/update", "", "post",
                      array("id" => "gDigibugForm"));
    $group = $form->group("group")
      ->label(t("Enter your account information."));
    $group->input("company_id")
      ->label(t("Company Id"))
      ->rules("required")
      ->value(module::get_var("digibug", "company_id", ""));
    $group->input("event_id")
      ->label(t("Event Id"))
      ->rules("required")
      ->value(module::get_var("digibug", "event_id", ""));
    $group->submit("")->value(t("Submit"));

    return $form;
  }
}