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
class Admin_Languages_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_languages.html");

    $locales = locale::available();
    asort($locales, SORT_LOCALE_STRING);

    $form = new Forge("admin/languages/save", "", "post", array("id" => "gLanguageSettingsForm"));
    $group = $form->group("settings")
      ->label(t("Please select a language"));
    $group->dropdown("locale_selection")
      ->options($locales)
      ->selected(module::get_var("core", "default_locale"));
    $group->submit("save")->value(t("Save settings"));

    $view->content->form = $form;

    print $view;
  }

  public function save() {
    $locales = locale::available();
    $selected_locale = $this->input->post("locale_selection");
    if (!isset($locales[$selected_locale])) {
      message::error(t("Invalid selection"));
    } else {
      module::set_var("core", "default_locale", $selected_locale);
      message::success(t("Settings saved"));
    }
    url::redirect("admin/languages");
  }
}

