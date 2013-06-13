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
class Gallery_Controller_L10nClient extends Controller {
  public function action_save() {
    Access::verify_csrf();
    if (!Identity::active_user()->admin) {
      throw HTTP_Exception::factory(403);
    }

    $locale = I18n::instance()->locale();
    $key = $this->request->post("l10n-message-key");

    $root_message = ORM::factory("IncomingTranslation")
      ->where("key", "=", $key)
      ->where("locale", "=", "root")
      ->find();

    if (!$root_message->loaded()) {
      throw new Gallery_Exception("Bad request data / illegal state");
    }
    $is_plural = I18n::is_plural_message(unserialize($root_message->message));

    $is_empty = true;
    if ($is_plural) {
      $plural_forms = L10nClient::plural_forms($locale);
      $translation = array();
      foreach($plural_forms as $plural_form) {
        $value = $this->request->post("l10n-edit-plural-translation-$plural_form");
        if (null === $value || !is_string($value)) {
          throw new Gallery_Exception("Bad request data");
        }
        $translation[$plural_form] = $value;
        $is_empty = $is_empty && empty($value);
      }
    } else {
      $translation = $this->request->post("l10n-edit-translation");
      $is_empty = empty($translation);
      if (null === $translation || !is_string($translation)) {
        throw new Gallery_Exception("Bad request data");
      }
    }

    $entry = ORM::factory("OutgoingTranslation")
      ->where("key", "=", $key)
      ->where("locale", "=", $locale)
      ->find();

    if ($is_empty) {
      if ($entry->loaded()) {
        $entry->delete();
      }
    } else {
      if (!$entry->loaded()) {
        $entry->key = $key;
        $entry->locale = $locale;
        $entry->message = $root_message->message;
        $entry->base_revision = null;
      }

      $entry->translation = serialize($translation);

      $entry_from_incoming = ORM::factory("IncomingTranslation")
        ->where("key", "=", $key)
        ->where("locale", "=", $locale)
        ->find();

      if (!$entry_from_incoming->loaded()) {
        $entry->base_revision = $entry_from_incoming->revision;
      }

      $entry->save();
    }

    I18n::clear_cache($locale);

    $this->response->json(new stdClass());
  }

  public function action_toggle_l10n_mode() {
    Access::verify_csrf();
    if (!Identity::active_user()->admin) {
      throw HTTP_Exception::factory(403);
    }

    $session = Session::instance();
    $l10n_mode = $session->get("l10n_mode", false);
    $session->set("l10n_mode", !$l10n_mode);

    $redirect_url = "admin/languages";
    if (!$l10n_mode) {
      $redirect_url .= "#l10n-client";
    }

    $this->redirect($redirect_url);
  }

  protected static function _l10n_client_search_form() {
    $form = Formo::form()
      ->attr("id", "g-l10n-search-form")
      ->attr("action", "#")
      ->add("search", "group");
    $form->search
      ->set("label", "")
      ->add("terms", "input");
    $form->search->terms
      ->set("label", "")
      ->attr("id", "g-l10n-search");

    return $form;
  }

  public static function l10n_form() {
    if (Request::current()->query("show_all_l10n_messages")) {
      $calls = array();
      foreach (DB::select("key", "message")
               ->from("incoming_translations")
               ->where("locale", "=", "root")
               ->as_object()
               ->execute() as $row) {
        $calls[$row->key] = array(unserialize($row->message), array());
      }
    } else {
      $calls = I18n::instance()->call_log();
    }
    $locale = I18n::instance()->locale();

    if ($calls) {
      $translations = array();
      foreach (DB::select("key", "translation")
               ->from("incoming_translations")
               ->where("locale", "=", $locale)
               ->as_object()
               ->execute() as $row) {
        $translations[$row->key] = unserialize($row->translation);
      }
      // Override incoming with outgoing...
      foreach (DB::select("key", "translation")
               ->from("outgoing_translations")
               ->where("locale", "=", $locale)
               ->as_object()
               ->execute() as $row) {
        $translations[$row->key] = unserialize($row->translation);
      }

      $string_list = array();
      $cache = array();
      foreach ($calls as $key => $call) {
        list ($message, $options) = $call;
        // Ensure that the message is in the DB
        L10nScanner::process_message($message, $cache);
        // Note: Not interpolating placeholders for the actual translation input field.
        // TODO: Might show a preview w/ interpolations (using $options)
        $translation = isset($translations[$key]) ? $translations[$key] : '';
        $string_list[] = array('source' => $message,
                               'key' => $key,
                               'translation' => $translation);
      }

      $v = new View('gallery/l10n_client.html');
      $v->string_list = $string_list;
      $v->l10n_search_form = static::_l10n_client_search_form();
      $v->plural_forms = L10nClient::plural_forms($locale);
      return $v;
    }

    return '';
  }
}
