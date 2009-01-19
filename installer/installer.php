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
  private static $database = null;
  private static $config_errors = false;

  static function command_line() {
    // remove the script name from the arguments
    array_shift($_SERVER["argv"]);

    //$_SERVER["HTTP_USER_AGENT"] = phpversion();
    //date_default_timezone_set('America/Los_Angeles');

    set_error_handler(create_function('$errno, $errstr, $errfile, $errline',
        'throw new ErrorException($errstr, 0, $errno, $errfile, $errline);'));

    // Set exception handler
    set_exception_handler(array("installer", "print_exception"));

    // @todo Log the results of failed call
    if (!installer::environment_check()) {
      self::display_requirements();
      die;
    }

    self::parse_cli_parms($_SERVER["argv"]);

    $config_valid = true;

    try {
      $config_valid = self::check_database_authorization();
    } catch (Exception $e) {
      self::print_exception($e);
      die("Specifed User does not have sufficient authority to install Gallery3\n");
    }

    $config_valid &= self::check_docroot_writable();

    self::display_requirements(!$config_valid);

    if ($config_valid) {
      print self::install();
    }
  }

  static function environment_check() {
    $failed = false;
    $section = array("header" => "Environment Test",
                     "description" => "The following tests have been run to determine if " .
                     "Gallery3 will work in your environment. If any of the tests have " .
                     "failed, consult the documention on http://gallery.menalto.com for " .
                     "more information on how to correct the problem.",
                     "msgs" => array());

    if (version_compare(PHP_VERSION, "5.2", "<")) {
      $section["msgs"]["PHP Version"] = array("error" => true,
       "text" => sprintf("Gallery3 requires PHP 5.2 or newer, current version: %s.", PHP_VERSION));
      $failed = true;
    } else {
      $section["msgs"]["PHP Version"] = array("error" => false,
        "text" => PHP_VERSION);
    }


    if (!(is_dir(SYSPATH) AND is_file(SYSPATH.'core/Bootstrap'.EXT))) {
      $section["msgs"]["Kohana Directory"] = array("error" => true,
        "text" => "The configured Kohana directory does not exist or does not contain the required files.");
    } else {
      $section["msgs"]["Kohana Directory"] = array("error" => false,
        "text" => SYSPATH);
    }

    if (!(is_dir(APPPATH) AND is_file(APPPATH.'config/config'.EXT))) {
      $section["msgs"]["Application Directory"] = array("error" => true,
        "text" => "The configured Gallery3 application directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      $section["msgs"]["Application Directory"] = array("error" => false,
        "text" => APPPATH);
    }

    if (!(is_dir(MODPATH))) {
      $section["msgs"]["Modules Directory"] = array("error" => true,
        "text" => "The configured Gallery3 modules directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      $section["msgs"]["Modules Directory"] = array("error" => false,
        "text" => MODPATH);
    }

    if (!(is_dir(THEMEPATH))) {
      $section["msgs"]["Theme Directory"] = array("error" => true,
        "text" => "The configured Gallery3 themes directory does not exist or does not contain the required files.");
      $failed = true;
    } else {
      $section["msgs"]["Themes Directory"] = array("error" => false,
        "text" => THEMEPATH);
    }

    if (!@preg_match("/^.$/u", utf8_encode("\xF1"))) {
      $section["msgs"]["PCRE UTF-8"] = array("error" => true,
        "text" => "Perl-Compatible Regular Expressions has not been compiled with UTF-8 support.",
        "html" => "<a href=\"http://php.net/pcre\">PCRE</a> has not been compiled with UTF-8 support.");
      $failed = true;
    } else if (!@preg_match("/^\pL$/u", utf8_encode("\xF1"))) {
      $section["msgs"]["PCRE UTF-8"] = array("error" => true,
        "text" => "Perl-Compatible Regular Expressions has not been compiled with Unicode support.",
        "html" => "<a href=\"http://php.net/pcre\">PCRE</a> has not been compiled with Unicode property support.");
      $failed = true;
    } else {
      $section["msgs"]["PCRE UTF-8"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(class_exists("ReflectionClass"))) {
      $section["msgs"]["Reflection Enabled"] = array("error" => true,
        "text" => "PHP relection is either not loaded or not compiled in.",
        "html" => "PHP <a href=\"http://php.net/reflection\">relection<a> is either not loaded or not compiled in.");
      $failed = true;
    } else {
      $section["msgs"]["Reflection Enabled"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(function_exists("filter_list"))) {
      $section["msgs"]["Filters Enabled"] = array("error" => true,
        "text" => "The filter extension is either not loaded or not compiled in.",
        "html" => "The <a href=\"http://php.net/filter\">filter</a> extension is either not loaded or not compiled in.");
      $failed = true;
    } else {
      $section["msgs"]["Filters Enabled"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(extension_loaded("iconv"))) {
      $section["msgs"]["Iconv Loaded"] = array("error" => true,
        "text" => "The iconv extension is not loaded.",
        "html" => "The <a href=\"http://php.net/iconv\">iconv</a> extension is not loaded.");
      $failed = true;
    } else {
      $section["msgs"]["Iconv Enabled"] = array("error" => false,
        "text" => "Pass");
    }

    if (extension_loaded("mbstring") &&
        (ini_get("mbstring.func_overload") & MB_OVERLOAD_STRING)) {
      $section["msgs"]["Mbstring Overloaded"] = array("error" => true,
        "text" => "The mbstring extension is overloading PHP's native string functions.",
        "html" => "The <a href=\"http://php.net/mbstring\">mbstring</a> extension is overloading PHP's native string functions.");
      $failed = true;
    } else {
      $section["msgs"]["MbString Overloaded"] = array("error" => false,
        "text" => "Pass");
    }

    if (!(isset($_SERVER["REQUEST_URI"]) || isset($_SERVER["PHP_SELF"]))) {
      $section["msgs"]["URI Determination"] = array("error" => true,
        "text" => "Neither \$_SERVER['REQUEST_URI'] or \$_SERVER['PHP_SELF'] is available.",
        "html" => "Neither <code>\$_SERVER['REQUEST_URI']</code> or <code>\$_SERVER['PHP_SELF']<code> is available.");
      $failed = true;
    } else {
      $section["msgs"]["URI Determination"] = array("error" => false,
        "text" => "Pass");
    }

    $short_tags = ini_get("short_open_tag");
    if (empty($short_tags)) {
      $section["msgs"]["Short Tags"] = array("error" => true,
        "text" => "Gallery3 requires that PHP short tags be enabled.",
        "html" => "Gallery3 requires that PHP <a href=\"http://ca2.php.net/manual/en/ini.core.php\">short tags</a> be enabled");
      $failed = true;
    } else {
      $section["msgs"]["Short Tags"] = array("error" => false,
        "text" => "Pass");
    }
    self::$messages[] = $section;

    return !$failed;
  }

  static function display_requirements($errors=false) {
    self::$config_errors = $errors;
    if (PHP_SAPI == 'cli') {
      print self::_render("installer/views/installer.txt");
    } else {
      print self::_render("installer/views/installer.html");
    }
  }

  static function parse_cli_parms($argv) {
    $section = array("header" => "Installation Parameters",
                     "description" => "The following parameters will be used to install and " .
                     "configure your Gallery3 installation.",
                     "msgs" => array());
    $arguments = array();
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
      case "-i":
        $arguments["type"] = $argv[++$i];
        break;
      case "-m":
        $arguments["modules"] = $argv[++$i];
        break;
      }
    }

    $config = array("host" => "localhost", "user" => "root", "password" => "",
                    "modules" => array("core" => 1, "user" => 1), "type" => "mysqli",
                    "dbname" => "gallery3", "prefix" => "");

    if (!empty($arguments["file"])) {
      if (file_exists($arguments["file"])) {
        $save_modules = $config["modules"];
        include $arguments["file"];
        if (!is_array($config["modules"])) {
          $modules = explode(",", $config["modules"]);
          $config["modules"] = array_merge($save_modules, array_fill_keys($modules, 1));
        }
      }
      unset($arguments["file"]);
    }

    if (!empty($arguments["modules"])) {
      $modules = explode(",", $arguments["modules"]);

      $config["modules"] = array_merge($config["modules"], array_fill_keys($modules, 1));
      unset($arguments["modules"]);
    }

    foreach (array_keys($config["modules"]) as $module) {
      unset($config["modules"][$module]);
      $config["modules"][trim($module)] = 1;
    }

    self::$config = array_merge($config, $arguments);

    foreach (self::$config as $key => $value) {
      if ($key == "modules") {
        $value = implode(", ", array_keys($value));
      }
      $section["msgs"][$key] = array("text" => $value, "error" => false);
    }
    self::$messages[] = $section;
  }

  static function check_database_authorization() {
    $section = array("header" => "Database Configuration",
                     "description" => "Gallery3 requires the following database configuration.",
                     "msgs" => array());
    if (!mysql_connect(self::$config["host"], self::$config["user"], self::$config["password"])) {
      throw new Exception(mysql_error());
    }

    /*
     * If we got this far, then the user/password combination is valid and we can now
     * a little more information for the individual that is running the script. We can also
     * connect to the database and ask for more information
     */

    $db_config_valid = true;
    if (!mysql_select_db(self::$config["dbname"]) && !mysql_create_db(self::$config["dbname"])) {
      $db_config_valid = false;
      $section["msgs"]["Database"] = array(
        "text" => "Database '$dbname' is not defined and can't be created",
        "error" => true);
    }

    if (mysql_num_rows(mysql_query("SHOW TABLES FROM " . self::$config["dbname"]))) {
      $db_config_valid = false;
      $section["msgs"]["Database Empty"] = array("text" => "Database '$dbname' is not empty",
                                                 "error" => true);
    }

    self::$messages[] = $section;
    return $db_config_valid;
  }

  static function check_docroot_writable() {
    $section = array("header" => "File System Access",
                     "description" => "The requires the following file system configuration.",
                     "msgs" => array());
    if (is_writable(DOCROOT)) {
      $writable = true;
      $section["msgs"]["Permissions"] =
        array("text" => "The installation directory '" . DOCROOT . "' is writable.",
              "error" => false);
    } else {
      $writable = false;
      $section["msgs"]["Permissions"] =
        array("text" => "The current user is unable to write to '" . DOCROOT . "'.",
              "error" => true);
    }
    self::$messages[] = $section;
    return $writable;
  }

  static function install() {
    ob_start();
    $step = 0;
    $modules[] = array();
    try {
      include(DOCROOT . "installer/data/init_var.php");

      $db_config_file = realpath("var") . "/database.php";
      $data = array("type" => strtolower(self::$config["type"]),
                    "user" => self::$config["user"],
                    "password" => self::$config["password"],
                    "host" => self::$config["host"],
                    "database" => self::$config["dbname"],
                    "prefix" => self::$config["prefix"]);

      $config = self::_render("installer/views/database.php", $data);
      if (file_put_contents($db_config_file, $config) !== false) {
        print "'var/database.php' created\n";
      } else {
        throw new Exception("'var/database.php' was not created");
      }

      $command = "mysql -h{$data['host']} " .
          "-u{$data['user']} -p{$data['password']} {$data['database']} <" .
          "\"installer/data/install.sql\"";
      exec($command, $output, $status);
      if ($status) {
        print implode("\n", $output);
        throw new Exception("Database initialization failed");
      }

      if (file_put_contents("var/installed", "installed")) {
        print "Gallery3 installed\n";
      } else {
        throw new Exception("Unable to write 'var/installed'");
      }
    } catch (Exception $e) {
      self::print_exception($e);
    }
    $return = ob_get_contents();
    ob_clean();
    return $return;
  }

  static function print_exception($exception) {
    // Beautify backtrace
    try {
    $trace = self::_backtrace($exception);
    } catch(Exception $e) {
      print_r($e);
    }

    $type     = get_class($exception);
    $message  = $exception->getMessage();
    $file     = $exception->getFile();
    $line     = $exception->getLine();

    print "$type Occurred: $message \nin {$file}[$line]\n$trace";
    // Turn off error reporting
    error_reporting(0);
  }

  /**
   * Install a module.
   */
  private static function _module_install($module_name) {
    $installer_class = "{$module_name}_installer";
    print "$installer_class install (initial)\n";
    if ($module_name != "core") {
      require_once(DOCROOT . "modules/${module_name}/helpers/{$installer_class}.php");
    } else {
      require_once(DOCROOT . "core/helpers/core_installer.php");
    }

    $core_config = Kohana::config_load("core");
    $kohana_modules = $core_config["modules"];
    $kohana_modules[] = MODPATH . $module_name;
    Kohana::config_set("core.modules", $kohana_modules);


    call_user_func(array($installer_class, "install"));

    //if (method_exists($installer_class, "install")) {
    //  call_user_func_array(array($installer_class, "install"), array());
    //}
    print "Installed module $module_name\n";
  }

  private static function _render($view, $data=null) {
    if ($view == '')
      return;

    // Buffering on
    ob_start();

    try {
      // Views are straight HTML pages with embedded PHP, so importing them
      // this way insures that $this can be accessed as if the user was in
      // the controller, which gives the easiest access to libraries in views
      include realpath($view . EXT);
    } catch (Exception $e) {
      // Display the exception using its internal __toString method
      echo $e;
    }

    // Fetch the output and close the buffer
    return ob_get_clean();
  }

  /**
   * Displays nice backtrace information.
   * @see http://php.net/debug_backtrace
   *
   * @param   array   backtrace generated by an exception or debug_backtrace
   * @return  string
   */
  private static function _backtrace($exception) {
    $trace = $exception->getTrace();
    if ( ! is_array($trace)) {
      return;
    }

    // Final output
    $output = array();
    $cli = PHP_SAPI == "cli";

    $args = array();
    // Remove the first entry of debug_backtrace(), it is the exception_handler call
    if ($exception instanceof ErrorException) {
      $last = array_shift($trace);
      $args = !empty($last["args"]) ? $last["args"] : $args;
    }

    foreach ($trace as $entry) {
      $temp = $cli ? "" : "<li>";

      if (isset($entry["file"])) {
        $format = $cli ? "%s[%s]" : "<tt>%s <strong>[%s]:</strong></tt>";
        $temp .= sprintf($format, preg_replace("!^".preg_quote(DOCROOT)."!", "",
                                               $entry["file"]), $entry["line"]);
      }

      $temp .= $cli ? "\n\t" : "<pre>";

      if (isset($entry["class"])) {
        // Add class and call type
        $temp .= $entry["class"].$entry["type"];
      }

      // Add function
      $temp .= $entry["function"]."(";

      // Add function args
      if (isset($entry["args"]) AND is_array($entry["args"])) {
        // Separator starts as nothing
        $sep = "";

        while ($arg = array_shift($args)) {
          if (is_string($arg) AND is_file($arg)) {
            // Remove docroot from filename
            $arg = preg_replace("!^".preg_quote(DOCROOT)."!", "", $arg);
          }

          $temp .= $sep . ($cli ? print_r($arg, TRUE) : html::specialchars(print_r($arg, TRUE)));

          // Change separator to a comma
          $sep = ", ";
        }
        $args = $entry["args"];
      }

      $temp .= ")" . ($cli ? "\n" : "</pre></li>");

      $output[] = $temp;
    }

    $output = implode("\n", $output);
    return $cli ? $output : "<ul class=\"backtrace\">" . $output . "</ul>";
  }
}