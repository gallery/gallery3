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
class Admin_Theme_Options_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Theme options");
    $view->content = new View("admin_theme_options.html");
    $view->content->form = $this->_get_edit_form_admin();
    print $view;
  }

  public function save() {
    access::verify_csrf();

    $form = $this->_get_edit_form_admin();
    if ($form->validate()) {
      module::set_var("gallery", "page_size", $form->edit_theme->page_size->value);

      $thumb_size = $form->edit_theme->thumb_size->value;
      if (module::get_var("gallery", "thumb_size") != $thumb_size) {
        graphics::remove_rule("gallery", "thumb", "gallery_graphics::resize");
        graphics::add_rule(
          "gallery", "thumb", "gallery_graphics::resize",
          array("width" => $thumb_size, "height" => $thumb_size, "master" => Image::AUTO),
          100);
        module::set_var("gallery", "thumb_size", $thumb_size);
      }

      $resize_size = $form->edit_theme->resize_size->value;
      if (module::get_var("gallery", "resize_size") != $resize_size) {
        graphics::remove_rule("gallery", "resize", "gallery_graphics::resize");
        graphics::add_rule(
          "gallery", "resize", "gallery_graphics::resize",
          array("width" => $resize_size, "height" => $resize_size, "master" => Image::AUTO),
          100);
        module::set_var("gallery", "resize_size", $resize_size);
      }

      module::set_var("gallery", "header_text", $form->edit_theme->header_text->value);
      module::set_var("gallery", "footer_text", $form->edit_theme->footer_text->value);
      module::set_var("gallery", "show_credits", $form->edit_theme->show_credits->value);
      module::set_var("gallery", "favicon_url", $form->edit_theme->favicon_url->value);
      module::set_var("gallery", "apple_touch_icon_url", $form->edit_theme->apple_touch_icon_url->value);

      module::event("theme_edit_form_completed", $form);

      message::success(t("Updated theme details"));
      url::redirect("admin/theme_options");
    } else {
      $view = new Admin_View("admin.html");
      $view->content = new View("admin_theme_options.html");
      $view->content->form = $form;
      print $view;
    }
  }

  private function _get_edit_form_admin() {
    $form = new Forge("admin/theme_options/save/", "", null, array("id" =>"g-theme-options-form"));
    $group = $form->group("edit_theme")->label(t("Theme layout"));
    $group->input("page_size")->label(t("Items per page"))->id("g-page-size")
      ->rules("required|valid_digit")
      ->callback(array($this, "_valididate_page_size"))
      ->error_messages("required", t("You must enter a number"))
      ->error_messages("valid_digit", t("You must enter a number"))
      ->error_messages("valid_min_value", t("The value must be greater than zero"))
      ->value(module::get_var("gallery", "page_size"));
    $group->input("thumb_size")->label(t("Thumbnail size (in pixels)"))->id("g-thumb-size")
      ->rules("required|valid_digit")
      ->error_messages("required", t("You must enter a number"))
      ->error_messages("valid_digit", t("You must enter a number"))
      ->value(module::get_var("gallery", "thumb_size"));
    $group->input("resize_size")->label(t("Resized image size (in pixels)"))->id("g-resize-size")
      ->rules("required|valid_digit")
      ->error_messages("required", t("You must enter a number"))
      ->error_messages("valid_digit", t("You must enter a number"))
      ->value(module::get_var("gallery", "resize_size"));
    $group->input("favicon_url")->label(t("URL (or relative path) to your favicon.ico"))
      ->id("g-favicon")
      ->value(module::get_var("gallery", "favicon_url"));
    $group->input("apple_touch_icon_url")->label(t("URL (or relative path) to your Apple Touch icon"))
      ->id("g-apple-touch")
      ->value(module::get_var("gallery", "apple_touch_icon_url"));
    $group->textarea("header_text")->label(t("Header text"))->id("g-header-text")
      ->value(module::get_var("gallery", "header_text"));
    $group->textarea("footer_text")->label(t("Footer text"))->id("g-footer-text")
      ->value(module::get_var("gallery", "footer_text"));
    $group->checkbox("show_credits")->label(t("Show site credits"))->id("g-footer-text")
      ->checked(module::get_var("gallery", "show_credits"));

    module::event("theme_edit_form", $form);

    $group->submit("")->value(t("Save"));
    return $form;
  }

  function _valididate_page_size($input) {
    if ($input->value < 1) {
      $input->add_error("valid_min_value", true);
    }

  }
}

