<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Admin_g2_import_Controller extends Admin_Controller {
  public function index() {
    g2_import::lower_error_reporting();
    if (g2_import::is_configured()) {
      g2_import::init();
    }

    if (class_exists("GalleryCoreApi")) {
      $g2_stats = g2_import::stats();
      $g2_sizes = g2_import::common_sizes();
    }

    $view = new Admin_View("admin.html");
    $view->page_title = t("Gallery 2 import");
    $view->content = new View("admin_g2_import.html");
    $view->content->form = $this->_get_import_form();
    $view->content->version = "";

    if (g2_import::is_initialized()) {
      $view->content->g2_stats = $g2_stats;
      $view->content->g2_sizes = $g2_sizes;
      $view->content->thumb_size = module::get_var("gallery", "thumb_size");
      $view->content->resize_size = module::get_var("gallery", "resize_size");
      $view->content->version = g2_import::version();
    } else if (g2_import::is_configured()) {
      $view->content->form->configure_g2_import->embed_path->add_error("invalid", 1);
    }
    g2_import::restore_error_reporting();
    print $view;
  }

  public function save() {
    access::verify_csrf();
    g2_import::lower_error_reporting();

    $form = $this->_get_import_form();
    if ($form->validate()) {
      $embed_path = $form->configure_g2_import->embed_path->value;
      if (!is_file($embed_path) && file_exists("$embed_path/embed.php")) {
        $embed_path = "$embed_path/embed.php";
      }

      if (g2_import::is_valid_embed_path($embed_path)) {
        message::success(t("Gallery 2 path saved"));
        module::set_var("g2_import", "embed_path", $embed_path);
        url::redirect("admin/g2_import");
      } else {
        $form->configure_g2_import->embed_path->add_error("invalid", 1);
      }
    }

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_g2_import.html");
    $view->content->form = $form;
    g2_import::restore_error_reporting();
    print $view;
  }

  private function _get_import_form() {
    $form = new Forge(
      "admin/g2_import/save", "", "post", array("id" => "g-admin-configure-g2-import-form"));
    $group = $form->group("configure_g2_import")->label(t("Configure Gallery 2 Import"));
    $group->input("embed_path")->label(t("Filesystem path to your Gallery 2 embed.php file"))
      ->value(module::get_var("g2_import", "embed_path", ""));
    $group->embed_path->error_messages(
      "invalid", t("The path you entered is not a Gallery 2 installation."));
    $group->submit("")->value(t("Save"));
    return $form;
  }
}