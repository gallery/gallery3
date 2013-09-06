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
class Gallery_Controller_Admin_ThemeOptions extends Controller_Admin {
  public function action_index() {
    $form = Formo::form()
      ->attr("id", "g-theme-options-form")
      ->add("theme", "group");
    $form->theme
      ->set("label", t("Theme layout"))
      ->add("page_size",            "input",    Module::get_var("gallery", "page_size"))
      ->add("thumb_size",           "input",    Module::get_var("gallery", "thumb_size"))
      ->add("resize_size",          "input",    Module::get_var("gallery", "resize_size"))
      ->add("favicon_url",          "input",    Module::get_var("gallery", "favicon_url"))
      ->add("apple_touch_icon_url", "input",    Module::get_var("gallery", "apple_touch_icon_url"))
      ->add("header_text",          "textarea", Module::get_var("gallery", "header_text"))
      ->add("footer_text",          "textarea", Module::get_var("gallery", "footer_text"))
      ->add("show_credits",         "checkbox", Module::get_var("gallery", "show_credits"))
      ->add("submit",               "input|submit", t("Save"));
    $form->theme->page_size
      ->set("label", t("Items per page"))
      ->add_rule("not_empty", array(":value"),           t("You must enter a number"))
      ->add_rule("digit",     array(":value"),           t("You must enter a number"))
      ->add_rule("range",     array(":value", 1, 1e100), t("The value must be greater than zero"));
    $form->theme->thumb_size
      ->set("label", t("Thumbnail size (in pixels)"))
      ->add_rule("not_empty", array(":value"),           t("You must enter a number"))
      ->add_rule("digit",     array(":value"),           t("You must enter a number"))
      ->add_rule("range",     array(":value", 1, 1e100), t("The value must be greater than zero"));
    $form->theme->resize_size
      ->set("label", t("Resized image size (in pixels)"))
      ->add_rule("not_empty", array(":value"),           t("You must enter a number"))
      ->add_rule("digit",     array(":value"),           t("You must enter a number"))
      ->add_rule("range",     array(":value", 1, 1e100), t("The value must be greater than zero"));
    $form->theme->favicon_url
      ->set("label", t("URL (or relative path) to your favicon.ico"));
    $form->theme->apple_touch_icon_url
      ->set("label", t("URL (or relative path) to your Apple Touch icon"));
    $form->theme->header_text
      ->set("label", t("Header text"));
    $form->theme->footer_text
      ->set("label", t("Footer text"));
    $form->theme->show_credits
      ->set("label", t("Show site credits"));

    Module::event("theme_edit_form", $form);

    if ($form->load()->validate()) {
      Module::set_var("gallery", "page_size",    $form->theme->page_size->val());
      Module::set_var("gallery", "show_credits", $form->theme->show_credits->val());

      // Sanitize values that get placed directly in HTML output by theme.
      Module::set_var("gallery", "favicon_url",
        Purifier::clean_html($form->theme->favicon_url->val()));
      Module::set_var("gallery", "apple_touch_icon_url",
        Purifier::clean_html($form->theme->apple_touch_icon_url->val()));
      Module::set_var("gallery", "header_text",
        Purifier::clean_html($form->theme->header_text->val()));
      Module::set_var("gallery", "footer_text",
        Purifier::clean_html($form->theme->footer_text->val()));

      foreach (array("thumb", "resize") as $type) {
        $size = $form->theme->{"{$type}_size"}->val();

        if (Module::get_var("gallery", "{$type}_size") != $size) {
          Graphics::remove_rule("gallery", $type, "GalleryGraphics::resize");
          Graphics::add_rule("gallery", $type, "GalleryGraphics::resize", array(
            "width"  => $size,
            "height" => $size,
            "master" => Image::AUTO
          ), 100);
          Module::set_var("gallery", "{$type}_size", $size);
        }
      }

      Module::event("theme_edit_form_completed", $form);
      Message::success(t("Updated theme details"));
    }

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Theme options");
    $view->content = new View("admin/theme_options.html");
    $view->content->form = $form;
    $this->response->body($view);
  }
}
