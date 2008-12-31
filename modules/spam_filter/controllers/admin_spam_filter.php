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
class Admin_Spam_Filter_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = $this->get_edit_form();

    print $view;
  }

  public function get_edit_form() {
    $form = new Forge("admin/spam_filter/edit", "", "post");
    $group = $form->group("edit_spam_filter")->label(_("Configure Spam Filter"));
    $drivers = spam_filter::get_driver_names();
    $current_driver = module::get_var("spam_filter", "driver");

    $selected = -1;
    foreach ($drivers as $idx => $driver) {
      if ($driver == $current_driver) {
        $selected = $idx;
      }
    }
    $group->dropdown("drivers")->label(_("Available Drivers"))
      ->options(spam_filter::get_driver_names())
      ->rules("required")
      ->selected($selected);
    $group->input("api_key")->label(_("Api Key"))
      ->rules("required")
      ->value(module::get_var("spam_filter", "api_key"));
    $group->submit(_("Configure"));

    return $form;
  }

  public function edit() {
    $form = $this->get_edit_form();
    if ($form->validate()) {
      $driver_index = $form->edit_spam_filter->drivers->value;
      $drivers = spam_filter::get_driver_names();
      module::set_var("spam_filter", "driver", $drivers[$driver_index]);
      // @todo do verify key
      module::set_var("spam_filter", "api_key", $form->edit_spam_filter->api_key->value);
      log::success("spam_filter", _("Spam Filter configured"));
      message::success(_("Spam Filter configured"));
      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/spam_filter")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }
}