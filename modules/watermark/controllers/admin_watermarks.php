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
class Admin_Watermarks_Controller extends Admin_Controller {
  public function index() {
    $name = module::get_var("watermark", "name");

    $view = new Admin_View("admin.html");
    $view->content = new View("admin_watermarks.html");
    if ($name) {
      $view->content->name = module::get_var("watermark", "name");
      $view->content->url = url::file("var/modules/watermark/$name");
      $view->content->width = module::get_var("watermark", "width");
      $view->content->height = module::get_var("watermark", "height");
      $view->content->position = module::get_var("watermark", "position");
    }
    print $view;
  }

  public function form_edit() {
    print watermark::get_edit_form();
  }

  public function edit() {
    rest::http_content_type(rest::JSON);
    $form = watermark::get_edit_form();
    if ($form->validate()) {
      $position = $form->edit_watermark->position->value;
      module::set_var("watermark", "position", $position);
      $this->_update_graphics_rules();

      log::success("watermark", _("Watermark changed"));
      message::success(_("Watermark changed"));
      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/watermarks")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function form_delete() {
    print watermark::get_delete_form();
  }

  public function delete() {
    rest::http_content_type(rest::JSON);
    $form = watermark::get_delete_form();
    if ($form->validate()) {
      if ($name = module::get_var("watermark", "name")) {
        @unlink(VARPATH . "modules/watermark/$name");

        module::clear_var("watermark", "name");
        module::clear_var("watermark", "width");
        module::clear_var("watermark", "height");
        module::clear_var("watermark", "mime_type");
        module::clear_var("watermark", "position");
        $this->_update_graphics_rules();

        log::success("watermark", _("Watermark deleted"));
        message::success(_("Watermark deleted"));
      }
      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/watermarks")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  public function form_add() {
    print watermark::get_add_form();
  }

  public function add() {
    rest::http_content_type(rest::JSON);
    $form = watermark::get_add_form();
    if ($form->validate()) {
      $file = $_POST["file"];
      $pathinfo = pathinfo($file);
      // Forge prefixes files with "uploadfile-xxxxxxx" for uniqueness
      $name = preg_replace("/uploadfile-[^-]+-(.*)/", '$1', $pathinfo["basename"]);

      if (!($image_info = getimagesize($file)) ||
          !in_array($image_info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
        message::error(_("Unable to identify this image file"));
        @unlink($file);
        return;
      }

      rename($file, VARPATH . "modules/watermark/$name");
      $position = $form->add_watermark->position->value;
      module::set_var("watermark", "name", $name);
      module::set_var("watermark", "width", $image_info[0]);
      module::set_var("watermark", "height", $image_info[1]);
      module::set_var("watermark", "mime_type", $image_info["mime"]);
      module::set_var("watermark", "position", $position);
      $this->_update_graphics_rules();
      @unlink($file);

      message::success(_("Watermark saved"));
      log::success("watermark", _("Watermark saved"));
      print json_encode(
        array("result" => "success",
              "location" => url::site("admin/watermarks")));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  private function _update_graphics_rules() {
    graphics::remove_rules("watermark");
    if ($name = module::get_var("watermark", "name")) {
      foreach (array("thumb", "resize") as $target) {
        graphics::add_rule(
          "watermark", $target, "composite",
          array("file" => VARPATH . "modules/watermark/$name",
                "width" => module::get_var("watermark", "width"),
                "height" => module::get_var("watermark", "height"),
                "mime_type" => module::get_var("watermark", "mime_type"),
                "position" => module::get_var("watermark", "position")),
          1000);
      }
    }
  }
}