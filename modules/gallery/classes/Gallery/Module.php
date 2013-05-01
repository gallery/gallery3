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
 * This is the API for handling modules.
 *
 * Note: by design, this class does not do any permission checking.
 */
class Gallery_Module {
  public static $active = array();
  public static $modules = array();
  public static $var_cache = null;
  public static $available = array();

  /**
   * Setup some module constants.  Modules are loaded in the following order:
   *   1. Unit test modules (if in TEST_MODE).  These do not have module.info files.
   *   2. First module.  This is "purifier", which *cannot* be overridden.
   *   3. User-selected active modules.
   *   4. Last module.  This is "gallery", which *can* be overridden.
   *   5. Third-party modules.  These do not have module.info files.
   */
  protected static $_unittest_modules = array("gallery_unittest", "unittest");
  protected static $_first_module = "purifier";
  protected static $_last_module = "gallery";
  protected static $_third_party_modules = array(
    "cache", "database", "formo", "image", "orm", "pagination");

  /**
   * Set the version of the corresponding Model_Module.
   * If the module doesn't yet have a DB entry, add one.
   *
   * @param string  $module_name
   * @param integer $version
   */
  static function set_version($module_name, $version) {
    $module = Module::get($module_name);
    if (!$module->loaded()) {
      $module->name = $module_name;
      // Only the pre-defined first and last modules are active by default.
      $module->active = in_array($module_name, array(Module::$_first_module, Module::$_last_module));
    }
    $module->version = $version;
    $module->save();
    Log::instance()->add(Log::DEBUG, "$module_name: version is now $version");
  }

  /**
   * Load the corresponding Model_Module
   * @param string $module_name
   */
  static function get($module_name) {
    if (empty(Module::$modules[$module_name])) {
      return ORM::factory("Module")->where("name", "=", $module_name)->find();
    }
    return Module::$modules[$module_name];
  }

  /**
   * Get the information about a module
   * @returns ArrayObject containing the module information from the module.info file or false if
   *                      not found
   */
  static function info($module_name) {
    $module_list = Module::available();
    return isset($module_list->$module_name) ? $module_list->$module_name : false;
  }

  /**
   * Check to see if a module is installed
   * @param string $module_name
   */
  static function is_installed($module_name) {
    return array_key_exists($module_name, Module::$modules);
  }

  /**
   * Check to see if a module is active
   * @param string $module_name
   */
  static function is_active($module_name) {
    return array_key_exists($module_name, Module::$modules) &&
      Module::$modules[$module_name]->active;
  }

  /**
   * Return the list of available modules, including uninstalled modules.
   */
  static function available() {
    if (empty(Module::$available)) {
      $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
      foreach (glob(MODPATH . "*/module.info") as $file) {
        $module_name = basename(dirname($file));
        $modules->$module_name =
          new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
        $m =& $modules->$module_name;
        $m->installed = Module::is_installed($module_name);
        $m->active = Module::is_active($module_name);
        $m->code_version = $m->version;
        $m->version = Module::get_version($module_name);
        $m->locked = false;

        if ($m->active && $m->version != $m->code_version) {
          SiteStatus::warning(t("Some of your modules are out of date.  <a href=\"%upgrader_url\">Upgrade now!</a>", array("upgrader_url" => URL::abs_site("upgrader"))), "upgrade_now");
        }
      }

      // Lock certain modules
      $identity_module = Module::get_var("gallery", "identity_provider", "user");
      $modules->$identity_module->locked = true;
      $modules->{Module::$_first_module}->locked = true;
      $modules->{Module::$_last_module}->locked = true;

      $modules->uasort(array("module", "module_comparator"));
      Module::$available = $modules;
    }

    return Module::$available;
  }

  /**
   * Natural name sort comparator
   */
  static function module_comparator($a, $b) {
    return strnatcasecmp($a->name, $b->name);
  }

  /**
   * Return a list of all the active modules in order of priority.
   */
  static function active() {
    return Module::$active;
  }

  /**
   * Check that the module can be activated. (i.e. all the prerequistes exist)
   * @param string $module_name
   * @return array an array of warning or error messages to be displayed
   */
  static function can_activate($module_name) {
    Module::_add_to_path($module_name);
    $messages = array();

    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    if (class_exists($installer_class) && method_exists($installer_class, "can_activate")) {
      $messages = call_user_func(array($installer_class, "can_activate"));
    }

    // Remove it from the active path
    Module::_remove_from_path($module_name);
    return $messages;
  }

  /**
   * Allow modules to indicate the impact of deactivating the specified module
   * @param string $module_name
   * @return array an array of warning or error messages to be displayed
   */
  static function can_deactivate($module_name) {
    $data = (object)array("module" => $module_name, "messages" => array());

    Module::event("pre_deactivate", $data);

    return $data->messages;
  }

  /**
   * Install a module.  This will call <module>Installer::install(), which is responsible for
   * creating database tables, setting module variables and calling Module::set_version().
   * Note that after installing, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function install($module_name) {
    Module::_add_to_path($module_name);

    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    if (class_exists($installer_class) && method_exists($installer_class, "install")) {
      call_user_func_array(array($installer_class, "install"), array());
    }
    // Module::set_version() will add the module to the DB if it doesn't already exist.
    Module::set_version($module_name, Module::available()->$module_name->code_version);

    // Set the weight of the new Module, which controls the order in which the modules are
    // loaded. By default, new modules are installed at the end of the priority list.  Since the
    // id field is monotonically increasing, the easiest way to guarantee that is to set the weight
    // the same as the id.  We don't know that until we save it for the first time
    $module = ORM::factory("Module")->where("name", "=", $module_name)->find();
    if ($module->loaded()) {
      $module->weight = $module->id;
      $module->save();
    }
    // Similar to activate(), deactivate(), and upgrade(), calling load_modules() here
    // refreshes Module::$modules, Module::$active, and the Kohana paths as needed.
    Module::load_modules();

    GalleryLog::success(
      "module", t("Installed module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Add a module path to Kohana's list.  This is used to temporarily add a module path
   * to the top of the list during activation, installation, etc.
   */
  protected static function _add_to_path($module_name) {
    $kohana_modules = Kohana::modules();
    Arr::unshift($kohana_modules, $module_name, MODPATH . $module_name);
    Kohana::modules($kohana_modules);
    ORM::load_relationships($module_name);
  }

  /**
   * Remove a module path from Kohana's list.  This is used to remove a module path
   * temporarily added with _add_to_path().
   */
  protected static function _remove_from_path($module_name) {
    $kohana_modules = Kohana::modules();
    unset($kohana_modules[$module_name]);
    Kohana::modules($kohana_modules);
    ORM::load_relationships();  // reset all relationships
  }

  /**
   * Upgrade a module.  This will call <module>Installer::upgrade(), which is responsible for
   * modifying database tables, changing module variables and calling Module::set_version().
   * Note that after upgrading, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function upgrade($module_name) {
    if (!Module::is_active($module_name)) {
      Module::_add_to_path($module_name);
    }

    $version_before = Module::get_version($module_name);
    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    $available = Module::available();
    if (class_exists($installer_class) && method_exists($installer_class, "upgrade")) {
      call_user_func_array(array($installer_class, "upgrade"), array($version_before));
    } else {
      if (isset($available->$module_name->code_version)) {
        Module::set_version($module_name, $available->$module_name->code_version);
      } else {
        throw new Exception("@todo UNKNOWN_MODULE");
      }
    }
    // Similar to activate(), deactivate(), and install(), calling load_modules() here
    // refreshes Module::$modules, Module::$active, and the Kohana paths as needed.
    Module::load_modules();

    $version_after = Module::get_version($module_name);
    if ($version_before != $version_after) {
      GalleryLog::success(
        "module", t("Upgraded module %module_name from %version_before to %version_after",
                    array("module_name" => $module_name,
                          "version_before" => $version_before,
                          "version_after" => $version_after)));
    }

    if ($version_after != $available->$module_name->code_version) {
      throw new Exception("@todo MODULE_FAILED_TO_UPGRADE");
    }
  }

  /**
   * Activate an installed module.  This will call <module>Installer::activate() which should take
   * any steps to make sure that the module is ready for use.  This will also activate any
   * existing graphics rules for this module.
   * @param string $module_name
   */
  static function activate($module_name) {
    Module::_add_to_path($module_name);

    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    if (class_exists($installer_class) && method_exists($installer_class, "activate")) {
      call_user_func_array(array($installer_class, "activate"), array());
    }

    $module = Module::get($module_name);
    if ($module->loaded()) {
      $module->active = true;
      $module->save();
    }
    Module::load_modules();

    Graphics::activate_rules($module_name);

    BlockManager::activate_blocks($module_name);

    GalleryLog::success(
      "module", t("Activated module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Deactivate an installed module.  This will call <module>Installer::deactivate() which should
   * take any cleanup steps to make sure that the module isn't visible in any way.  Note that the
   * module remains available in Kohana's cascading file system until the end of the request!
   * @param string $module_name
   */
  static function deactivate($module_name) {
    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    if (class_exists($installer_class) && method_exists($installer_class, "deactivate")) {
      call_user_func_array(array($installer_class, "deactivate"), array());
    }

    $module = Module::get($module_name);
    if ($module->loaded()) {
      $module->active = false;
      $module->save();
    }
    Module::load_modules();

    Graphics::deactivate_rules($module_name);

    BlockManager::deactivate_blocks($module_name);

    if (Module::info($module_name)) {
      GalleryLog::success(
        "module", t("Deactivated module %module_name", array("module_name" => $module_name)));
    } else {
      GalleryLog::success(
        "module", t("Deactivated missing module %module_name", array("module_name" => $module_name)));
    }
  }

  /**
   * Deactivate modules that are unavailable or missing, yet still active.
   * This happens when a user deletes a module without deactivating it.
   */
  static function deactivate_missing_modules() {
    foreach (Module::$modules as $module_name => $module) {
      if (Module::is_active($module_name) && !Module::info($module_name)) {
        Module::deactivate($module_name);
      }
    }
  }

  /**
   * Uninstall a deactivated module.  This will call <module>Installer::uninstall() which should
   * take whatever steps necessary to make sure that all traces of a module are gone.
   * @param string $module_name
   */
  static function uninstall($module_name) {
    $installer_class = "Hook_" . Inflector::convert_module_to_class_name($module_name) . "Installer";
    if (class_exists($installer_class) && method_exists($installer_class, "uninstall")) {
      call_user_func(array($installer_class, "uninstall"));
    }

    Graphics::remove_rules($module_name);
    $module = Module::get($module_name);
    if ($module->loaded()) {
      $module->delete();
    }
    Module::load_modules();

    // We could delete the module vars here too, but it's nice to leave them around
    // in case the module gets reinstalled.

    GalleryLog::success(
      "module", t("Uninstalled module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Load (or refresh) all installed modules.  This:
   *   - rebuilds Module::$modules with all installed modules
   *   - rebuilds Module::$active with all active modules
   *   - reinitializes Kohana's module list (including unit test and third-party modules)
   *   - causes Kohana to run any init.php files it may find
   *
   * It is called at bootstrap time as well as during module install, uninstall, activate,
   * deactivate, or upgrade events.
   */
  static function load_modules() {
    Module::$modules = array();
    Module::$active = array();

    // In version 32 we introduced a weight column so we can specify the module order
    // If we try to use that blindly, we'll break earlier versions before they can even
    // run the upgrader.
    $modules = Module::get_version("gallery") < 32 ?
      ORM::factory("Module")->find_all() :
      ORM::factory("Module")->order_by("weight")->find_all();

    // Rebuild installed and active module lists
    $first_module = array();
    $last_module = array();
    foreach ($modules as $module) {
      Module::$modules[$module->name] = $module;
      // Skip inactive or missing modules.  Kohana 3 will not let us load a module that's missing.
      // Kohana 2 would, so it was possible to have an active, deleted module in Gallery 3.0.x.
      if (!$module->active || !is_dir(MODPATH . $module->name)) {
        continue;
      }

      if ($module->name == Module::$_first_module) {
        $first_module = array($module->name => $module);
      } else if ($module->name == Module::$_last_module) {
        $last_module = array($module->name => $module);
      } else {
        Module::$active[$module->name] = $module;
      }
    }
    Module::$active = array_merge($first_module, Module::$active, $last_module);

    // Build the complete list of module names, including unit test and third-party modules.
    $module_names = array_merge(
      (TEST_MODE ? Module::$_unittest_modules : array()),
      array_keys(Module::$active),
      Module::$_third_party_modules
    );

    // Format the module names and paths as needed for Kohana, then send it off.
    $kohana_modules = array();
    foreach ($module_names as $module_name) {
      $kohana_modules[$module_name] = MODPATH . $module_name;
    }
    Kohana::modules(array_merge(Theme::$kohana_themes, $kohana_modules));
    ORM::load_relationships();
  }

  /**
   * Run a specific event on all active modules.
   * @param string $name the event name
   * @param mixed  $data data to pass to each event handler
   */
  static function event($name, &$data=null) {
    $args = func_get_args();
    array_shift($args);
    $function = str_replace(".", "_", $name);

    if (method_exists("Hook_GalleryEvent", $function)) {
      switch (count($args)) {
      case 0:
        Hook_GalleryEvent::$function();
        break;
      case 1:
        Hook_GalleryEvent::$function($args[0]);
        break;
      case 2:
        Hook_GalleryEvent::$function($args[0], $args[1]);
        break;
      case 3:
        Hook_GalleryEvent::$function($args[0], $args[1], $args[2]);
        break;
      case 4:      // Context menu events have 4 arguments so lets optimize them
        Hook_GalleryEvent::$function($args[0], $args[1], $args[2], $args[3]);
        break;
      default:
        call_user_func_array(array("Hook_GalleryEvent", $function), $args);
      }
    }

    foreach (Module::$active as $module) {
      if ($module->name == "gallery") {
        continue;
      }
      $class = "Hook_" . Inflector::convert_module_to_class_name($module->name) . "Event";
      if (class_exists($class) && method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }

    // Give the admin theme a chance to respond, if we're in admin mode.
    if (Theme::$is_admin) {
      $class = "Hook_" . Inflector::convert_module_to_class_name(Theme::$admin_theme_name) . "Event";
      if (class_exists($class) && method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }

    // Give the site theme a chance to respond as well.  It gets a chance even in admin mode, as
    // long as the theme has an admin subdir.
    $class = "Hook_" . Inflector::convert_module_to_class_name(Theme::$site_theme_name) . "Event";
    if (class_exists($class) && method_exists($class, $function)) {
      call_user_func_array(array($class, $function), $args);
    }
  }

  /**
   * Get a variable from this module
   * @param string $module_name
   * @param string $name
   * @param string $default_value
   * @return the value
   */
  static function get_var($module_name, $name, $default_value=null) {
    // We cache vars so we can load them all at once for performance.
    if (empty(Module::$var_cache)) {
      Module::$var_cache = Cache::instance()->get("var_cache");
      if (empty(Module::$var_cache)) {
        // Cache doesn't exist, create it now.
        Module::$var_cache = new stdClass();
        foreach (DB::select("module_name", "name", "value")
                 ->from("vars")
                 ->order_by("module_name")
                 ->order_by("name")
                 ->as_object()
                 ->execute() as $row) {
          if (!isset(Module::$var_cache->{$row->module_name})) {
            Module::$var_cache->{$row->module_name} = new stdClass();
          }
          Module::$var_cache->{$row->module_name}->{$row->name} = $row->value;
        }
        Cache::instance()->set_with_tags("var_cache", Module::$var_cache, null, array("vars"));
      }
    }

    if (isset(Module::$var_cache->$module_name->$name)) {
      return Module::$var_cache->$module_name->$name;
    } else {
      return $default_value;
    }
  }

  /**
   * Store a variable for this module
   * @param string $module_name
   * @param string $name
   * @param string $value
   */
  static function set_var($module_name, $name, $value) {
    $var = ORM::factory("Var")
      ->where("module_name", "=", $module_name)
      ->where("name", "=", $name)
      ->find();
    if (!$var->loaded()) {
      $var->module_name = $module_name;
      $var->name = $name;
    }
    $var->value = $value;
    $var->save();

    Cache::instance()->delete("var_cache");
    Module::$var_cache = null;
 }

  /**
   * Increment the value of a variable for this module
   *
   * Note: Frequently updating counters is very inefficient because it invalidates the cache value
   * which has to be rebuilt every time we make a change.
   *
   * @todo Get rid of this and find an alternate approach for all callers (currently only Akismet)
   *
   * @deprecated
   * @param string $module_name
   * @param string $name
   * @param string $increment (optional, default is 1)
   */
  static function incr_var($module_name, $name, $increment=1) {
    DB::update("vars")
      ->set(array("value" => DB::expr("`value` + $increment")))
      ->where("module_name", "=", $module_name)
      ->where("name", "=", $name)
      ->execute();

    Cache::instance()->delete("var_cache");
    Module::$var_cache = null;
  }

 /**
   * Remove a variable for this module.
   * @param string $module_name
   * @param string $name
   */
  static function clear_var($module_name, $name) {
    DB::delete("vars")
      ->where("module_name", "=", $module_name)
      ->where("name", "=", $name)
      ->execute();

    Cache::instance()->delete("var_cache");
    Module::$var_cache = null;
  }

 /**
   * Remove all variables for this module.
   * @param string $module_name
   */
  static function clear_all_vars($module_name) {
    DB::delete("vars")
      ->where("module_name", "=", $module_name)
      ->execute();

    Cache::instance()->delete("var_cache");
    Module::$var_cache = null;
  }

  /**
   * Return the version of the installed module.
   * @param string $module_name
   */
  static function get_version($module_name) {
    return Module::get($module_name)->version;
  }

  /**
   * Check if obsolete modules are active and, if so, return a warning message.
   * If none are found, return null.
   */
  static function get_obsolete_modules_message() {
    // This is the obsolete modules list.  Any active module that's on the list
    // with version number at or below the one given will be considered obsolete.
    // It is hard-coded here, and may be updated with future releases of Gallery.
    $obsolete_modules = array("videos" => 4, "noffmpeg" => 1, "videodimensions" => 1,
                              "digibug" => 2);

    // Before we check the active modules, deactivate any that are missing.
    Module::deactivate_missing_modules();

    $modules_found = array();
    foreach ($obsolete_modules as $module => $version) {
      if (Module::is_active($module) && (Module::get_version($module) <= $version)) {
        $modules_found[] = $module;
      }
    }

    if ($modules_found) {
      // Need this to be on one super-long line or else the localization scanner may not work.
      // (ref: http://sourceforge.net/apps/trac/gallery/ticket/1321)
      return t("Recent upgrades to Gallery have made the following modules obsolete: %modules. We recommend that you <a href=\"%url_mod\">deactivate</a> the module(s). For more information, please see the <a href=\"%url_doc\">documentation page</a>.",
               array("modules" => implode(", ", $modules_found),
                     "url_mod" => URL::site("admin/modules"),
                     "url_doc" => "http://codex.galleryproject.org/Gallery3:User_guide:Obsolete_modules"));
    }

    return null;
  }
}
