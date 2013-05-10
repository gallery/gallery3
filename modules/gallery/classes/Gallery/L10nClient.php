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
class Gallery_L10nClient {
  const URL = "http://galleryproject.org/translations/";
  const API_VERSION = "1.0";

  const SERVER_API_KEY   = "userkey/";
  const VALIDATE_API_KEY = "status";
  const FETCH            = "fetch";
  const SUBMIT           = "submit";

  /**
   * Get the server API KEY URL.  This isn't used in this class, but is used elsewhere
   * (e.g. Controller_Languages::action_share()).
   */
  static function server_api_key_url() {
    return static::URL . static::SERVER_API_KEY . static::client_token();
  }

  /**
   * Get the client token.
   */
  static function client_token() {
    return md5("l10n_client_client_token" . Access::private_key());
  }

  /**
   * Get or set the API key.
   */
  static function api_key($api_key=null) {
    if ($api_key !== null) {
      Module::set_var("gallery", "l10n_client_key", $api_key);
    }
    return Module::get_var("gallery", "l10n_client_key", "");
  }

  /**
   * Get the server UID.  If no API key is given, the configured key is used.
   */
  static function server_uid($api_key=null) {
    $api_key = $api_key == null ? static::api_key() : $api_key;
    $parts = explode(":", $api_key);
    return empty($parts) ? 0 : $parts[0];
  }

  /**
   * Get the signature for a data payload.  If no API key is given, the configured key is used.
   * The payload should already be JSON encoded.
   */
  protected static function _sign($payload, $api_key=null) {
    $api_key = $api_key == null ? static::api_key() : $api_key;
    return md5($api_key . $payload . static::client_token());
  }

  /**
   * Validate an API key.  This returns an array of two booleans: the first is whether or not a
   * server connection was made, and the second is whether or not the key was validated.
   *
   * @param  string $api_key candidate API key
   * @return array           array([connected?], [validated?])
   */
  static function validate_api_key($api_key) {
    try {
      $response = Request::factory(static::URL . static::VALIDATE_API_KEY)
        ->method(Request::POST)
        ->post(array(
            "version"      => static::API_VERSION,
            "client_token" => static::client_token(),
            "signature"    => static::_sign(static::API_VERSION, $api_key),
            "uid"          => static::server_uid($api_key)
          ))
        ->execute();
    } catch (Exception $e) {
      // Log the error, but then return a "can't make connection" error
      Log::instance()->add(Log::ERROR, $e->getMessage() . "\n" . $e->getTraceAsString());
      $response = null;
    }

    if (!isset($response)) {
      return array(false, false);
    }

    if (floor($response->status() / 100) != 2) {  // i.e. status isn't 2xx
      return array(true, false);
    }

    return array(true, true);
  }

  /**
   * Fetches translations for l10n messages. Must be called repeatedly
   * until 0 is returned (which is a countdown indicating progress).
   *
   * @param $num_fetched in/out parameter to specify which batch of
   *     messages to fetch translations for.
   * @return The number of messages for which we didn't fetch
   *     translations for.
   */
  static function fetch_updates(&$num_fetched) {
    $data = new stdClass();
    $data->locales = array();
    $data->messages = new stdClass();

    $locales = Locales::installed();
    foreach ($locales as $locale => $locale_data) {
      $data->locales[] = $locale;
    }

    // See the server side code for how we arrive at this
    // number as a good limit for #locales * #messages.
    $max_messages = 2000 / count($locales);
    $num_messages = 0;
    $rows = DB::select("key", "locale", "revision", "translation")
      ->from("incoming_translations")
      ->order_by("key")
      ->limit(1000000)  // ignore, just there to satisfy SQL syntax
      ->offset($num_fetched)
      ->as_object()
      ->execute();
    $num_remaining = $rows->count();
    foreach ($rows as $row) {
      if (!isset($data->messages->{$row->key})) {
        if ($num_messages >= $max_messages) {
          break;
        }
        $data->messages->{$row->key} = 1;
        $num_messages++;
      }
      if (!empty($row->revision) && !empty($row->translation) &&
          isset($locales[$row->locale])) {
        if (!is_object($data->messages->{$row->key})) {
          $data->messages->{$row->key} = new stdClass();
        }
        $data->messages->{$row->key}->{$row->locale} = (int) $row->revision;
      }
      $num_fetched++;
      $num_remaining--;
    }
    // @todo Include messages from outgoing_translations?

    if (!$num_messages) {
      return $num_remaining;
    }

    $data = json_encode($data);
    $response = Request::factory(static::URL . static::FETCH)
      ->method(Request::POST)
      ->post(array("data" => $data))
      ->execute();

    $code = $response->status();
    if (floor($code / 100) != 2) {  // i.e. status isn't 2xx
      throw new Gallery_Exception("Translations fetch request failed: response status $code");
    }

    $response = $response->body();
    if (empty($response)) {
      return $num_remaining;
    }

    $response = json_decode($response);

    // Response format (JSON payload):
    //   [{key:<key_1>, translation: <JSON encoded translation>, rev:<rev>, locale:<locale>},
    //    {key:<key_2>, ...}
    //   ]
    foreach ($response as $message_data) {
      // @todo Better input validation
      if (empty($message_data->key) || empty($message_data->translation) ||
          empty($message_data->locale) || empty($message_data->rev)) {
        throw new Gallery_Exception("Translations fetch request failed: invalid response data");
      }
      $key = $message_data->key;
      $locale = $message_data->locale;
      $revision = $message_data->rev;
      $translation = json_decode($message_data->translation);
      if (!is_string($translation)) {
        // Normalize stdclass to array
        $translation = (array) $translation;
      }
      $translation = serialize($translation);

      // @todo Should we normalize the incoming_translations table into messages(id, key, message)
      // and incoming_translations(id, translation, locale, revision)? Or just allow
      // incoming_translations.message to be NULL?
      $locale = $message_data->locale;
      $entry = ORM::factory("IncomingTranslation")
        ->where("key", "=", $key)
        ->where("locale", "=", $locale)
        ->find();
      if (!$entry->loaded()) {
        // @todo Load a message key -> message (text) dict into memory outside of this loop
        $root_entry = ORM::factory("IncomingTranslation")
          ->where("key", "=", $key)
          ->where("locale", "=", "root")
          ->find();
        $entry->key = $key;
        $entry->message = $root_entry->message;
        $entry->locale = $locale;
      }
      $entry->revision = $revision;
      $entry->translation = $translation;
      $entry->save();
    }

    return $num_remaining;
  }

  /**
   * Submit our outgoing translations to the server.
   * @return void
   */
  static function submit_translations() {
    // Request format (HTTP POST):
    //   client_token = <client_token>
    //   uid = <l10n server user id>
    //   signature = md5(user_api_key($uid, $client_token) . $data . $client_token))
    //   data = // JSON payload
    //
    //     {<key_1>: {message: <JSON encoded message>
    //                translations: {<locale_1>: <JSON encoded translation>,
    //                               <locale_2>: ...}},
    //      <key_2>: {...}
    //     }

    // @todo Batch requests (max request size)
    // @todo include base_revision in submission / how to handle resubmissions / edit fights?
    $data = new stdClass();
    foreach (DB::select("key", "message", "locale", "base_revision", "translation")
             ->from("outgoing_translations")
             ->as_object()
             ->execute() as $row) {
      $key = $row->key;
      if (!isset($data->{$key})) {
        $data->{$key} = new stdClass();
        $data->{$key}->translations = new stdClass();
        $data->{$key}->message = json_encode(unserialize($row->message));
      }
      $data->{$key}->translations->{$row->locale} = json_encode(unserialize($row->translation));
    }

    $data = json_encode($data);
    $response = Request::factory(static::URL . static::SUBMIT)
      ->method(Request::POST)
      ->post(array(
          "data"         => $data,
          "client_token" => static::client_token(),
          "signature"    => static::_sign($data),
          "uid"          => static::server_uid()
        ))
      ->execute();

    $code = $response->status();
    if (floor($code / 100) != 2) {  // i.e. status isn't 2xx
      throw new Gallery_Exception("Translations submission failed: response status $code");
    }

    // $response = json_decode($response->body());
    // Response format (JSON payload):
    //   [{key:<key_1>, locale:<locale_1>, rev:<rev_1>, status:<rejected|accepted|pending>},
    //    {key:<key_2>, ...}
    //   ]

    // @todo Move messages out of outgoing into incoming, using new rev?
    // @todo show which messages have been rejected / are pending?
  }

  /**
   * Plural forms.  This returns an array of which types of forms are needed for a given locale.
   * The possible values are "zero", "one", "two", "few", "many", and "other".
   *
   * @param  string $locale
   * @return array
   */
  static function plural_forms($locale) {
    $parts = explode('_', $locale);
    $language = $parts[0];

    // Data from CLDR 1.6 (http://unicode.org/cldr/data/common/supplemental/plurals.xml).
    // Docs: http://www.unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
    switch ($language) {
      case 'az':
      case 'fa':
      case 'hu':
      case 'ja':
      case 'ko':
      case 'my':
      case 'to':
      case 'tr':
      case 'vi':
      case 'yo':
      case 'zh':
      case 'bo':
      case 'dz':
      case 'id':
      case 'jv':
      case 'ka':
      case 'km':
      case 'kn':
      case 'ms':
      case 'th':
        return array('other');

      case 'ar':
        return array('zero', 'one', 'two', 'few', 'many', 'other');

      case 'lv':
        return array('zero', 'one', 'other');

      case 'ga':
      case 'se':
      case 'sma':
      case 'smi':
      case 'smj':
      case 'smn':
      case 'sms':
        return array('one', 'two', 'other');

      case 'ro':
      case 'mo':
      case 'lt':
      case 'cs':
      case 'sk':
      case 'pl':
        return array('one', 'few', 'other');

      case 'hr':
      case 'ru':
      case 'sr':
      case 'uk':
      case 'be':
      case 'bs':
      case 'sh':
      case 'mt':
        return array('one', 'few', 'many', 'other');

      case 'sl':
        return array('one', 'two', 'few', 'other');

      case 'cy':
        return array('one', 'two', 'many', 'other');

      default: // en, de, etc.
        return array('one', 'other');
    }
  }
}