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
class Gallery_Controller_Admin_Languages extends Controller_Admin {
  public function action_index() {
    $this->show_languages_view();
  }

  public function show_languages_view($share_translations_form=null) {
    $v = new View_Admin("required/admin.html");
    $v->page_title = t("Languages and translations");
    $v->content = new View("admin/languages.html");
                $v->content->available_locales = Locales::available();
    $v->content->installed_locales = Locales::installed();
    $v->content->default_locale = Module::get_var("gallery", "default_locale");

    if (empty($share_translations_form)) {
      $share_translations_form = $this->get_share_translations_form();
    }
    $v->content->share_translations_form = $share_translations_form;
    $this->_outgoing_translations_count();
    $this->response->body($v);
  }

  public function action_save() {
    Access::verify_csrf();

    Locales::update_installed(Request::current()->post("installed_locales"));

    $installed_locales = array_keys(Locales::installed());
    $new_default_locale = Request::current()->post("default_locale");
    if (!in_array($new_default_locale, $installed_locales)) {
      if (!empty($installed_locales)) {
        $new_default_locale = $installed_locales[0];
      } else {
        $new_default_locale = "en_US";
      }
    }
    Module::set_var("gallery", "default_locale", $new_default_locale);

    $this->response->json(array("result" => "success"));
  }

  public function action_share() {
    Access::verify_csrf();

    $form = $this->get_share_translations_form();
    if (!$form->validate()) {
      // Show the page with form errors
      return $this->show_languages_view($form);
    }

    if (Request::current()->post("share")) {
      L10nClient::submit_translations();
      Message::success(t("Translations submitted"));
    } else {
      return $this->_save_api_key($form);
    }
    HTTP::redirect("admin/languages");
  }

  private function _save_api_key($form) {
    $new_key = $form->sharing->api_key->value;
    if ($new_key) {
      list($connected, $valid) = L10nClient::validate_api_key($new_key);
      if (!$valid) {
        $form->sharing->api_key->add_error($connected ? "invalid" : "no_connection", 1);
      }
    } else {
      $valid = true;
    }

    if ($valid) {
        $old_key = L10nClient::api_key();
        L10nClient::api_key($new_key);
        if ($old_key && !$new_key) {
          Message::success(t("Your API key has been cleared."));
        } else if ($old_key && $new_key && $old_key != $new_key) {
          Message::success(t("Your API key has been changed."));
        } else if (!$old_key && $new_key) {
          Message::success(t("Your API key has been saved."));
        } else if ($old_key && $new_key && $old_key == $new_key) {
          Message::info(t("Your API key was not changed."));
        }

        GalleryLog::success(t("gallery"), t("l10n_client API key changed."));
        HTTP::redirect("admin/languages");
    } else {
      // Show the page with form errors
      $this->show_languages_view($form);
    }
  }

  private function _outgoing_translations_count() {
    return ORM::factory("OutgoingTranslation")->count_all();
  }

  public function get_share_translations_form() {
    $form = new Forge("admin/languages/share", "", "post", array("id" => "g-share-translations-form"));
    $group = $form->group("sharing")
      ->label("Translations API Key");
    $api_key = L10nClient::api_key();
    $server_link = L10nClient::server_api_key_url();
    $group->input("api_key")
      ->label(empty($api_key)
              ? t("This is a unique key that will allow you to send translations to the remote
                  server. To get your API key go to %server-link.",
                  array("server-link" => HTML::mark_clean(HTML::anchor($server_link))))
              : t("API key"))
      ->value($api_key)
      ->error_messages("invalid", t("The API key you provided is invalid."))
      ->error_messages(
        "no_connection", t("Could not connect to remote server to validate the API key."));
    $group->submit("save")->value(t("Save settings"));
    if ($api_key && $this->_outgoing_translations_count()) {
      // TODO: UI improvement: hide API key / save button when API key is set.
      $group->submit("share")->value(t("Submit translations"));
    }
    return $form;
  }
}

