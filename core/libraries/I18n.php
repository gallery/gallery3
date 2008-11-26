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
 * @todo Add caching: e.g. keep all translation data in memory during the request.
 *       Remember the locale fallback, cache by locale
 * @todo Might compile l10n files such that no fallbacks have to be performed.
 * @todo Might keep all l10n data in the database instead of php files.
 */
class I18n_Core {
  private $_config = array();
  private $_data = array();
  
  private static $_instance;
  
  public $missing_placeholder_strategy;
  
  private function __construct($config) {
    $this->_config = $config;
  }
  
  public static function instance($config=null) {
    if (self::$_instance == NULL || isset($config)) {
      $config = isset($config) ? $config : Kohana::config('locale');
      self::$_instance = new I18n_Core($config);
      self::$_instance->missing_placeholder_strategy = new Ignore_Missing_Placeholder();
    }

    return self::$_instance;
  }

  public function translate($message, $options=array() /** @todo , $hint=null */) {
    $locale = empty($options['locale']) ? $this->_config['default_locale'] : $options['locale'];
    $count = empty($options['count']) ? null : $options['count'];
    $values = $options;
    unset($values['locale']);

    $entry = $this->lookup($locale, $message);
    
    if (empty($entry)) {
      $entry = $this->default_entry($message);
    }

    $entry = $this->pluralize($locale, $entry, $count);
    
    $entry = $this->interpolate($locale, $entry, $values);
    
    return $entry;
  }

  private function get_fallbacks_for_locale($locale) {
    $fallbacks = array();
    $fallbacks[$locale] = true;
    /** @todo add proper / robust locale string handling */
    /** @todo add smart locale fallback handling, e.g. en_US -> en -> en_* -> root */
    $locale_parts = explode('_', $locale);
    if (count($locale_parts) == 2) {
      $fallbacks[$locale_parts[0]] = true;
    }
    $fallbacks[$this->_config['default_locale']] = true;
    
    return array_keys($fallbacks);
  }
  
  private function lookup($locale, $message) {
    $entry = null;
    $locales = $this->get_fallbacks_for_locale($locale);
    // If message is an array (plural forms), use the first form as message id.
    // TODO: Might rather use hash of message as msgid.
    $key = is_array($message) ? array_shift($message) : $message;

    while (!empty($locales) && $entry == null) {
      $locale = array_shift($locales);

      if ($this->has_l10n_for_locale($locale)) {
        $entry = $this->get_entry_from_locale_data($locale, $key);
      }
    }    
    
    return $entry;
  }
  
  private function default_entry($message) {
    return $message;
  }
  
  private function get_entry_from_locale_data($locale, $key) {
    if (!isset($this->_data[$locale])) {
      $this->load_l10n_data_for_locale($locale);
    }
    
    if (isset($this->_data[$locale][$key])) {
      return $this->_data[$locale][$key];
    }
    
    return null;
  }
  
  private function load_l10n_data_for_locale($locale) {
    $data = array();
    include($this->_config['locale_dir'] . $locale . '.php');

    $this->_data[$locale] = $data;
  }
  
  private function interpolate($locale, $string, $values) {
    // TODO: Benchmark whether {{stuff}} type syntax is prohibitively slow compared to sprintf()/
    // TODO: Benchmark whether str_replace() is much faster (no handling of escape syntax)
    // TODO: Benchmark whether nested vs. outer function is significantly slower.
    $callback = new I18n_Placeholder_Replacer($values, $locale, $string);
    // TODO: Benchmark with pattern string as class constant
    $string = preg_replace_callback("/(\\\\)?\{\{([^\}]+)\}\}/S", array($callback, 'replace'), $string);
    
    return $string;
  }
  
  private function pluralize($locale, $entry, $count) {
    if ($count == NULL || !is_array($entry)) {
      return $entry;
    }
    $plural_key = self::get_plural_key($locale, $count);
    
    if (!isset($entry[$plural_key])) {
      // Fallback to the default plural form.
      $plural_key = 'other';
    }
    
    if (isset($entry[$plural_key])) {
      return $entry[$plural_key];
    } else {
      // Fallback to just any plural form.
      list ($plural_key, $string) = each($entry);
      return $string;
    }
  }
  
  private function has_l10n_for_locale($locale) {
    return file_exists($this->_config['locale_dir'] . $locale . '.php');
  }
  
  private static function get_plural_key($locale, $count) {
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
        return 'other';

      case 'ar':
        if ($count == 0) {
          return 'zero';
        } else if ($count == 1) {
          return 'one';
        } else if ($count == 2) {
          return 'two';
        } else if (is_int($count) && ($i = $count % 100) >= 3 && $i <= 10) {
          return 'few';
        } else if (is_int($count) && ($i = $count % 100) >= 11 && $i <= 99) {
          return 'many';
        } else {
          return 'other';
        }

      case 'pt':
      case 'am':
      case 'bh':
      case 'fil':
      case 'tl':
      case 'guw':
      case 'hi':
      case 'ln':
      case 'mg':
      case 'nso':
      case 'ti':
      case 'wa':
        if ($count == 0 || $count == 1) {
          return 'one';
        } else {
          return 'other';
        }

      case 'fr':
        if ($count >= 0 and $count < 2) {
          return 'one';
        } else {
          return 'other';
        }

      case 'lv':
        if ($count == 0) {
          return 'zero';
        } else if ($count % 10 == 1 && $count % 100 != 11) {
          return 'one';
        } else {
          return 'other';
        }

      case 'ga':
      case 'se':
      case 'sma':
      case 'smi':
      case 'smj':
      case 'smn':
      case 'sms':
        if ($count == 1) {
          return 'one';
        } else if ($count == 2) {
          return 'two';
        } else {
          return 'other';
        }

      case 'ro':
      case 'mo':
        if ($count == 1) {
          return 'one';
        } else if (is_int($count) && $count == 0 && ($i = $count % 100) >= 1 && $i <= 19) {
          return 'few';
        } else {
          return 'other';
        }

      case 'lt':
        if (is_int($count) && $count % 10 == 1 && $count % 100 != 11) {
          return 'one';
        } else if (is_int($count) && ($i = $count % 10) >= 2 && $i <= 9 && ($i = $count % 100) < 11 && $i > 19) {
          return 'few';
        } else {
          return 'other';
        }

      case 'hr':
      case 'ru':
      case 'sr':
      case 'uk':
      case 'be':
      case 'bs':
      case 'sh':
        if (is_int($count) && $count % 10 == 1 && $count % 100 != 11) {
          return 'one';
        } else if (is_int($count) && ($i = $count % 10) >= 2 && $i <= 4 && ($i = $count % 100) < 12 && $i > 14) {
          return 'few';
        } else if (is_int($count) && ($count % 10 == 0 || (($i = $count % 10) >= 5 && $i <= 9) || (($i = $count % 100) >= 11 && $i <= 14))) {
          return 'many';
        } else {
          return 'other';
        }

      case 'cs':
      case 'sk':
        if ($count == 1) {
          return 'one';
        } else if (is_int($count) && $count >= 2 && $count <= 4) {
          return 'few';
        } else {
          return 'other';
        }      

      case 'pl':
        if ($count == 1) {
          return 'one';
        } else if (is_int($count) && ($i = $count % 10) >= 2 && $i <= 4 &&
                   ($i = $count % 100) < 12 && $i > 14 && ($i = $count % 100) < 22 && $i > 24) {
          return 'few';
        } else {
          return 'other';
        } 

      case 'sl':
        if ($count % 100 == 1) {
          return 'one';
        } else if ($count % 100 == 2) {
          return 'two';
        } else if (is_int($count) && ($i = $count % 100) >= 3 && $i <= 4) {
          return 'few';
        } else {
          return 'other';
        }      

      case 'mt':
        if ($count == 1) {
          return 'one';
        } else if ($count == 0 || is_int($count) && ($i = $count % 100) >= 2 && $i <= 10) {
          return 'few';
        } else if (is_int($count) && ($i = $count % 100) >= 11 && $i <= 19) {
          return 'many';
        } else {
          return 'other';
        }      

      case 'mk':
        if ($count % 10 == 1) {
          return 'one';
        } else {
          return 'other';
        }      

      case 'cy':
        if ($count == 1) {
          return 'one';
        } else if ($count == 2) {
          return 'two';
        } else if ($count == 8 || $count == 11) {
          return 'many';
        } else {
          return 'other';
        }

      default: // en, de, etc.
        return $count == 1 ? 'one' : 'other';
    }
  }
}

class I18n_Placeholder_Replacer {
  private $_values;
  private $_locale;
  private $_string;

  public function __construct($values, $locale, $string) {
    $this->_values = $values;
    $this->_locale = $locale;
    $this->_string = $string;
  }

  function replace($matches) {
    list ($full_match, $escaped, $placeholder) = $matches;

    if ($escaped) {
      return $full_match;
    } else if (!isset($this->_values[$placeholder])) {
      return I18n::instance()->missing_placeholder_strategy
          ->replace($this->_locale, $this->_string, $this->_values, $placeholder, $full_match);
    } else {
      return $this->_values[$placeholder];
    }
  }
}

interface Missing_Placeholder_Strategy {
  /**
   * Handle the case where a localization requests a placeholder which is not provided in the translate() call.
   * @param $locale The locale for this localization.
   * @param String $string The complete message string.
   * @param array $values All available replacement key value pairs.
   * @param String $placeholder The placeholder for which there is no replacement value, e.g. "name"
   * @param String $full_match The placeholder including its surrounding placeholder syntax, e.g. "{{name}}"
   * @return String The replacement for the placeholder.
   */
  public function replace($locale, $string, $values, $placeholder, $full_match);
}

class Ignore_Missing_Placeholder implements Missing_Placeholder_Strategy {
  function replace($locale, $string, $values, $placeholder, $full_match) {
    return $full_match;
  }
}