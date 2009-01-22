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
class Admin_Themedetails_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = theme::get_edit_form_admin();
    print $view;
  }

  public function save() {
    $form = theme::get_edit_form_admin();
    $init_array = $form->as_array();
    if ($form->validate()) {
      $form_array = $form->as_array();
      $intersect = array_diff_key($form_array, array('csrf' => 0));
      foreach ($intersect as $key => $value) {
        if ($init_array[$key] != $value) {
          module::set_var("core", $key, $value);
        }
      }
      message::success(t("Updated theme details"));
    } else {
      message::error(t("Error updating theme details"));      
    }
    url::redirect("admin/themedetails");
  }
}

