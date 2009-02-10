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
  /**
   * Return the list of available locales.
   */
  static function available() {
    $locales = array();
    list ($supported_languages, $default_Country) = self::_get_language_data();
    foreach ($supported_languages as $language_tag => $country_locales) {
      foreach ($country_locales as $country_tag => $entry) {
        $locales[$language_tag . '_' . $country_tag] =
          $entry['description'];
      }
    }

    return $locales;
  }

  private static function _get_language_data() {
    static $supported_languages = array();
    static $default_country = array();

    // TODO(andy_st): Might want to add a localizable language name as well.
    if (empty($supported_languages)) {
      /* English */
      $supported_languages['en']['US']['description'] = 'English (US)';
      $supported_languages['en']['GB']['description'] = 'English (UK)';
      $default_country['en'] = 'US';

      /* Afrikaans */
      $supported_languages['af']['ZA']['description'] = 'Afrikaans';
      $default_country['af'] = 'ZA';

      /* Catalan */
      $supported_languages['ca']['ES']['description'] = 'Catalan';
      $default_country['ca'] = 'ES';

      /* Czech */
      $supported_languages['cs']['CZ']['description'] = '&#x010c;esky';
      $default_country['cs'] = 'CZ';

      /* Danish */
      $supported_languages['da']['DK']['description'] = 'Dansk';
      $default_country['da'] = 'DK';

      /* German */
      $supported_languages['de']['DE']['description'] = 'Deutsch';
      $default_country['de'] = 'DE';

      /* Spanish */
      $supported_languages['es']['ES']['description'] = 'Espa&#241;ol';
      $supported_languages['es']['MX']['description'] = 'Espa&#241;ol (MX)';
      $supported_languages['es']['AR']['description'] = 'Espa&#241;ol (AR)';
      $default_country['es'] = 'ES';

      /* Estonian */
      $supported_languages['et']['EE']['description'] = 'Eesti';
      $default_country['et'] = 'EE';

      /* Basque */
      $supported_languages['eu']['ES']['description'] = 'Euskara';
      $default_country['eu'] = 'ES';

      /* French */
      $supported_languages['fr']['FR']['description'] = 'Fran&#231;ais';
      $default_country['fr'] = 'FR';

      /* Irish */
      $supported_languages['ga']['IE']['description'] = 'Gaeilge';
      $default_country['ga'] = 'IE';

      /* Greek */
      $supported_languages['el']['GR']['description'] = 'Greek';
      $default_country['el'] = 'GR';

      /* Icelandic */
      $supported_languages['is']['IS']['description'] = 'Icelandic';
      $default_country['is'] = 'IS';

      /* Italian */
      $supported_languages['it']['IT']['description'] = 'Italiano';
      $default_country['it'] = 'IT';

      /* Latvian */
      $supported_languages['lv']['LV']['description'] = 'Latvie&#353;u';
      $default_country['lv'] = 'LV';

      /* Lithuanian */
      $supported_languages['lt']['LT']['description'] = 'Lietuvi&#371;';
      $default_country['lt'] = 'LT';

      /* Hungarian */
      $supported_languages['hu']['HU']['description'] = 'Magyar';
      $default_country['hu'] = 'HU';

      /* Dutch */
      $supported_languages['nl']['NL']['description'] = 'Nederlands';
      $default_country['nl'] = 'NL';

      /* Norwegian */
      $supported_languages['no']['NO']['description'] = 'Norsk bokm&#229;l';
      $default_country['no'] = 'NO';

      /* Polish */
      $supported_languages['pl']['PL']['description'] = 'Polski';
      $default_country['pl'] = 'PL';

      /* Portuguese */
      $supported_languages['pt']['BR']['description'] = 'Portugu&#234;s Brasileiro';
      $supported_languages['pt']['PT']['description'] = 'Portugu&#234;s';
      $default_country['pt'] = 'PT';

      /* Romanian */
      $supported_languages['ro']['RO']['description'] = 'Rom&#226;n&#259;';
      $default_country['ro'] = 'RO';

      /* Slovak */
      $supported_languages['sk']['SK']['description'] = 'Sloven&#269;ina';
      $default_country['sk'] = 'SK';

      /* Slovenian */
      $supported_languages['sl']['SI']['description'] = 'Sloven&#353;&#269;ina';
      $default_country['sl'] = 'SI';

      /* Serbian */
      $supported_languages['sr']['CS']['description'] = 'Srpski';
      $default_country['sr'] = 'CS';

      /* Finnish */
      $supported_languages['fi']['FI']['description'] = 'Suomi';
      $default_country['fi'] = 'FI';

      /* Swedish */
      $supported_languages['sv']['SE']['description'] = 'Svenska';
      $default_country['sv'] = 'SE';

      /* Ukrainian */
      $supported_languages['uk']['UA']['description'] = 'Ð£ÐºÑÐ°ÑÐ½ÑÑÐºÐ°';
      $default_country['uk'] = 'UA';

      /* Vietnamese */
      $supported_languages['vi']['VN']['description'] = 'Ti&#7871;ng Vi&#7879;t';
      $default_country['vi'] = 'VN';

      /* Turkish */
      $supported_languages['tr']['TR']['description'] = 'T&#252;rk&#231;e';
      $default_country['tr'] = 'TR';

      /* Bulgarian */
      $supported_languages['bg']['BG']['description'] =
        '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
      $default_country['bg'] = 'BG';

      /* Russian */
      $supported_languages['ru']['RU']['description'] =
        '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
      $default_country['ru'] = 'RU';

      /* Chinese */
      $supported_languages['zh']['CN']['description'] = '&#31616;&#20307;&#20013;&#25991;';
      $supported_languages['zh']['TW']['description'] = '&#32321;&#39636;&#20013;&#25991;';
      $default_country['zh'] = 'CN';

      /* Korean */
      $supported_languages['ko']['KR']['description'] = '&#xd55c;&#xad6d;&#xb9d0;';
      $default_country['ko'] = 'KR';

      /* Japanese */
      $supported_languages['ja']['JP']['description'] = '&#x65e5;&#x672c;&#x8a9e;';
      $default_country['ja'] = 'JP';

      /* Arabic */
      $supported_languages['ar']['SA']['description'] =
        '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;';
      $supported_languages['ar']['SA']['right-to-left'] = true;
      $default_country['ar'] = 'SA';

      /* Hebrew */
      $supported_languages['he']['IL']['description'] = '&#1506;&#1489;&#1512;&#1497;&#1514;';
      $supported_languages['he']['IL']['right-to-left'] = true;
      $default_country['he'] = 'IL';

      /* Farsi */
      $supported_languages['fa']['IR']['description'] = '&#1601;&#1575;&#1585;&#1587;&#1610;';
      $supported_languages['fa']['IR']['right-to-left'] = true;
      $default_country['fa'] = 'IR';
    }

    return array($supported_languages, $default_country);
  }
}