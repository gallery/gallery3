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
class Admin_Watermarks_Controller extends Admin_Controller {
  public function index() {
    $name = module::get_var("watermark", "name");

    $view = new Admin_View("admin.html");
    $view->page_title = t("Watermarks");
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
    access::verify_csrf();

    $form = watermark::get_edit_form();
    if ($form->validate()) {
      module::set_var("watermark", "position", $form->edit_watermark->position->value);
      module::set_var("watermark", "transparency", $form->edit_watermark->transparency->value);
      $this->_update_graphics_rules();

      log::success("watermark", t("Watermark changed"));
      message::success(t("Watermark changed"));
      json::reply(
        array("result" => "success",
              "location" => url::site("admin/watermarks")));
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function form_delete() {
    print watermark::get_delete_form();
  }

  public function delete() {
    access::verify_csrf();

    $form = watermark::get_delete_form();
    if ($form->validate()) {
      if ($name = basename(module::get_var("watermark", "name"))) {
        system::delete_later(VARPATH . "modules/watermark/$name");

        module::clear_var("watermark", "name");
        module::clear_var("watermark", "width");
        module::clear_var("watermark", "height");
        module::clear_var("watermark", "mime_type");
        module::clear_var("watermark", "position");
        $this->_update_graphics_rules();

        log::success("watermark", t("Watermark deleted"));
        message::success(t("Watermark deleted"));
      }
      json::reply(array("result" => "success", "location" => url::site("admin/watermarks")));
    } else {
      json::reply(array("result" => "error", "html" => (string)$form));
    }
  }

  public function form_add() {
    print watermark::get_add_form();
  }

  public function add() {
    access::verify_csrf();

    $form = watermark::get_add_form();
    // For TEST_MODE, we want to simulate a file upload.  Because this is not a true upload, Forge's
    // validation logic will correctly reject it.  So, we skip validation when we're running tests.
    if (TEST_MODE || $form->validate()) {
      $file = $_POST["file"];
      // Forge prefixes files with "uploadfile-xxxxxxx" for uniqueness
      $name = preg_replace("/uploadfile-[^-]+-(.*)/", '$1', basename($file));

      try {
        list ($width, $height, $mime_type, $extension) = photo::get_file_metadata($file);
        // Sanitize filename, which ensures a valid extension.  This renaming prevents the issues
        // addressed in ticket #1855, where an image that looked valid (header said jpg) with a
        // php extension was previously accepted without changing its extension.
        $name = legal_file::sanitize_filename($name, $extension, "photo");
      } catch (Exception $e) {
        message::error(t("Invalid or unidentifiable image file"));
        system::delete_later($file);
        return;
      }

      rename($file, VARPATH . "modules/watermark/$name");
      module::set_var("watermark", "name", $name);
      module::set_var("watermark", "width", $width);
      module::set_var("watermark", "height", $height);
      module::set_var("watermark", "mime_type", $mime_type);
      module::set_var("watermark", "position", $form->add_watermark->position->value);
      module::set_var("watermark", "transparency", $form->add_watermark->transparency->value);
      $this->_update_graphics_rules();
      system::delete_later($file);

      message::success(t("Watermark saved"));
      log::success("watermark", t("Watermark saved"));
      json::reply(array("result" => "success", "location" => url::site("admin/watermarks")));
    } else {
      // rawurlencode the results because the JS code that uploads the file buffers it in an
      // iframe which entitizes the HTML and makes it difficult for the JS to process.  If we url
      // encode it now, it passes through cleanly.  See ticket #797.
      json::reply(array("result" => "error", "html" => rawurlencode((string)$form)));
    }

    // Override the application/json mime type.  The dialog based HTML uploader uses an iframe to
    // buffer the reply, and on some browsers (Firefox 3.6) it does not know what to do with the
    // JSON that it gets back so it puts up a dialog asking the user what to do with it.  So force
    // the encoding type back to HTML for the iframe.
    // See: http://jquery.malsup.com/form/#file-upload
    header("Content-Type: text/html; charset=" . Kohana::CHARSET);
  }

  private function _update_graphics_rules() {
    graphics::remove_rules("watermark");
    if ($name = module::get_var("watermark", "name")) {
      foreach (array("thumb", "resize") as $target) {
        graphics::add_rule(
          "watermark", $target, "gallery_graphics::composite",
          array("file" => VARPATH . "modules/watermark/$name",
                "width" => module::get_var("watermark", "width"),
                "height" => module::get_var("watermark", "height"),
                "position" => module::get_var("watermark", "position"),
                "transparency" => 101 - module::get_var("watermark", "transparency")),
          1000);
      }
    }
  }
}