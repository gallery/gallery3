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

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH . "classes/Kohana/Core.php";

if (is_file(APPPATH . "classes/Kohana.php")) {
  // Application extends the core
  require APPPATH . "classes/Kohana.php";
} else {
  // Load empty core extension
  require SYSPATH . "classes/Kohana.php";
}

// Kohana default bootstrap normally sets the default timezone and locale
// here, but we take care of that in the gallery module.

// Enable the Kohana auto-loader.
//
// @link http://kohanaframework.org/guide/using.autoloading
// @link http://www.php.net/manual/function.spl-autoload-register
spl_autoload_register(array("Kohana", "auto_load"));

// Enable the Kohana auto-loader for unserialization.
//
// @link http://www.php.net/manual/function.spl-autoload-call
// @link http://www.php.net/manual/var.configuration#unserialize-callback-func
ini_set("unserialize_callback_func", "spl_autoload_call");

// -- Configuration and initialization -----------------------------------------

// Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
//
// Note: If you supply an invalid environment name, a PHP warning will be thrown
// saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
if (isset($_SERVER["KOHANA_ENV"])) {
  Kohana::$environment = constant("Kohana::" . strtoupper($_SERVER["KOHANA_ENV"]));
}

// Initialize Kohana, setting the default options.
//
// The following options are available:
//
// - string   base_url    path, and optionally domain, of your application   NULL
// - string   index_file  name of your index file, usually "index.php"       index.php
// - string   charset     internal character set used for input and output   utf-8
// - string   cache_dir   set the internal cache directory                   APPPATH/cache
// - integer  cache_life  lifetime, in seconds, of items cached              60
// - boolean  errors      enable or disable error handling                   TRUE
// - boolean  profile     enable or disable internal profiling               TRUE
// - boolean  caching     enable or disable internal caching                 FALSE
// - boolean  expose      set the X-Powered-By header                        FALSE
Kohana::init(
  array(
    // Base path of the web site. If this includes a domain, eg: localhost/kohana/
    // then a full URL will be used, eg: http://localhost/kohana/.
    //
    // If you'd like to force a site protocol (e.g. https), include it in the base_url.
    //
    // Here we do our best to autodetect the base path to Gallery.  If your url is something like:
    //   http://example.com/gallery3/index.php/album73/photo5.jpg?param=value
    //
    // We want the base_url to be:
    //   /gallery3
    //
    // In the above example, $_SERVER["SCRIPT_NAME"] contains "/gallery3/index.php" so
    // dirname($_SERVER["SCRIPT_NAME"]) is what we need.  Except some low end hosts (namely 1and1.com)
    // break SCRIPT_NAME and it contains the extra path info, so in the above example it'd be:
    //   /gallery3/index.php/album73/photo5.jpg
    //
    // So dirname doesn't work.  So we do a tricky workaround where we look up the SCRIPT_FILENAME (in
    // this case it'd be "index.php" and we delete from that part onwards.  If you work at 1and1 and
    // you're reading this, please fix this bug!
    //
    // Rawurlencode each of the elements to avoid breaking the page layout.
    "base_url" => implode(
      "/", array_map(
        "rawurlencode", explode(
          "/",
          substr($_SERVER["SCRIPT_NAME"], 0,
                 strpos($_SERVER["SCRIPT_NAME"], basename($_SERVER["SCRIPT_FILENAME"])))))),

    "index_file" => "index.php",
    "charset" => "utf-8",
    "cache_dir" => VARPATH . "cache",
    "cache_life" => 60,
    "errors" => true,
    "profiling" => true,
    "caching" => false,
    "expose" => false
));

// Attach the file write to logging. Multiple writers are supported.
// The second parameter is the log threshold, which uses the standard
// PHP constants (see http://php.net/manual/en/function.syslog.php).
//
Kohana::$log->attach(new Log_File(VARPATH . "logs"), LOG_NOTICE);

// Attach a file reader to config. Multiple readers are supported.
Kohana::$config->attach(new Config_File);

// Enable some core modules that are needed for the bootstrap.  We'll load the complete set later.
// Modules are referenced by a relative or absolute path.  Note that none of these modules
// have init.php files, so nothing should interfere with XSS cleaning happening first.
Kohana::modules(array(
  "purifier"    => MODPATH . "purifier",
  "gallery"     => MODPATH . "gallery",
  "cache"       => MODPATH . "cache",
  "orm"         => MODPATH . "orm",
  "database"    => MODPATH . "database"
));

// Set the default driver for caching.  Gallery_Cache_Database is the implementation
// that we provide.
Cache::$default = "database";

// Initialize I18n support.  We have to do this after we add the gallery module to the module list
// because we want the gallery I18n, not the Kohana one.
I18n::lang("en-us");
I18n::instance();

// Protect against XSS.  This cleans $_GET, $_POST, and $_COOKIE and stores their raw values in
// RAW::$_GET, RAW::$_POST, and RAW::$_COOKIE, respectively.  It also runs UTF8::clean() on
// $_SERVER to remove control characters and convert to UTF8 if needed.
//
// This is run after Kohana's init (which calls Kohana::sanitize()).  For more details,
// see Purifier::clean_input_array().
class RAW {
  public static $_GET;
  public static $_POST;
  public static $_COOKIE;
}

list ($_GET,    RAW::$_GET)    = Purifier::clean_input_array($_GET);
list ($_POST,   RAW::$_POST)   = Purifier::clean_input_array($_POST);
list ($_COOKIE, RAW::$_COOKIE) = Purifier::clean_input_array($_COOKIE);

if (isset($_SERVER["SERVER_NAME"])) {
  // HTTP_HOST comes from the client and is untrustworthy.
  // Clear it here to force the use of SERVER_NAME instead.
  unset($_SERVER["HTTP_HOST"]);
}
$_SERVER = UTF8::clean($_SERVER);

// If var/database.php doesn't exist, then we assume that the Gallery is not properly installed
// and send users to the installer.
if (!file_exists(VARPATH . "database.php")) {
  URL::redirect(URL::abs_file("installer"));
}

// Simple and cheap test to make sure that the database config is ok.  Do this before we do
// anything else database related.
try {
  Database::instance()->connect();
} catch (Database_Exception $e) {
  print "Database configuration error.  Please check var/database.php";
  exit;
}

// Override the cookie and user agent if they're provided in the request
isset($_POST["g3sid"]) && $_COOKIE["g3sid"] = $_POST["g3sid"];
isset($_GET["g3sid"]) && $_COOKIE["g3sid"] = $_GET["g3sid"];
isset($_POST["user_agent"]) && $_SERVER["HTTP_USER_AGENT"] = $_POST["user_agent"];
isset($_GET["user_agent"]) && $_SERVER["HTTP_USER_AGENT"] = $_GET["user_agent"];

// Setup our file upload configuration.
Upload::$remove_spaces = false;
Upload::$default_directory = VARPATH . "uploads";

// Setup our cookie configuration.
// An empty $domain should restrict cookie access to the current domain (and, for some browsers,
// its subdomains).  Change this only if you want to keep the same cookie across multiple domains.
Cookie::$domain = "";
Cookie::$httponly = true;
Cookie::$secure = !empty($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] === "on");

// Pick a salt for our cookies.
// @todo: should this be something different for each system?  Perhaps something tied
// to the domain?
Cookie::$salt = "g3";

// Enable the complete set of all active modules.  This will trigger each module to load its own
// init.php file which can, among other things, load its own routes which can override those below.
Module::load_modules();

// Set our five routes.  This will match all valid Gallery URLs (including the empty root URL).
//
// Since there are the only two controller directories we use (root and admin), we can remove all
// other underscores.  In Route::matches(), filters are called *after* ucwords, so
// "admin/advanced_settings" maps to "Controller_Admin_AdvancedSettings" and "file_proxy" maps to
// "Controller_FileProxy".
Route::set("admin_forms", "form/<type>/<directory>/<controller>(/<args>)",
           array("type" => "(edit|add)", "directory" => "admin", "args" => "[^.,;?\\n]++"))
  ->filter(function($route, $params, $request) {
      $params["controller"] = str_replace("_", "", $params["controller"]);
      $params["action"] = "form_" . $params["type"];
      $params["is_admin"] = true;
      return $params;
    });

Route::set("site_forms", "form/<type>/<controller>(/<args>)",
           array("type" => "(edit|add)", "args" => "[^.,;?\\n]++"))
  ->filter(function($route, $params, $request) {
      $params["controller"] = str_replace("_", "", $params["controller"]);
      $params["action"] = "form_" . $params["type"];
      return $params;
    });

Route::set("admin", "<directory>(/<controller>(/<action>(/<args>)))",
           array("directory" => "admin", "args" => "[^.,;?\\n]++"))
  ->filter(function($route, $params, $request) {
      $params["controller"] = str_replace("_", "", $params["controller"]);
      $params["is_admin"] = true;
      return $params;
    })
  ->defaults(array(
      "controller" => "dashboard",
      "action" => "index"
    ));

Route::set("site", "<controller>(/<action>(/<args>))",
           array("args" => "[^.,;?\\n]++"))
  ->filter(function($route, $params, $request) {
      if (substr($params["controller"], 0, 6) == "Admin_") {
        // Admin controllers are not available, except via /admin
        return false;
      }
      $params["controller"] = str_replace("_", "", $params["controller"]);
      return $params;
    })
  ->defaults(array(
      "action" => "index"
    ));

Route::set("item", "(<item_url>)",
           array("item" => "[^A-Za-z0-9-_/]++")) // Ref: Model_Item::valid_slug, Route::REGEX_SEGMENT
  ->filter(function($route, $params, $request) {
      // Note: at this point, item_url has matched against the regex above, so it's XSS-free.
      if (empty($params["item_url"])) {
        $item = Item::root();
      } else {
        $item = Item::find_by_relative_url($params["item_url"]);
        if (!$item->loaded()) {
          // Nothing found - abort match.
          return false;
        }
      }
      $params["controller"] = ucfirst($item->type) . "s";
      $params["action"] = "show";
      $params["item"] = $item;
      return $params;
    });

// Initialize our session support
Session::instance();

register_shutdown_function(array("Gallery", "shutdown"));
