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
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_languages.html");
    $v->content->form = $this->_languages_form();
    print $v;
  }

  public function save() {
    $form = $this->_languages_form();
    if ($form->validate()) {
      module::set_var("core", "default_locale", $form->choose_language->locale->value);
      message::success(t("Settings saved"));
    }
    url::redirect("admin/languages");
  }

  private function _languages_form() {
    $locales = locale::available();
    $form = new Forge("admin/languages/save", "", "post", array("id" => "gLanguageSettingsForm"));
    $group = $form->group("choose_language")
      ->label(t("Please select a language"));
    $group->dropdown("locale")
      ->options($locales)
      ->selected(module::get_var("core", "default_locale"));
    $group->submit("save")->value(t("Save settings"));
    return $form;
  }
}

