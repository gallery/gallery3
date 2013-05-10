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
    // This view has two forms - one provided by the admin/languages.html view,
    // and one provided by the share_translations_form() view.
    $v = new View_Admin("required/admin.html");
    $v->page_title = t("Languages and translations");
    $v->content = new View("admin/languages.html");
    $v->content->available_locales = Locales::available();
    $v->content->installed_locales = Locales::installed();
    $v->content->default_locale = Module::get_var("gallery", "default_locale");
    $v->content->share_translations_form = $this->share_translations_form();

    $this->response->body($v);
  }

  public function action_save() {
    Access::verify_csrf();

    Locales::update_installed($this->request->post("installed_locales"));

    $installed_locales = array_keys(Locales::installed());
    $new_default_locale = $this->request->post("default_locale");
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

  public function share_translations_form() {
    $api_key = L10nClient::api_key();
    $server_link = L10nClient::server_api_key_url();

    $form = Formo::form()
      ->attr("id", "g-share-translations-form")
      ->add("sharing", "group");
    $form->sharing
      ->set("label", t("Translations API Key"))
      ->add("api_key", "input")
      ->add("save",  "input|submit", t("Save settings"))
      ->add("share", "input|submit", t("Submit translations"));
    $form->sharing->api_key
      ->set("label", empty($api_key) ? t("This is a unique key that will allow you to send translations to the remote server. To get your API key go to %server-link.",
          array("server-link" => HTML::mark_clean(HTML::anchor($server_link)))) : t("API key"))
      ->set("error_messages", array(
          "invalid"       => t("The API key you provided is invalid."),
          "no_connection" => t("Could not connect to remote server to validate the API key.")
        ))
      ->val($api_key)
      ->callback("pass", array("Controller_Admin_Languages::validate_api_key"));
    $form->sharing->save
      ->set("can_be_empty", true);  // We use this since we have multiple buttons on this form.
    $form->sharing->share
      ->set("can_be_empty", true);

    if ($form->load()->validate()) {
      if ($form->sharing->save->val()) {
        // User hit save button - update API key.
        $new_key = $form->sharing->api_key->val();
        $old_key = L10nClient::api_key();
        L10nClient::api_key($new_key);
        if ($old_key && $new_key && $old_key == $new_key) {
          Message::info(t("Your API key was not changed."));
        } else {
          if ($old_key && !$new_key) {
            Message::success(t("Your API key has been cleared."));
          } else if (!$old_key && $new_key) {
            Message::success(t("Your API key has been saved."));
          } else {
            Message::success(t("Your API key has been changed."));
          }
          GalleryLog::success(t("gallery"), t("l10n_client API key changed."));
        }
      } else if ($form->sharing->share->val()) {
        // User hit share button - submit translations.
        L10nClient::submit_translations();
        Message::success(t("Translations submitted"));
      }
    }

    if (!L10nClient::api_key() || !ORM::factory("OutgoingTranslation")->count_all()) {
      // Nothing to share - hide the share button.
      $form->sharing->remove("share");
    }

    return $form;
  }

  /**
   * Validate API key callback to translate L10nClient::validate_api_key() responses
   * to form errors.
   */
  public static function validate_api_key($field) {
    $api_key = $field->val();
    if (empty($api_key)) {
      return;
    }

    list ($connected, $validated) = L10nClient::validate_api_key($api_key);
    if (!$connected) {
      $field->error("no_connection");
    } else if (!$validated) {
      $field->error("invalid");
    }
  }
}
