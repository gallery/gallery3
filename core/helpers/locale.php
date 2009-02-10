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
  public static function available() {
    $locales = array();
    list ($supportedLanguages, $defaultCountry) = self::getLanguageData();
    foreach ($supportedLanguages as $language_tag => $country_locales) {
      foreach ($country_locales as $country_tag => $entry) {
        $locales[$language_tag . '_' . $country_tag] =
          $entry['description'];
      }
    }

    return $locales;
  }

  private static function getLanguageData() {
    static $supportedLanguages = array();
    static $defaultCountry = array();

    // TODO(andy_st): Might want to add a localizable language name as well.
    if (empty($supportedLanguages)) {
      /* English */
      $supportedLanguages['en']['US']['description'] = 'English (US)';
      $supportedLanguages['en']['GB']['description'] = 'English (UK)';
      $defaultCountry['en'] = 'US';

      /* Afrikaans */
      $supportedLanguages['af']['ZA']['description'] = 'Afrikaans';
      $defaultCountry['af'] = 'ZA';

      /* Catalan */
      $supportedLanguages['ca']['ES']['description'] = 'Catalan';
      $defaultCountry['ca'] = 'ES';

      /* Czech */
      $supportedLanguages['cs']['CZ']['description'] = '&#x010c;esky';
      $defaultCountry['cs'] = 'CZ';
	
      /* Danish */
      $supportedLanguages['da']['DK']['description'] = 'Dansk';
      $defaultCountry['da'] = 'DK';
	
      /* German */
      $supportedLanguages['de']['DE']['description'] = 'Deutsch';
      $defaultCountry['de'] = 'DE';

      /* Spanish */
      $supportedLanguages['es']['ES']['description'] = 'Espa&#241;ol';
      $supportedLanguages['es']['MX']['description'] = 'Espa&#241;ol (MX)';
      $supportedLanguages['es']['AR']['description'] = 'Espa&#241;ol (AR)';
      $defaultCountry['es'] = 'ES';

      /* Estonian */
      $supportedLanguages['et']['EE']['description'] = 'Eesti';
      $defaultCountry['et'] = 'EE';

      /* Basque */
      $supportedLanguages['eu']['ES']['description'] = 'Euskara';
      $defaultCountry['eu'] = 'ES';

      /* French */
      $supportedLanguages['fr']['FR']['description'] = 'Fran&#231;ais';
      $defaultCountry['fr'] = 'FR';
	
      /* Irish */
      $supportedLanguages['ga']['IE']['description'] = 'Gaeilge';
      $defaultCountry['ga'] = 'IE';
	
      /* Greek */
      $supportedLanguages['el']['GR']['description'] = 'Greek';
      $defaultCountry['el'] = 'GR';
	
      /* Icelandic */
      $supportedLanguages['is']['IS']['description'] = 'Icelandic';
      $defaultCountry['is'] = 'IS';


      /* Italian */
      $supportedLanguages['it']['IT']['description'] = 'Italiano';
      $defaultCountry['it'] = 'IT';

      /* Latvian */
      $supportedLanguages['lv']['LV']['description'] = 'Latvie&#353;u';
      $defaultCountry['lv'] = 'LV';

      /* Lithuanian */
      $supportedLanguages['lt']['LT']['description'] = 'Lietuvi&#371;';
      $defaultCountry['lt'] = 'LT';

      /* Hungarian */
      $supportedLanguages['hu']['HU']['description'] = 'Magyar';
      $defaultCountry['hu'] = 'HU';

      /* Dutch */
      $supportedLanguages['nl']['NL']['description'] = 'Nederlands';
      $defaultCountry['nl'] = 'NL';

      /* Norwegian */
      $supportedLanguages['no']['NO']['description'] = 'Norsk bokm&#229;l';
      $defaultCountry['no'] = 'NO';
      
      /* Polish */
      $supportedLanguages['pl']['PL']['description'] = 'Polski';
      $defaultCountry['pl'] = 'PL';
	
      /* Portuguese */
      $supportedLanguages['pt']['BR']['description'] = 'Portugu&#234;s Brasileiro';
      $supportedLanguages['pt']['PT']['description'] = 'Portugu&#234;s';
      $defaultCountry['pt'] = 'PT';

      /* Romanian */
      $supportedLanguages['ro']['RO']['description'] = 'Rom&#226;n&#259;';
      $defaultCountry['ro'] = 'RO';

      /* Slovak */
      $supportedLanguages['sk']['SK']['description'] = 'Sloven&#269;ina';
      $defaultCountry['sk'] = 'SK';

      /* Slovenian */
      $supportedLanguages['sl']['SI']['description'] = 'Sloven&#353;&#269;ina';
      $defaultCountry['sl'] = 'SI';

      /* Serbian */
      $supportedLanguages['sr']['CS']['description'] = 'Srpski';
      $defaultCountry['sr'] = 'CS';

      /* Finnish */
      $supportedLanguages['fi']['FI']['description'] = 'Suomi';
      $defaultCountry['fi'] = 'FI';

      /* Swedish */
      $supportedLanguages['sv']['SE']['description'] = 'Svenska';
      $defaultCountry['sv'] = 'SE';

      /* Ukrainian */
      $supportedLanguages['uk']['UA']['description'] = 'Ð£ÐºÑÐ°ÑÐ½ÑÑÐºÐ°';
      $defaultCountry['uk'] = 'UA';
	
      /* Vietnamese */
      $supportedLanguages['vi']['VN']['description'] = 'Ti&#7871;ng Vi&#7879;t';
      $defaultCountry['vi'] = 'VN';

      /* Turkish */
      $supportedLanguages['tr']['TR']['description'] = 'T&#252;rk&#231;e';
      $defaultCountry['tr'] = 'TR';

      /* Bulgarian */
      $supportedLanguages['bg']['BG']['description'] =
        '&#x0411;&#x044a;&#x043b;&#x0433;&#x0430;&#x0440;&#x0441;&#x043a;&#x0438;';
      $defaultCountry['bg'] = 'BG';

      /* Russian */
      $supportedLanguages['ru']['RU']['description'] =
        '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;';
      $defaultCountry['ru'] = 'RU';

      /* Chinese */
      $supportedLanguages['zh']['CN']['description'] = '&#31616;&#20307;&#20013;&#25991;';
      $supportedLanguages['zh']['TW']['description'] = '&#32321;&#39636;&#20013;&#25991;';
      $defaultCountry['zh'] = 'CN';
      
      /* Korean */
      $supportedLanguages['ko']['KR']['description'] = '&#xd55c;&#xad6d;&#xb9d0;';
      $defaultCountry['ko'] = 'KR';

      /* Japanese */
      $supportedLanguages['ja']['JP']['description'] = '&#x65e5;&#x672c;&#x8a9e;';
      $defaultCountry['ja'] = 'JP';

      /* Arabic */
      $supportedLanguages['ar']['SA']['description'] =
        '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;';
      $supportedLanguages['ar']['SA']['right-to-left'] = true;
      $defaultCountry['ar'] = 'SA';

      /* Hebrew */
      $supportedLanguages['he']['IL']['description'] = '&#1506;&#1489;&#1512;&#1497;&#1514;';
      $supportedLanguages['he']['IL']['right-to-left'] = true;
      $defaultCountry['he'] = 'IL';
	
      /* Farsi */
      $supportedLanguages['fa']['IR']['description'] = '&#1601;&#1575;&#1585;&#1587;&#1610;';
      $supportedLanguages['fa']['IR']['right-to-left'] = true;
      $defaultCountry['fa'] = 'IR';
    }

    return array($supportedLanguages, $defaultCountry);
  }
}
