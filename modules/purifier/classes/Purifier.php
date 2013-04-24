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
 * XSS Cleaner/Purifier helper class.
 *
 * This class has two purposes:
 * - clean $_GET, $_POST, and $_COOKIE superglobals right after Kohana::init()
 * - provide a general-use interface for HTMLPurifier using
 *   Purifier::clean_html(), Purifier::clean_html_array(), and Purifier::add_config_group()
 *
 * This class is called before the rest of Gallery is loaded, and therefore cannot be transparently
 * extended by other modules.  For this reason, it does not have a transparent-extension-enabling
 * file (i.e. no file with "class Purifier_Purifier extends Purifier {}").  However, its settings
 * can be changed after initialization using Purifier::add_config_group().
 */
class Purifier {
  /**
   * Initialize the purifier array and setup our default configuration
   */
  private static $_purifier = array();
  private static $_config = array(
    "default" => array(
      "Cache.SerializerPath" => TMPPATH,
      "Attr.EnableID" => true
    )
  );

  /**
   * Add another config group.  Use this if you want to setup another instance of
   * HTMLPurifier with different settings.  This loads the default settings as a base,
   * then allows them to be overridden.
   *
   * Example: to reclean get and post with a different HTMLPurifier config:
   *   $my_settings = array("MyNamespace.MyDirective" => "MyValue", ...);
   *   Purifier::add_config_group("my_config", $my_settings);
   *   $my_get  = Purifier::clean_html(RAW::$_GET,  "my_config");
   *   $my_post = Purifier::clean_html(RAW::$_POST, "my_config");
   *   Request::current()->query($my_get);
   *   Request::current()->post($my_post);
   *
   * @param   string $config_group  config group name
   * @param   array  $settings      config group HTMLPurifier settings
   */
  public static function add_config_group($config_group, $settings) {
    if ($config_group == "default") {
      throw new Exception("@todo: cannot change default Purifier config group.");
    }
    self::$_config[$config_group] = array_merge(self::$_config["default"], $settings);
  }

  /**
   * Clean an HTML string, array, or object.  This takes care initializing and configuring
   * HTMLPurifier if needed.  For arrays and objects, it recursively cleans each value,
   * but does not clean the key names.
   *
   * Optionally, if a different config group has been added, it can be used instead.
   *
   * @param   mixed   $html (as string, array, or object)
   * @param   string  $config_group (optional)
   * @return  mixed   clean html
   */
  public static function clean_html($html, $config_group="default") {
    // Initialize HTMLPurifier if needed.
    if (!isset(self::$_purifier[$config_group])) {
      self::_init($config_group);
    }

    // Ensure that null/false/0 are returned as such (HTMLPurifier would return "" instead).
    if (!$html) {
      return $html;
    }

    // Recurse if needed.
    if (is_array($html) || is_object($html)) {
      foreach ($html as $key => $value) {
        $html[$key] = self::clean_html($value, $config_group);
      }
      return $html;
    }

    return self::$_purifier[$config_group]->purify($html);
  }

  /**
   * Clean an input array.  This returns an array of two arrays: the "clean" one and the "raw" one.
   * This is intended for use with the superglobals $_GET, $_POST, and $_COOKIE to combat XSS.
   *
   * For cleaning the "raw" array, it:
   * - Cleans all array keys ([^0-9a-zA-Z:_.-] --> [_])
   * - Removes all control characters
   * - Checks charset and, if needed, converts to UTF8 and silently removes incompatible characters.
   * For cleaning the "clean" array, it additionally:
   * - Runs HTMLPurifier on all array values
   *
   * Optionally, if a different config group has been added, it can be used instead.
   *
   * @param   array   $raw_array                       the input array
   * @param   string  $config_group (optional)         the config group ("default" if not given)
   * @return  array   array($clean_array, $raw_array)  the two arrays as described above
   */
  public static function clean_input_array($raw_array, $config_group="default") {
    $clean_array = array();

    foreach ($raw_array as $raw_key => $raw_value) {
      // Check if the key is clean.  If so, don't do anything.
      // This keeps our nominal case (i.e. no unclean keys) fast.
      $clean_key = preg_replace("/[^0-9a-zA-Z:_.-]/", "_", $raw_key);
      if ($raw_key != $clean_key) {
        // Key isn't clean - see if we can safely remap it.  If not, it'll get trashed.
        if (!isset($raw_array[$clean_key])) {
          $raw_array[$clean_key] = $raw_value;
        }
        unset($raw_array[$raw_key]);
      }

      // Check if the value is actually an array.  If so, recurse.
      if (is_array($raw_value)) {
        list ($clean_array[$clean_key], $raw_array[$clean_key]) =
          Purifier::clean_input_array($raw_value, $config_group);
      } else {
        // Run UTF8::clean().  This removes control characters then checks the charset and,
        // if needed, converts to UTF8 and (silently) removes incompatible characters.
        $raw_array[$clean_key] = UTF8::clean($raw_value);
        // Run Purifier::clean_html().  This uses HTMLPurifier to clean our values.
        $clean_array[$clean_key] = Purifier::clean_html($raw_array[$clean_key], $config_group);
      }
    }

    return array($clean_array, $raw_array);
  }

  /**
   * Initialize an instance of HTMLPurifier and load the library if needed.
   */
  private static function _init($config_group) {
    if (empty(self::$_purifier)) {
      // To further reinforce the fact that the purifier module cannot be overridden,
      // the library path is hard-coded and doesn't use Kohana::find_file().
      require MODPATH . "purifier/vendor/htmlpurifier/HTMLPurifier.standalone.php";
    }

    if (!isset(self::$_config[$config_group])) {
      // Specified config group doesn't exist - throw an exception
      throw new Exception("@todo: invalid Purifier config group - see Purifier::add_config_group()");
    }

    $config = HTMLPurifier_Config::createDefault();
    $config->loadArray(self::$_config[$config_group]);
    self::$_purifier[$config_group] = new HTMLPurifier($config);
  }
}
