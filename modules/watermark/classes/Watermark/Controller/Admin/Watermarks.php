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
  /**
   * Show the main admin screen.
   */
  public function action_index() {
    $name = Module::get_var("watermark", "name");

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Watermarks");
    $view->content = new View("admin/watermarks.html");
    if ($name) {
      $view->content->url = URL::file("var/modules/watermark/$name");
      $view->content->width = Module::get_var("watermark", "width");
      $view->content->height = Module::get_var("watermark", "height");
      $view->content->position =
        Arr::get(static::get_positions(), Module::get_var("watermark", "position"));
      $view->content->transparency = Module::get_var("watermark", "transparency");
    }
    $this->response->body($view);
  }

  /**
   * Add the watermark.  This generates the form, validates it, processes the uploaded file,
   * updates the watermark config, and returns a response.  This is an ajax dialog from index.
   */
  public function action_add() {
    $form = Formo::form()
      ->attr("id", "g-add-watermark-form")
      ->add("watermark", "group");
    $form->watermark
      ->set("label", t("Upload watermark"))
      ->add("data_file",    "file")
      ->add("position",     "select", "southeast")
      ->add("transparency", "select", 1)
      ->add("submit",       "input|submit", t("Upload"));
    $form->watermark->data_file
      ->set("label", t("Watermark"))
      ->add_rule("not_empty",    array(":value"),       t("You must select a watermark"))
      ->add_rule("Upload::type",
          array(":value", array("jpg", "gif", "png")),  t("The watermark must be a JPG, GIF or PNG"))
      ->add_rule("Upload::size", array(":value", "1M"), t("The watermark is too big (1 MB max)"));
    $form->watermark->position
      ->set("label", t("Watermark position"))
      ->set("opts", static::get_positions());
    $form->watermark->transparency
      ->set("label", t("Transparency (100% = completely transparent)"))
      ->set("opts", static::get_transparencies());

    if ($form->load()->validate()) {
      $file_array = $form->watermark->data_file->val();
      $name = $file_array["name"];
      $path = $file_array["tmp_name"];

      try {
        list ($width, $height, $mime_type, $extension) = Photo::get_file_metadata($path);
        // Sanitize filename, which ensures a valid extension.  This renaming prevents the issues
        // addressed in ticket #1855, where an image that looked valid (header said jpg) with a
        // php extension was previously accepted without changing its extension.
        $name = LegalFile::sanitize_filename($name, $extension, "photo");
      } catch (Exception $e) {
        Message::error(t("Invalid or unidentifiable image file"));
        System::delete_later($path);
        return;
      }

      $new_path = VARPATH . "modules/watermark/$name";
      rename($path, $new_path);
      chmod($new_path, 0644);
      System::delete_later($path);

      Module::set_var("watermark", "name",         $name);
      Module::set_var("watermark", "width",        $width);
      Module::set_var("watermark", "height",       $height);
      Module::set_var("watermark", "mime_type",    $mime_type);

      Module::set_var("watermark", "position",     $form->watermark->position->val());
      Module::set_var("watermark", "transparency", $form->watermark->transparency->val());
      $this->_update_graphics_rules();

      Message::success(t("Watermark saved"));
      GalleryLog::success("watermark", t("Watermark saved"));
    }

    $this->response->ajax_form($form);
  }

  /**
   * Edit the watermark.  This generates the form, validates it, updates the watermark config,
   * and returns a response.  This is an ajax dialog from index.
   */
  public function action_edit() {
    $form = Formo::form()
      ->attr("id", "g-edit-watermark-form")
      ->add("watermark", "group");
    $form->watermark
      ->set("label", t("Edit Watermark"))
      ->add("position",     "select", Module::get_var("watermark", "position"))
      ->add("transparency", "select", Module::get_var("watermark", "transparency"))
      ->add("submit",       "input|submit", t("Save"));
    $form->watermark->position
      ->set("label", t("Watermark position"))
      ->set("opts", static::get_positions());
    $form->watermark->transparency
      ->set("label", t("Transparency (100% = completely transparent)"))
      ->set("opts", static::get_transparencies());

    if ($form->load()->validate()) {
      Module::set_var("watermark", "position",     $form->watermark->position->val());
      Module::set_var("watermark", "transparency", $form->watermark->transparency->val());
      $this->_update_graphics_rules();

      GalleryLog::success("watermark", t("Watermark changed"));
      Message::success(t("Watermark changed"));
    }

    $this->response->ajax_form($form);
  }

  /**
   * Delete the watermark.  This generates the form, validates it, updates the watermark config,
   * and returns a response.  This is an ajax dialog from index.
   */
  public function action_delete() {
    $form = Formo::form()
      ->attr("id", "g-delete-watermark-form")
      ->add("confirm", "group");
    $form->confirm
      ->set("label", t("Confirm Deletion"))
      ->html(t("Really delete Watermark?"))
      ->add("submit", "input|submit", t("Delete"));

    if ($form->load()->validate()) {
      if ($name = basename(Module::get_var("watermark", "name"))) {
        System::delete_later(VARPATH . "modules/watermark/$name");
        Module::clear_var("watermark", "name");
        Module::clear_var("watermark", "width");
        Module::clear_var("watermark", "height");
        Module::clear_var("watermark", "mime_type");

        Module::clear_var("watermark", "position");
        Module::clear_var("watermark", "transparency");
        $this->_update_graphics_rules();

        GalleryLog::success("watermark", t("Watermark deleted"));
        Message::success(t("Watermark deleted"));
      }
    }

    $this->response->ajax_form($form);
  }

  /**
   * Update the graphics rules.  This is a helper for the edit/add/delete actions.
   */
  protected function _update_graphics_rules() {
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

  /**
   * Return a structured set of all the possible positions.
   */
  public static function get_positions() {
    return array("northwest" => t("Northwest"),
                 "north"     => t("North"),
                 "northeast" => t("Northeast"),
                 "west"      => t("West"),
                 "center"    => t("Center"),
                 "east"      => t("East"),
                 "southwest" => t("Southwest"),
                 "south"     => t("South"),
                 "southeast" => t("Southeast"));
  }

  /**
   * Return a structured set of all the possible transparency levels.
   */
  public static function get_transparencies() {
    $range = array();
    for ($i = 1; $i <= 100; $i++) {
      $range[$i] = "$i%";
    }
    return $range;
  }
}