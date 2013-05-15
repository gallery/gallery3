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
class Locales_Test extends Unittest_TestCase {
  static $installed_locales;
  static $default_locale;

  public function setup() {
    parent::setup();
    static::$installed_locales = Locales::installed();
    static::$default_locale = Module::get_var("gallery", "default_locale");
    Locales::update_installed(array_keys(Locales::available()));
    Module::set_var("gallery", "default_locale", "no_NO");
  }

  public function teardown() {
    Locales::update_installed(array_keys(static::$installed_locales));
    Module::set_var("gallery", "default_locale", static::$default_locale);
    parent::teardown();
  }

  public function test_locale_from_http_request() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("de_DE", $locale);
  }

  public function test_locale_from_http_request_fallback() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("de_DE", $locale);
  }

  public function test_locale_from_http_request_by_qvalue() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de;q=0.8,fr-fr;q=0.9";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("fr_FR", $locale);
  }

  public function test_locale_from_http_request_default_qvalue() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de;q=0.8,it-it,fr-fr;q=0.9";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("it_IT", $locale);
  }

  public function test_locale_from_http_request_lang_fallback_qvalue_adjustment() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = ",fr-fr;q=0.4,de-ch;q=0.8";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("de_DE", $locale);
  }

  public function test_locale_from_http_request_best_match_vs_installed() {
    Locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,ja_JP;q=0.7,no-fr;q=0.9";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("no_NO", $locale);
  }

  public function test_locale_from_http_request_best_match_vs_installed_2() {
    Locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,ja_JP;q=0.5,no-fr;q=0.9";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("no_NO", $locale);
  }

  public function test_locale_from_http_request_no_match_vs_installed() {
    Locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,de";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals(null, $locale);
  }

  public function test_locale_from_http_request_prefer_inexact_same_language_match_over_exact_other_language_match() {
    Locales::update_installed(array("de_DE", "ar_AR", "fa_IR", "he_IL", "en_US"));
    // Accept-Language header from Firefox 3.5/Ubuntu
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "he,en-us;q=0.9,de-ch;q=0.5,en;q=0.3";
    $locale = Locales::locale_from_http_request();
    $this->assertEquals("he_IL", $locale);
  }
}