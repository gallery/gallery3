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
class Locales_Helper_Test extends Gallery_Unit_Test_Case {
  static $installed_locales;
  static $default_locale;

  public function setup() {
    self::$installed_locales = locales::installed();
    self::$default_locale = module::get_var("gallery", "default_locale");
    locales::update_installed(array_keys(locales::available()));
    module::set_var("gallery", "default_locale", "no_NO");
  }

  public function teardown() {
    locales::update_installed(array_keys(self::$installed_locales));
    module::set_var("gallery", "default_locale", self::$default_locale);
  }

  public function locale_from_http_request_test() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("de_DE", $locale);
  }

  public function locale_from_http_request_fallback_test() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("de_DE", $locale);
  }

  public function locale_from_http_request_by_qvalue_test() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de;q=0.8,fr-fr;q=0.9";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("fr_FR", $locale);
  }

  public function locale_from_http_request_default_qvalue_test() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "de-de;q=0.8,it-it,fr-fr;q=0.9";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("it_IT", $locale);
  }

  public function locale_from_http_request_lang_fallback_qvalue_adjustment_test() {
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = ",fr-fr;q=0.4,de-ch;q=0.8";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("de_DE", $locale);
  }

  public function locale_from_http_request_best_match_vs_installed_test() {
    locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,ja_JP;q=0.7,no-fr;q=0.9";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("no_NO", $locale);
  }

  public function locale_from_http_request_best_match_vs_installed_2_test() {
    locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,ja_JP;q=0.5,no-fr;q=0.9";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("no_NO", $locale);
  }

  public function locale_from_http_request_no_match_vs_installed_test() {
    locales::update_installed(array("no_NO", "pt_PT", "ja_JP"));
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en,en-us,de";
    $locale = locales::locale_from_http_request();
    $this->assert_equal(null, $locale);
  }

  public function locale_from_http_request_prefer_inexact_same_language_match_over_exact_other_language_match_test() {
    locales::update_installed(array("de_DE", "ar_AR", "fa_IR", "he_IL", "en_US"));
    // Accept-Language header from Firefox 3.5/Ubuntu
    $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "he,en-us;q=0.9,de-ch;q=0.5,en;q=0.3";
    $locale = locales::locale_from_http_request();
    $this->assert_equal("he_IL", $locale);
  }
}