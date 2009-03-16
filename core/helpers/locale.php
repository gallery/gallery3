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

/**
 * This is the API for handling locales.
 */
class locale_Core {
  private static $locales;

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
    $default = module::get_var("core", "default_locale");
    $codes = explode("|", module::get_var("core", "installed_locales", $default));
    foreach ($codes as $code) {
      if (isset($available->$code)) {
        $installed[$code] = $available[$code];
      }
    }
    return $installed;
  }

  static function update_installed($locales) {
    // Ensure that the default is included...
    $default = module::get_var("core", "default_locale");
    $locales = array_merge($locales, array($default));

    module::set_var("core", "installed_locales", join("|", $locales));
  }

  // TODO(andy_st): Might want to add a localizable language name as well.
  private static function _init_language_data() {
    $l["af_ZA"] = "Afrikaans";                            // Afrikaans
    $l["ar_SA"] = "&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;"; // Arabic
    $l["bg_BG"] = "&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;"; // Bulgarian
    $l["ca_ES"] = "Catalan";                              // Catalan
    $l["cs_CZ"] = "&#x010c;esky";                         // Czech
    $l["da_DK"] = "Dansk";                                // Danish
    $l["de_DE"] = "Deutsch";                              // German
    $l["el_GR"] = "Greek";                                // Greek
    $l["en_GB"] = "English (UK)";                         // English (UK)
    $l["en_US"] = "English (US)";                         // English (US)
    $l["es_AR"] = "Espa&#241;ol (AR)";                    // Spanish (AR)
    $l["es_ES"] = "Espa&#241;ol";                         // Spanish (ES)
    $l["es_MX"] = "Espa&#241;ol (MX)";                    // Spanish (MX)
    $l["et_EE"] = "Eesti";                                // Estonian
    $l["eu_ES"] = "Euskara";                              // Basque
    $l["fa_IR"] = "&#1601;&#1575;&#1585;&#1587;&#1610;";  // Farsi
    $l["fi_FI"] = "Suomi";                                // Finnish
    $l["fr_FR"] = "Fran&#231;ais";                        // French
    $l["ga_IE"] = "Gaeilge";                              // Irish
    $l["he_IL"] = "&#1506;&#1489;&#1512;&#1497;&#1514;";  // Hebrew
    $l["hu_HU"] = "Magyar";                               // Hungarian
    $l["is_IS"] = "Icelandic";                            // Icelandic
    $l["it_IT"] = "Italiano";                             // Italian
    $l["ja_JP"] = "&#x65e5;&#x672c;&#x8a9e;";             // Japanese
    $l["ko_KR"] = "&#xd55c;&#xad6d;&#xb9d0;";             // Korean
    $l["lt_LT"] = "Lietuvi&#371;";                        // Lithuanian
    $l["lv_LV"] = "Latvie&#353;u";                        // Latvian
    $l["nl_NL"] = "Nederlands";                           // Dutch
    $l["no_NO"] = "Norsk bokm&#229;l";                    // Norwegian
    $l["pl_PL"] = "Polski";                               // Polish
    $l["pt_BR"] = "Portugu&#234;s Brasileiro";            // Portuguese (BR)
    $l["pt_PT"] = "Portugu&#234;s";                       // Portuguese (PT)
    $l["ro_RO"] = "Rom&#226;n&#259;";                     // Romanian
    $l["ru_RU"] = "&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;"; // Russian
    $l["sk_SK"] = "Sloven&#269;ina";                      // Slovak
    $l["sl_SI"] = "Sloven&#353;&#269;ina";                // Slovenian
    $l["sr_CS"] = "Srpski";                               // Serbian
    $l["sv_SE"] = "Svenska";                              // Swedish
    $l["tr_TR"] = "T&#252;rk&#231;e";                     // Turkish
    $l["uk_UA"] = "Ð£ÐºÑÐ°ÑÐ½ÑÑÐºÐ°";     // Ukrainian
    $l["vi_VN"] = "Ti&#7871;ng Vi&#7879;t";               // Vietnamese
    $l["zh_CN"] = "&#31616;&#20307;&#20013;&#25991;";     // Chinese (CN)
    $l["zh_TW"] = "&#32321;&#39636;&#20013;&#25991;";     // Chinese (TW)
    asort($l, SORT_LOCALE_STRING);
    self::$locales = $l;
  }

  static function display_name($locale=null) {
    if (empty(self::$locales)) {
      self::_init_language_data();
    }
    $locale or $locale = I18n::instance()->locale();

    return self::$locales["$locale"];
  }

  static function is_rtl($locale) {
    return in_array($locale, array("he_IL", "fa_IR", "ar_SA"));
  }
}