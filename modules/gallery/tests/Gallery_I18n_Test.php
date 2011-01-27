<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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

class Gallery_I18n_Test extends Gallery_Unit_Test_Case {
  private $i18n;

  public function setup() {
    $config = array(
        'root_locale' => 'en',
        'default_locale' => 'te_ST',
        'locale_dir' => VARPATH . 'locale/');
    $this->i18n = Gallery_I18n::instance($config);

    db::build()
      ->delete("incoming_translations")
      ->where("locale", "=", "te_ST")
      ->execute();

    $messages_te_ST = array(
        array('Hello world', 'Hallo Welt'),
        array(array('one' => 'One item has been added',
                    'other' => '%count elements have been added'),
              array('one' => 'Ein Element wurde hinzugefuegt.',
                    'other' => '%count Elemente wurden hinzugefuegt.')),
        array('Hello %name, how are you today?', 'Hallo %name, wie geht es Dir heute?'));

    foreach ($messages_te_ST as $data) {
      list ($message, $translation) = $data;
      $entry = ORM::factory("incoming_translation");
      $entry->key = Gallery_I18n::get_message_key($message);
      $entry->message = serialize($message);
      $entry->translation = serialize($translation);
      $entry->locale = 'te_ST';
      $entry->revision = null;
      $entry->save();
    }
  }

  public function get_locale_test() {
    $locale = $this->i18n->locale();
    $this->assert_equal("te_ST", $locale);
  }

  public function set_locale_test() {
    $this->i18n->locale("de_DE");
    $locale = $this->i18n->locale();
    $this->assert_equal("de_DE", $locale);
  }

  public function translate_simple_test() {
    $result = $this->i18n->translate('Hello world');
    $this->assert_equal('Hallo Welt', $result);
  }

  public function translate_simple_root_fallback_test() {
    $result = $this->i18n->translate('Hello world zzz');
    $this->assert_equal('Hello world zzz', $result);
  }

  public function translate_plural_other_test() {
    $result = $this->i18n->translate(array('one' => 'One item has been added',
                                           'other' => '%count elements have been added'),
                                     array('count' => 5));
    $this->assert_equal('5 Elemente wurden hinzugefuegt.', $result);
  }

  public function translate_plural_one_test() {
    $result = $this->i18n->translate(array('one' => 'One item has been added',
                                           'other' => '%count elements have been added'),
                                     array('count' => 1));
    $this->assert_equal('Ein Element wurde hinzugefuegt.', $result);
  }

  public function translate_interpolate_test() {
    $result = $this->i18n->translate('Hello %name, how are you today?', array('name' => 'John'));
    $this->assert_equal('Hallo John, wie geht es Dir heute?', $result);
  }

  public function translate_interpolate_missing_value_test() {
    $result = $this->i18n->translate('Hello %name, how are you today?', array('foo' => 'bar'));
    $this->assert_equal('Hallo %name, wie geht es Dir heute?', $result);
  }

  public function translate_plural_zero_test() {
    // te_ST has the same plural rules as en and de.
    // For count 0, plural form "other" should be used.
    $result = $this->i18n->translate(array('one' => 'One item has been added',
                                           'other' => '%count elements have been added'),
                                     array('count' => 0));
    $this->assert_equal('0 Elemente wurden hinzugefuegt.', $result);
  }
}