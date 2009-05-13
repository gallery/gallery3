<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class L10n_Client_Controller extends Controller {
  public function save() {
    access::verify_csrf();
    user::active()->admin or access::forbidden();

    $input = Input::instance();
    $message = $input->post("l10n-message-source");
    $translation = $input->post("l10n-edit-target");
    $key = I18n::get_message_key($message);
    $locale = I18n::instance()->locale();

    $entry = ORM::factory("outgoing_translation")
      ->where(array("key" => $key,
                    "locale" => $locale))
      ->find();

    if (!$entry->loaded) {
      $entry->key = $key;
      $entry->locale = $locale;
      $entry->message = serialize($message);
      $entry->base_revision = null;
    }

    $entry->translation = serialize($translation);

    $entry_from_incoming = ORM::factory("incoming_translation")
      ->where(array("key" => $key,
                    "locale" => $locale))
      ->find();

    if (!$entry_from_incoming->loaded) {
      $entry->base_revision = $entry_from_incoming->revision;
    }

    $entry->save();

    print json_encode(new stdClass());
  }

  public function toggle_l10n_mode() {
    access::verify_csrf();

    $session = Session::instance();
    $session->set("l10n_mode",
                  !$session->get("l10n_mode", false));

    url::redirect("albums/1");
  }

  private static function _l10n_client_form() {
    $form = new Forge("l10n_client/save", "", "post", array("id" => "gL10nClientSaveForm"));
    $group = $form->group("l10n_message");
    $group->hidden("l10n-message-source")->value("");
    $group->textarea("l10n-edit-target");
    $group->submit("l10n-edit-save")->value(t("Save translation"));
    // TODO(andy_st): Avoiding multiple submit buttons for now (hassle with jQuery form plugin).
    // $group->submit("l10n-edit-copy")->value(t("Copy source"));
    // $group->submit("l10n-edit-clear")->value(t("Clear"));

    return $form;
  }

  private static function _l10n_client_search_form() {
    $form = new Forge("l10n_client/search", "", "post", array("id" => "gL10nSearchForm"));
    $group = $form->group("l10n_search");
    $group->input("l10n-search")->id("gL10nSearch");
    $group->submit("l10n-search-filter-clear")->value(t("X"));

    return $form;
  }

  public static function l10n_form() {
    $calls = I18n::instance()->call_log();

    if ($calls) {
      $string_list = array();
      foreach ($calls as $call) {
        list ($message, $options) = $call;
        // Note: Don't interpolate placeholders for the actual translation input field.
        // TODO: Use $options to generate a preview.
        if (is_array($message)) {
          // TODO: Handle plural forms.
          //   Translate each message. If it has a plural form, get
          //   the current locale's plural rules and all plural translations.
          continue;
        }
        $source = $message;
        $translation = '';
        $options_for_raw_translation = array();
        if (isset($options['count'])) {
          $options_for_raw_translation['count'] = $options['count'];
        }
        if (I18n::instance()->has_translation($message, $options_for_raw_translation)) {
          $translation = I18n::instance()->translate($message, $options_for_raw_translation);
        }
        $string_list[] = array('source' => $source,
                               'translation' => $translation);
      }

      $v = new View('l10n_client.html');
      $v->string_list = $string_list;
      $v->l10n_form = self::_l10n_client_form();
      $v->l10n_search_form = self::_l10n_client_search_form();
      return $v;
    }

    return '';
  }
}
