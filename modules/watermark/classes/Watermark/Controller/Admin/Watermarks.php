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
class Watermark_Controller_Admin_Watermarks extends Controller_Admin {
  public function index() {
    $name = Module::get_var("watermark", "name");

    $view = new View_Admin("admin.html");
    $view->page_title = t("Watermarks");
    $view->content = new View("admin/watermarks.html");
    if ($name) {
      $view->content->name = Module::get_var("watermark", "name");
      $view->content->url = URL::file("var/modules/watermark/$name");
      $view->content->width = Module::get_var("watermark", "width");
      $view->content->height = Module::get_var("watermark", "height");
      $view->content->position = Module::get_var("watermark", "position");
    }
    print $view;
  }

  public function form_edit() {
    print Watermark::get_edit_form();
  }

  public function edit() {
    Access::verify_csrf();

    $form = Watermark::get_edit_form();
    if ($form->validate()) {
      Module::set_var("watermark", "position", $form->edit_watermark->position->value);
      Module::set_var("watermark", "transparency", $form->edit_watermark->transparency->value);
      $this->_update_graphics_rules();

      Log::success("watermark", t("Watermark changed"));
      Message::success(t("Watermark changed"));
      JSON::reply(
        array("result" => "success",
              "location" => URL::site("admin/watermarks")));
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
    // Override the application/json mime type for iframe compatibility.  See ticket #2022.
    header("Content-Type: text/plain; charset=" . Kohana::$charset);
  }

  public function form_delete() {
    print Watermark::get_delete_form();
  }

  public function delete() {
    Access::verify_csrf();

    $form = Watermark::get_delete_form();
    if ($form->validate()) {
      if ($name = basename(Module::get_var("watermark", "name"))) {
        System::delete_later(VARPATH . "modules/watermark/$name");

        Module::clear_var("watermark", "name");
        Module::clear_var("watermark", "width");
        Module::clear_var("watermark", "height");
        Module::clear_var("watermark", "mime_type");
        Module::clear_var("watermark", "position");
        $this->_update_graphics_rules();

        Log::success("watermark", t("Watermark deleted"));
        Message::success(t("Watermark deleted"));
      }
      JSON::reply(array("result" => "success", "location" => URL::site("admin/watermarks")));
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
    // Override the application/json mime type for iframe compatibility.  See ticket #2022.
    header("Content-Type: text/plain; charset=" . Kohana::$charset);
  }

  public function form_add() {
    print Watermark::get_add_form();
  }

  public function add() {
    Access::verify_csrf();

    $form = Watermark::get_add_form();
    // For TEST_MODE, we want to simulate a file upload.  Because this is not a true upload, Forge's
    // validation logic will correctly reject it.  So, we skip validation when we're running tests.
    if (TEST_MODE || $form->validate()) {
      $file = $_POST["file"];
      // Forge prefixes files with "uploadfile-xxxxxxx" for uniqueness
      $name = preg_replace("/uploadfile-[^-]+-(.*)/", '$1', basename($file));

      try {
        list ($width, $height, $mime_type, $extension) = Photo::get_file_metadata($file);
        // Sanitize filename, which ensures a valid extension.  This renaming prevents the issues
        // addressed in ticket #1855, where an image that looked valid (header said jpg) with a
        // php extension was previously accepted without changing its extension.
        $name = LegalFile::sanitize_filename($name, $extension, "photo");
      } catch (Exception $e) {
        Message::error(t("Invalid or unidentifiable image file"));
        System::delete_later($file);
        return;
      }

      rename($file, VARPATH . "modules/watermark/$name");
      Module::set_var("watermark", "name", $name);
      Module::set_var("watermark", "width", $width);
      Module::set_var("watermark", "height", $height);
      Module::set_var("watermark", "mime_type", $mime_type);
      Module::set_var("watermark", "position", $form->add_watermark->position->value);
      Module::set_var("watermark", "transparency", $form->add_watermark->transparency->value);
      $this->_update_graphics_rules();
      System::delete_later($file);

      Message::success(t("Watermark saved"));
      Log::success("watermark", t("Watermark saved"));
      JSON::reply(array("result" => "success", "location" => URL::site("admin/watermarks")));
    } else {
      JSON::reply(array("result" => "error", "html" => (string)$form));
    }
    // Override the application/json mime type for iframe compatibility.  See ticket #2022.
    header("Content-Type: text/plain; charset=" . Kohana::$charset);
  }

  private function _update_graphics_rules() {
    Graphics::remove_rules("watermark");
    if ($name = Module::get_var("watermark", "name")) {
      foreach (array("thumb", "resize") as $target) {
        Graphics::add_rule(
          "watermark", $target, "GalleryGraphics::composite",
          array("file" => VARPATH . "modules/watermark/$name",
                "width" => Module::get_var("watermark", "width"),
                "height" => Module::get_var("watermark", "height"),
                "position" => Module::get_var("watermark", "position"),
                "transparency" => 101 - Module::get_var("watermark", "transparency")),
          1000);
      }
    }
  }
}