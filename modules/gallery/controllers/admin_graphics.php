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
class Admin_Graphics_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Graphics settings");
    $view->content = new View("admin_graphics.html");
    $view->content->tk = graphics::detect_toolkits();
    $view->content->active = module::get_var("gallery", "graphics_toolkit", "none");
    $view->content->form = $this->_get_admin_form();
    print $view;
  }

  public function choose($toolkit_id) {
    access::verify_csrf();

    if ($toolkit_id != module::get_var("gallery", "graphics_toolkit")) {
      $tk = graphics::detect_toolkits();
      module::set_var("gallery", "graphics_toolkit", $toolkit_id);
      module::set_var("gallery", "graphics_toolkit_path", $tk->$toolkit_id->dir);

      site_status::clear("missing_graphics_toolkit");

      $msg = t("Changed graphics toolkit to: %toolkit", array("toolkit" => $tk->$toolkit_id->name));
      message::success($msg);
      log::success("graphics", $msg);

      module::event("graphics_toolkit_change", $toolkit_id);
    }

    url::redirect("admin/graphics");
  }

  public function save() {
    access::verify_csrf();
    $form = $this->_get_admin_form();
    if ($form->validate()) {
      $enable_thumbs = $form->make_jpg->make_all_thumbs_jpg->value &&
                       !module::get_var("gallery", "make_all_thumbs_jpg", 0);
      $enable_resizes = $form->make_jpg->make_all_resizes_jpg->value &&
                        !module::get_var("gallery", "make_all_resizes_jpg", 0);
      $disable_thumbs = !$form->make_jpg->make_all_thumbs_jpg->value &&
                        module::get_var("gallery", "make_all_thumbs_jpg", 0);
      $disable_resizes = !$form->make_jpg->make_all_resizes_jpg->value &&
                         module::get_var("gallery", "make_all_resizes_jpg", 0);
      graphics::enable_make_all_jpg_mode($enable_thumbs, $enable_resizes);
      graphics::disable_make_all_jpg_mode($disable_thumbs, $disable_resizes);
      // All done - redirect with message.
      message::success(t("Graphics settings updated successfully"));
      url::redirect("admin/graphics");
    }
    // Something went wrong - print view from existing form.
    $this->_print_view($form);
  }

  private function _get_admin_form() {
    $form = new Forge("admin/graphics/save", "", "post", array("id" => "g-graphics-admin-form"));
    $group = $form->group("make_jpg")->label(t("Generate JPG images for thumbnails and resizes"));
    $group->checkbox("make_all_thumbs_jpg")
      ->label(t("Make all thumbnail images JPG"))
      ->checked(module::get_var("gallery", "make_all_thumbs_jpg", 0));
    $group->checkbox("make_all_resizes_jpg")
      ->label(t("Make all resize images JPG"))
      ->checked(module::get_var("gallery", "make_all_resizes_jpg", 0));
    $form->submit("save")->value(t("Save"));
    return $form;
  }
}
