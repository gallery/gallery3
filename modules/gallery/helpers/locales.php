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

/**
 * This is the API for handling locales.
 */
class locales_Core {
  private static $locales;
  private static $language_subtag_to_locale;

  /**
   * Return the list of available locales.
   */
  static function available() {
    if (empty(self::$locales)) {
      self::_init_language_data();
    }

    return self::$locales;
  }

  static function installed() {
    $available = self::available();
    $default = module::get_var("gallery", "default_locale");
    $codes = explode("|", module::get_var("gallery", "installed_locales", $default));
    foreach ($codes as $code) {
      if (isset($available[$code])) {
        $installed[$code] = $available[$code];
      }
    }
    return $installed;
  }

  static function update_installed($locales) {
    // Ensure that the default is included...
    $default = module::get_var("gallery", "default_locale");
    $locales = in_array($default, $locales)
      ? $locales
      : array_merge($locales, array($default));

    module::set_var("gallery", "installed_locales", join("|", $locales));

    // Clear the cache
    self::$locales = null;
  }

  // @todo Might want to add a localizable language name as well.
  // ref: http://cldr.unicode.org/
  // ref: http://cldr.unicode.org/index/cldr-spec/picking-the-right-language-code
  // ref: http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/likely_subtags.html
  private static function _init_language_data() {
    $l["af_ZA"] = "Afrikaans";                // Afrikaans
    $l["ar_SA"] = "العربية";                   // Arabic
    $l["be_BY"] = "Беларускі";           // Belarusian
    $l["bg_BG"] = "български";           // Bulgarian
    $l["bn_BD"] = "বাংলা";               // Bengali
    $l["ca_ES"] = "Catalan";                  // Catalan
    $l["cs_CZ"] = "čeština";                  // Czech
    $l["da_DK"] = "Dansk";                    // Danish
    $l["de_DE"] = "Deutsch";                  // German
    $l["el_GR"] = "Greek";                    // Greek
    $l["en_GB"] = "English (UK)";             // English (UK)
    $l["en_US"] = "English (US)";             // English (US)
    $l["es_AR"] = "Español (AR)";             // Spanish (AR)
    $l["es_ES"] = "Español";                  // Spanish (ES)
    $l["es_MX"] = "Español (MX)";             // Spanish (MX)
    $l["et_EE"] = "Eesti";                    // Estonian
    $l["eu_ES"] = "Euskara";                  // Basque
    $l["fa_IR"] = "فارس";                     // Farsi
    $l["fi_FI"] = "Suomi";                    // Finnish
    $l["fo_FO"] = "Føroyskt";                    // Faroese
    $l["fr_FR"] = "Français";                 // French
    $l["ga_IE"] = "Gaeilge";                  // Irish
    $l["he_IL"] = "עברית";                    // Hebrew
    $l["hr_HR"] = "hr̀vātskī";                 // Croatian
    $l["hu_HU"] = "Magyar";                   // Hungarian
    $l["is_IS"] = "Icelandic";                // Icelandic
    $l["it_IT"] = "Italiano";                 // Italian
    $l["ja_JP"] = "日本語";                    // Japanese
    $l["ko_KR"] = "한국어";                    // Korean
    $l["lt_LT"] = "Lietuvių";                 // Lithuanian
    $l["lv_LV"] = "Latviešu";                 // Latvian
    $l["ms_MY"] = "Bahasa Melayu";            // Malay
    $l["mk_MK"] = "Македонски јазик";         // Macedonian
    $l["nl_NL"] = "Nederlands";               // Dutch
    $l["no_NO"] = "Norsk bokmål";             // Norwegian
    $l["pl_PL"] = "Polski";                   // Polish
    $l["pt_BR"] = "Português do Brasil";      // Portuguese (BR)
    $l["pt_PT"] = "Português ibérico";        // Portuguese (PT)
    $l["ro_RO"] = "Română";                   // Romanian
    $l["ru_RU"] = "Русский";              // Russian
    $l["sk_SK"] = "Slovenčina";               // Slovak
    $l["sl_SI"] = "Slovenščina";              // Slovenian
    $l["sr_CS"] = "Srpski";                   // Serbian
    $l["sv_SE"] = "Svenska";                  // Swedish
    $l["th_TH"] = "ภาษาไทย";                     // Thai
    $l["tn_ZA"] = "Setswana";                 // Setswana
    $l["tr_TR"] = "Türkçe";                   // Turkish
    $l["uk_UA"] = "українська";         // Ukrainian
    $l["vi_VN"] = "Tiếng Việt";               // Vietnamese
    $l["zh_CN"] = "简体中文";                  // Chinese (CN)
    $l["zh_TW"] = "繁體中文";                  // Chinese (TW)
    asort($l, SORT_LOCALE_STRING);
    self::$locales = $l;

    // Language subtag to (default) locale mapping
    foreach ($l as $locale => $name) {
      list ($language) = explode("_", $locale . "_");
      // The first one mentioned is the default
      if (!isset($d[$language])) {
        $d[$language] = $locale;
      }
    }
    self::$language_subtag_to_locale = $d;
  }

  static function display_name($locale=null) {
    if (empty(self::$locales)) {
      self::_init_language_data();
    }
    $locale or $locale = Gallery_I18n::instance()->locale();

    return self::$locales[$locale];
  }

  static function is_rtl($locale=null) {
    return Gallery_I18n::instance()->is_rtl($locale);
  }

  /**
   * Returns the best match comparing the HTTP accept-language header
   * with the installed locales.
   * @todo replace this with request::accepts_language() when we upgrade to Kohana 2.4
   */
  static function locale_from_http_request() {
    $http_accept_language = Input::instance()->server("HTTP_ACCEPT_LANGUAGE");
    if ($http_accept_language) {
      // Parse the HTTP header and build a preference list
      // Example value: "de,en-us;q=0.7,en-uk,fr-fr;q=0.2"
      $locale_preferences = array();
      foreach (explode(",", $http_accept_language) as $code) {
        list ($requested_locale, $qvalue) = explode(";", $code . ";");
        $requested_locale = trim($requested_locale);
        $qvalue = trim($qvalue);
        if (preg_match("/^([a-z]{2,3})(?:[_-]([a-zA-Z]{2}))?/", $requested_locale, $matches)) {
          $requested_locale = strtolower($matches[1]);
          if (!empty($matches[2])) {
            $requested_locale .= "_" . strtoupper($matches[2]);
          }
          $requested_locale = trim(str_replace("-", "_", $requested_locale));
          if (!strlen($qvalue)) {
            // If not specified, default to 1.
            $qvalue = 1;
          } else {
            // qvalue is expected to be something like "q=0.7"
            list ($ignored, $qvalue) = explode("=", $qvalue . "==");
            $qvalue = floatval($qvalue);
          }
          // Group by language to boost inexact same-language matches
          list ($language) = explode("_", $requested_locale . "_");
          if (!isset($locale_preferences[$language])) {
            $locale_preferences[$language] = array();
          }
          $locale_preferences[$language][$requested_locale] = $qvalue;
        }
      }

      // Compare and score requested locales with installed ones
      $scored_locales = array();
      foreach ($locale_preferences as $language => $requested_locales) {
        // Inexact match adjustment (same language, different region)
        $fallback_adjustment_factor = 0.95;
        if (count($requested_locales) > 1) {
          // Sort by qvalue, descending
          $qvalues = array_values($requested_locales);
          rsort($qvalues);
          // Ensure inexact match scores worse than 2nd preference in same language.
          $fallback_adjustment_factor *= $qvalues[1];
        }
        foreach ($requested_locales as $requested_locale => $qvalue) {
          list ($matched_locale, $match_score) =
              self::_locale_match_score($requested_locale, $qvalue, $fallback_adjustment_factor);
          if ($matched_locale &&
              (!isset($scored_locales[$matched_locale]) ||
               $match_score > $scored_locales[$matched_locale])) {
            $scored_locales[$matched_locale] = $match_score;
          }
        }
      }

      arsort($scored_locales);

      list ($locale) = each($scored_locales);
      return $locale;
    }

    return null;
  }

  private static function _locale_match_score($requested_locale, $qvalue, $adjustment_factor) {
    $installed = locales::installed();
    if (isset($installed[$requested_locale])) {
      return array($requested_locale, $qvalue);
    }
    list ($language) = explode("_", $requested_locale . "_");
    if (isset(self::$language_subtag_to_locale[$language]) &&
        isset($installed[self::$language_subtag_to_locale[$language]])) {
      $score = $adjustment_factor * $qvalue;
      return array(self::$language_subtag_to_locale[$language], $score);
    }
    return array(null, 0);
  }

  static function set_request_locale() {
    // 1. Check the session specific preference (cookie)
    $locale = locales::cookie_locale();
    // 2. Check the user's preference
    if (!$locale) {
      $locale = identity::active_user()->locale;
    }
    // 3. Check the browser's / OS' preference
    if (!$locale) {
      $locale = locales::locale_from_http_request();
    }
    // If we have any preference, override the site's default locale
    if ($locale) {
      Gallery_I18n::instance()->locale($locale);
    }
  }

  static function cookie_locale() {
    // Can't use Input framework for client side cookies since
    // they're not signed.
    $cookie_data = isset($_COOKIE["g_locale"]) ? $_COOKIE["g_locale"] : null;
    $locale = null;
    if ($cookie_data) {
      if (preg_match("/^([a-z]{2,3}(?:_[A-Z]{2})?)$/", trim($cookie_data), $matches)) {
        $requested_locale = $matches[1];
        $installed_locales = locales::installed();
        if (isset($installed_locales[$requested_locale])) {
          $locale = $requested_locale;
        }
      }
    }
    return $locale;
  }
}
