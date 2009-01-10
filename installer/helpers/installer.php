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
class installer {
  private static $messages = array();
  private static $config = array();
  
  public function failed() {
    $failed = false;
    if (version_compare(PHP_VERSION, "5.2", "<")) {
      self::$messages["PHP Version"] = array("error" => true,
       "text" => sprintf("Gallery3 requires PHP 5.2 or newer, current version: %s.", PHP_VERSION));
      $failed = true;
    } else {
      self::$messages["PHP Version"] = array("error" => false,
        "text" => PHP_VERSION);
    }

    
    if (!(is_dir(SYSPATH) AND is_file(SYSPATH.'core/Bootstrap'.EXT))) {     
      self::$messages["Kohana Directory"] = array("error" => true,
        "text" => "The configured Kohana directory does not exist or does not contain the required files.");
    } else {
      self::$messages["Kohana Directory"] = array("error" => false,
        "text" => SYSPATH);
    }
                                                                       
    if (!(is_dir(APPPATH) AND is_file(APPPATH.'config/config'.EXT))) {     
      self::$messages["Application Directory"] = array("error" => true,
        "text" => "The configured Gallery3 application directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      self::$messages["Application Directory"] = array("error" => false,
        "text" => APPPATH);
    }
                                                                            
    if (!(is_dir(MODPATH))) {     
      self::$messages["Modules Directory"] = array("error" => true,
        "text" => "The configured Gallery3 modules directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      self::$messages["Modules Directory"] = array("error" => false,
        "text" => MODPATH);
    }
                                                                            
    if (!(is_dir(THEMEPATH))) {     
      self::$messages["Theme Directory"] = array("error" => true,
        "text" => "The configured Gallery3 themes directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      self::$messages["Themes Directory"] = array("error" => false,
        "text" => THEMEPATH);
    }
                                                                            
    if (!@preg_match("/^.$/u", utf8_encode("\xF1"))) {
      self::$messages["PCRE UTF-8"] = array("error" => true,
        "text" => "Perl-Compatible Regular Expressions has not been compiled with UTF-8 support.",
        "html" => "<a href=\"http://php.net/pcre\">PCRE</a> has not been compiled with UTF-8 support.");
      $failed = true;
    } else if (!@preg_match("/^\pL$/u", utf8_encode("\xF1"))) {             
      self::$messages["PCRE UTF-8"] = array("error" => true,
        "text" => "Perl-Compatible Regular Expressions has not been compiled with Unicode support.",
        "html" => "<a href=\"http://php.net/pcre\">PCRE</a> has not been compiled with Unicode property support.");
      $failed = true;
    } else {
      self::$messages["PCRE UTF-8"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(class_exists("ReflectionClass"))) {
      self::$messages["Reflection Enabled"] = array("error" => true,
        "text" => "PHP relection is either not loaded or not compiled in.",
        "html" => "PHP <a href=\"http://php.net/reflection\">relection<a> is either not loaded or not compiled in.");
      $failed = true;
    } else {
      self::$messages["Reflection Enabled"] = array("error" => false,
        "text" => "Pass");
    }
                                                                            
    if (!(function_exists("filter_list"))) {
      self::$messages["Filters Enabled"] = array("error" => true,
        "text" => "The filter extension is either not loaded or not compiled in.",
        "html" => "The <a href=\"http://php.net/filter\">filter</a> extension is either not loaded or not compiled in.");
      $failed = true;
    } else {
      self::$messages["Filters Enabled"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(extension_loaded("iconv"))) {
      self::$messages["Iconv Loaded"] = array("error" => true,
        "text" => "The iconv extension is not loaded.",
        "html" => "The <a href=\"http://php.net/iconv\">iconv</a> extension is not loaded.");
      $failed = true;
    } else {
      self::$messages["Iconv Enabled"] = array("error" => false,
        "text" => "Pass");
    }

    if (extension_loaded("mbstring") &&
        (ini_get("mbstring.func_overload") & MB_OVERLOAD_STRING)) {
      self::$messages["Mbstring Overloaded"] = array("error" => true,
        "text" => "The mbstring extension is overloading PHP's native string functions.",
        "html" => "The <a href=\"http://php.net/mbstring\">mbstring</a> extension is overloading PHP's native string functions.");
      $failed = true;
    } else {
      self::$messages["MbString Overloaded"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(isset($_SERVER["REQUEST_URI"]) || isset($_SERVER["PHP_SELF"]))) {
      self::$messages["URI Determination"] = array("error" => true,
        "text" => "Neither \$_SERVER['REQUEST_URI'] or \$_SERVER['PHP_SELF'] is available.",
        "html" => "Neither <code>\$_SERVER['REQUEST_URI']</code> or <code>\$_SERVER['PHP_SELF']<code> is available.");
      $failed = true;
    } else {
      self::$messages["URI Determination"] = array("error" => false,
        "text" => "Pass");
    }

    $short_tags = ini_get("short_open_tag");
    if (empty($short_tags)) {
      self::$messages["Short Tags"] = array("error" => true,
        "text" => "Gallery3 requires that PHP short tags be enabled.",
        "html" => "Gallery3 requires that PHP <a href=\"http://ca2.php.net/manual/en/ini.core.php\">short tags</a> be enabled");
      $failed = true;
    } else {
      self::$messages["Short Tags"] = array("error" => false,
        "text" => "Pass");
    }
    return $failed;
  }

  public static function display_requirements() {
    if (PHP_SAPI == 'cli') {
      print self::_render("installer/views/installer.txt");
    } else {
      print self::_render("installer/views/installer.html");
    }
  }

/* -h     Database host          (default: localhost)
 * -u     Database user          (default: root)
 * -p     Database user password (default: )
 * -d     Database name          (default: gallery3)
 * -t     Table prefix           (default: )
 * -f     Response file          (default: not used)
 *        The response file is a php file that contains the following syntax;
 *        $config[key] = value;
 *        Where key is one of "host", "user", "password", "dbname", "prefix".  Values specified
 *        on the command line will override values contained in this file
 */
  public function parse_cli_parms($argv) {
    for ($i=0; $i < count($argv); $i++) {
      switch (strtolower($argv[$i])) {
      case "-d":
        $arguments["dbname"] = $argv[++$i];
        break;
      case "-h":
        $arguments["host"] = $argv[++$i];
        break;
      case "-u":
        $arguments["user"] = $argv[++$i];
        break;
      case "-p":
        $arguments["password"] = $argv[++$i];
        break;
      case "-t":
        $arguments["prefix"] = $argv[++$i];
        break;
      case "-f":
        $arguments["file"] = $argv[++$i];
        break;
      }
    }

    $config = array("host" => "localhost", "user" => "root", "password" => "",
                    "dbname" => "gallery3", "prefix" => "");

    if (!empty($arguments["file"])) {
      if (file_exists($arguments["file"])) {
        include $arguments["file"];
      }
      unset($arguments["file"]);
    }
    self::$config = array_merge($config, $arguments);
                                     
    var_dump(self::$config);
  }
  
  private static function _render($view) {
    if ($view == '')
      return;

    // Buffering on
    ob_start();

    try
      {
        // Views are straight HTML pages with embedded PHP, so importing them
        // this way insures that $this can be accessed as if the user was in
        // the controller, which gives the easiest access to libraries in views
        include realpath($view . EXT);
      }
    catch (Exception $e)
      {
        // Display the exception using its internal __toString method
        echo $e;
      }

    // Fetch the output and close the buffer
    return ob_get_clean();
  }
}