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
class Admin_Theme_Details_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = theme::get_edit_form_admin();
    print $view;
  }

  public function save() {
    $form = theme::get_edit_form_admin();
    if ($form->validate()) {
      module::set_var("core", "page_size", $form->edit_theme->page_size->value);
      module::set_var("core", "thumb_size", $form->edit_theme->thumb_size->value);
      module::set_var("core", "resize_size", $form->edit_theme->resize_size->value);
      message::success(t("Updated theme details"));
      url::redirect("admin/theme_details");
    } else {
      $view = new Admin_View("admin.html");
      $view->content = $form;
      print $view;
    }
  }
}

