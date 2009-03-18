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
  public function index($share_translations_form=null) {
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_languages.html");
    $v->content->settings_form = $this->_languages_form();
    $v->content->update_translations_form = $this->_translation_updates_form();
    if (empty($share_translations_form)) {
      $share_translations_form = $this->_share_translations_form();
    }
    $v->content->share_translations_form = $share_translations_form;
    $this->_outgoing_translations_count();
    print $v;
  }

  public function save() {
    $form = $this->_languages_form();
    if ($form->validate()) {
      module::set_var("core", "default_locale", $form->choose_language->locale->value);
      locale::update_installed($form->choose_language->installed_locales->value);
      message::success(t("Settings saved"));
    }
    url::redirect("admin/languages");
  }

  public function fetch_updates() {
    // TODO: Convert this to AJAX / progress bar.
    $form = $this->_translation_updates_form();
    if ($form->validate()) {
      L10n_Scanner::instance()->update_index();
      l10n_client::fetch_updates();
      message::success(t("Translations installed/updated"));
    }
    url::redirect("admin/languages");
  }

  public function share() {
    $form = $this->_share_translations_form();
    if (!$form->validate()) {
      // Show the page with form errors
      return $this->index($form);
    }

    if ($form->sharing->share) {
      l10n_client::submit_translations();
      message::success(t("Translations submitted"));
    } else {
      return $this->_save_api_key($form);
    }
    url::redirect("admin/languages");
  }

  private function _save_api_key($form) {
    $new_key = $form->sharing->api_key->value;
    if ($new_key && !l10n_client::validate_api_key($new_key)) {
      $form->sharing->api_key->add_error("invalid", 1);
      $valid = false;
    } else {
      $valid = true;
    }

    if ($valid) {
        $old_key = l10n_client::api_key();
        l10n_client::api_key($new_key);
        if ($old_key && !$new_key) {
          message::success(t("Your API key has been cleared."));
        } else if ($old_key && $new_key && $old_key != $new_key) {
          message::success(t("Your API key has been changed."));
        } else if (!$old_key && $new_key) {
          message::success(t("Your API key has been saved."));
        }

        log::success(t("core"), t("l10n_client API key changed."));
        url::redirect("admin/languages");
    } else {
      // Show the page with form errors
      $this->index($form);
    }
  }
  
  private function _languages_form() {
    $all_locales = locale::available();
    $installed_locales = locale::installed();
    $form = new Forge("admin/languages/save", "", "post", array("id" => "gLanguageSettingsForm"));
    $group = $form->group("choose_language")
      ->label(t("Language settings"));
    $group->dropdown("locale")
      ->options($installed_locales)
      ->selected(module::get_var("core", "default_locale"))
      ->label(t("Default language"))
      ->rules('required');

    $installation_options = array();
    foreach ($all_locales as $code => $display_name) {
      $installation_options[$code] = array($display_name, isset($installed_locales->$code));
    }
    $group->checklist("installed_locales")
      ->label(t("Installed Languages"))
      ->options($installation_options)
      ->rules("required");
    $group->submit("save")->value(t("Save settings"));
    return $form;
  }

  private function _translation_updates_form() {
    // TODO: Show a timestamp of the last update.
    // TODO: Show a note if you've changed the language settings but not fetched translations for
    //       the selected languages yet.
    $form = new Forge("admin/languages/fetch_updates", "", "post", array("id" => "gLanguageUpdatesForm"));
    $group = $form->group("updates")
      ->label(t("Download translations for all selected languages from the Gallery Translation Server:"));
    $group->submit("update")->value(t("Get updates"));
    return $form;
  }

  private function _outgoing_translations_count() {
    return Database::instance()
      ->query("SELECT COUNT(*) AS `C` FROM outgoing_translations")
      ->current()->C;
  }

  private function _share_translations_form() {
    $form = new Forge("admin/languages/share", "", "post", array("id" => "gShareTranslationsForm"));
    $group = $form->group("sharing")
      ->label(t("Sharing you own translations with the Gallery community is easy. Please do!"));
    $api_key = l10n_client::api_key();
    $server_link = l10n_client::server_api_key_url();
    $group->input("api_key")
      ->label(empty($api_key)
              ? t("This is a unique key that will allow you to send translations to the remote server. To get your API key go to %server-link.",
                  array("server-link" => html::anchor($server_link)))
              : t("API Key"))
      ->value($api_key)
      ->error_messages("invalid", t("The API key you provided is invalid."));
    $group->submit("save")->value(t("Save settings"));
    if ($api_key && $this->_outgoing_translations_count()) {
      // TODO: UI improvement: hide API key / save button when API key is set.
      $group->submit("share")->value(t("Submit translations"));
    }
    return $form;
  }
}

