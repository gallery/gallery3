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

class I18n_Test extends Unit_Test_Case {
  private $i18n;

  public function setup() {
    $config = array(
        'root_locale' => 'en',
        'default_locale' => 'de_DE',
        'locale_dir' => VARPATH . 'locale/');
    $this->i18n = I18n::instance($config);
    
    $locale_file_contents = <<<EOT
<?php defined("SYSPATH") or die("No direct script access.");

\$data = array(
    'Hello world' => 'Hallo Welt',
    'One item has been added' =>
        array('one' => 'Ein Element wurde hinzugefuegt.',
              'other' => '{{count}} Elemente wurden hinzugefuegt.'),
    'Hello {{name}}, how are you today?' => 'Hallo {{name}}, wie geht es Dir heute?'
);
EOT;

    @mkdir(VARPATH . 'locale');
    $fp = file_put_contents(VARPATH . 'locale/de_DE.php', $locale_file_contents);
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
                                     'other' => '{{count}} items have been added.'),
                               array('count' => 5));
    $this->assert_equal('5 Elemente wurden hinzugefuegt.', $result);
  }

  public function translate_plural_one_test() {
    $result = $this->i18n->translate(array('one' => 'One item has been added',
                                     'other' => '{{count}} items have been added.'),
                               array('count' => 1));
    $this->assert_equal('Ein Element wurde hinzugefuegt.', $result);
  }

  public function translate_interpolate_test() {
    $result = $this->i18n->translate('Hello {{name}}, how are you today?', array('name' => 'John'));
    $this->assert_equal('Hallo John, wie geht es Dir heute?', $result);
  }

  public function translate_interpolate_missing_value_test() {
    $result = $this->i18n->translate('Hello {{name}}, how are you today?', array('foo' => 'bar'));
    $this->assert_equal('Hallo {{name}}, wie geht es Dir heute?', $result);
  }
}