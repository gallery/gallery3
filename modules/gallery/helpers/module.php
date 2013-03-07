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
class module_Core {
  public static $active = array();
  public static $modules = array();
  public static $var_cache = null;
  public static $available = array();

  /**
   * Set the version of the corresponding Module_Model
   * @param string  $module_name
   * @param integer $version
   */
  static function set_version($module_name, $version) {
    $module = module::get($module_name);
    if (!$module->loaded()) {
      $module->name = $module_name;
      $module->active = $module_name == "gallery";  // only gallery is active by default
    }
    $module->version = $version;
    $module->save();
    Kohana_Log::add("debug", "$module_name: version is now $version");
  }

  /**
   * Load the corresponding Module_Model
   * @param string $module_name
   */
  static function get($module_name) {
    if (empty(self::$modules[$module_name])) {
      return ORM::factory("module")->where("name", "=", $module_name)->find();
    }
    return self::$modules[$module_name];
  }

  /**
   * Get the information about a module
   * @returns ArrayObject containing the module information from the module.info file or false if
   *                      not found
   */
  static function info($module_name) {
    $module_list = module::available();
    return isset($module_list->$module_name) ? $module_list->$module_name : false;
  }

  /**
   * Check to see if a module is installed
   * @param string $module_name
   */
  static function is_installed($module_name) {
    return array_key_exists($module_name, self::$modules);
  }

  /**
   * Check to see if a module is active
   * @param string $module_name
   */
  static function is_active($module_name) {
    return array_key_exists($module_name, self::$modules) &&
      self::$modules[$module_name]->active;
  }

  /**
   * Return the list of available modules, including uninstalled modules.
   */
  static function available() {
    if (empty(self::$available)) {
      $modules = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
      foreach (glob(MODPATH . "*/module.info") as $file) {
        $module_name = basename(dirname($file));
        $modules->$module_name =
          new ArrayObject(parse_ini_file($file), ArrayObject::ARRAY_AS_PROPS);
        $m =& $modules->$module_name;
        $m->installed = module::is_installed($module_name);
        $m->active = module::is_active($module_name);
        $m->code_version = $m->version;
        $m->version = module::get_version($module_name);
        $m->locked = false;

        if ($m->active && $m->version != $m->code_version) {
          site_status::warning(t("Some of your modules are out of date.  <a href=\"%upgrader_url\">Upgrade now!</a>", array("upgrader_url" => url::abs_site("upgrader"))), "upgrade_now");
        }
      }

      // Lock certain modules
      $modules->gallery->locked = true;
      $identity_module = module::get_var("gallery", "identity_provider", "user");
      $modules->$identity_module->locked = true;

      $modules->uasort(array("module", "module_comparator"));
      self::$available = $modules;
    }

    return self::$available;
  }

  /**
   * Natural name sort comparator
   */
  static function module_comparator($a, $b) {
    return strnatcasecmp($a->name, $b->name);
  }

  /**
   * Return a list of all the active modules in no particular order.
   */
  static function active() {
    return self::$active;
  }

  /**
   * Check that the module can be activated. (i.e. all the prerequistes exist)
   * @param string $module_name
   * @return array an array of warning or error messages to be displayed
   */
  static function can_activate($module_name) {
    module::_add_to_path($module_name);
    $messages = array();

    $installer_class = "{$module_name}_installer";
    if (class_exists($installer_class) && method_exists($installer_class, "can_activate")) {
      $messages = call_user_func(array($installer_class, "can_activate"));
    }

    // Remove it from the active path
    module::_remove_from_path($module_name);
    return $messages;
  }

  /**
   * Allow modules to indicate the impact of deactivating the specified module
   * @param string $module_name
   * @return array an array of warning or error messages to be displayed
   */
  static function can_deactivate($module_name) {
    $data = (object)array("module" => $module_name, "messages" => array());

    module::event("pre_deactivate", $data);

    return $data->messages;
  }

  /**
   * Install a module.  This will call <module>_installer::install(), which is responsible for
   * creating database tables, setting module variables and calling module::set_version().
   * Note that after installing, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function install($module_name) {
    module::_add_to_path($module_name);

    $installer_class = "{$module_name}_installer";
    if (class_exists($installer_class) && method_exists($installer_class, "install")) {
      call_user_func_array(array($installer_class, "install"), array());
    }
    module::set_version($module_name, module::available()->$module_name->code_version);

    // Set the weight of the new module, which controls the order in which the modules are
    // loaded. By default, new modules are installed at the end of the priority list.  Since the
    // id field is monotonically increasing, the easiest way to guarantee that is to set the weight
    // the same as the id.  We don't know that until we save it for the first time
    $module = ORM::factory("module")->where("name", "=", $module_name)->find();
    if ($module->loaded()) {
      $module->weight = $module->id;
      $module->save();
    }
    module::load_modules();

    // Now the module is installed but inactive, so don't leave it in the active path
    module::_remove_from_path($module_name);

    log::success(
      "module", t("Installed module %module_name", array("module_name" => $module_name)));
  }

  private static function _add_to_path($module_name) {
    $config = Kohana_Config::instance();
    $kohana_modules = $config->get("core.modules");
    array_unshift($kohana_modules, MODPATH . $module_name);
    $config->set("core.modules",  $kohana_modules);
    // Rebuild the include path so the module installer can benefit from auto loading
    Kohana::include_paths(true);
  }

  private static function _remove_from_path($module_name) {
    $config = Kohana_Config::instance();
    $kohana_modules = $config->get("core.modules");
    if (($key = array_search(MODPATH . $module_name, $kohana_modules)) !== false) {
      unset($kohana_modules[$key]);
      $kohana_modules = array_values($kohana_modules); // reindex
    }
    $config->set("core.modules",  $kohana_modules);
    Kohana::include_paths(true);
  }

  /**
   * Upgrade a module.  This will call <module>_installer::upgrade(), which is responsible for
   * modifying database tables, changing module variables and calling module::set_version().
   * Note that after upgrading, the module must be activated before it is available for use.
   * @param string $module_name
   */
  static function upgrade($module_name) {
    $version_before = module::get_version($module_name);
    $installer_class = "{$module_name}_installer";
    $available = module::available();
    if (class_exists($installer_class) && method_exists($installer_class, "upgrade")) {
      call_user_func_array(array($installer_class, "upgrade"), array($version_before));
    } else {
      if (isset($available->$module_name->code_version)) {
        module::set_version($module_name, $available->$module_name->code_version);
      } else {
        throw new Exception("@todo UNKNOWN_MODULE");
      }
    }
    module::load_modules();

    $version_after = module::get_version($module_name);
    if ($version_before != $version_after) {
      log::success(
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
   * Activate an installed module.  This will call <module>_installer::activate() which should take
   * any steps to make sure that the module is ready for use.  This will also activate any
   * existing graphics rules for this module.
   * @param string $module_name
   */
  static function activate($module_name) {
    module::_add_to_path($module_name);

    $installer_class = "{$module_name}_installer";
    if (class_exists($installer_class) && method_exists($installer_class, "activate")) {
      call_user_func_array(array($installer_class, "activate"), array());
    }

    $module = module::get($module_name);
    if ($module->loaded()) {
      $module->active = true;
      $module->save();
    }
    module::load_modules();

    graphics::activate_rules($module_name);

    block_manager::activate_blocks($module_name);

    log::success(
      "module", t("Activated module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Deactivate an installed module.  This will call <module>_installer::deactivate() which should
   * take any cleanup steps to make sure that the module isn't visible in any way.  Note that the
   * module remains available in Kohana's cascading file system until the end of the request!
   * @param string $module_name
   */
  static function deactivate($module_name) {
    $installer_class = "{$module_name}_installer";
    if (class_exists($installer_class) && method_exists($installer_class, "deactivate")) {
      call_user_func_array(array($installer_class, "deactivate"), array());
    }

    $module = module::get($module_name);
    if ($module->loaded()) {
      $module->active = false;
      $module->save();
    }
    module::load_modules();

    graphics::deactivate_rules($module_name);

    block_manager::deactivate_blocks($module_name);

    if (module::info($module_name)) {
      log::success(
        "module", t("Deactivated module %module_name", array("module_name" => $module_name)));
    } else {
      log::success(
        "module", t("Deactivated missing module %module_name", array("module_name" => $module_name)));
    }
  }

  /**
   * Deactivate modules that are unavailable or missing, yet still active.
   * This happens when a user deletes a module without deactivating it.
   */
  static function deactivate_missing_modules() {
    foreach (self::$modules as $module_name => $module) {
      if (module::is_active($module_name) && !module::info($module_name)) {
        module::deactivate($module_name);
      }
    }
  }

  /**
   * Uninstall a deactivated module.  This will call <module>_installer::uninstall() which should
   * take whatever steps necessary to make sure that all traces of a module are gone.
   * @param string $module_name
   */
  static function uninstall($module_name) {
    $installer_class = "{$module_name}_installer";
    if (class_exists($installer_class) && method_exists($installer_class, "uninstall")) {
      call_user_func(array($installer_class, "uninstall"));
    }

    graphics::remove_rules($module_name);
    $module = module::get($module_name);
    if ($module->loaded()) {
      $module->delete();
    }
    module::load_modules();

    // We could delete the module vars here too, but it's nice to leave them around
    // in case the module gets reinstalled.

    log::success(
      "module", t("Uninstalled module %module_name", array("module_name" => $module_name)));
  }

  /**
   * Load the active modules.  This is called at bootstrap time.
   */
  static function load_modules() {
    self::$modules = array();
    self::$active = array();
    $kohana_modules = array();

    // In version 32 we introduced a weight column so we can specify the module order
    // If we try to use that blindly, we'll break earlier versions before they can even
    // run the upgrader.
    $modules = module::get_version("gallery") < 32 ?
      ORM::factory("module")->find_all():
      ORM::factory("module")->order_by("weight")->find_all();

    foreach ($modules as $module) {
      self::$modules[$module->name] = $module;
      if (!$module->active) {
        continue;
      }

      if ($module->name == "gallery") {
        $gallery = $module;
      } else {
        self::$active[] = $module;
        $kohana_modules[] = MODPATH . $module->name;
      }
    }
    self::$active[] = $gallery;  // put gallery last in the module list to match core.modules
    $config = Kohana_Config::instance();
    $config->set(
      "core.modules", array_merge($kohana_modules, $config->get("core.modules")));
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

    if (method_exists("gallery_event", $function)) {
      switch (count($args)) {
      case 0:
        gallery_event::$function();
        break;
      case 1:
        gallery_event::$function($args[0]);
        break;
      case 2:
        gallery_event::$function($args[0], $args[1]);
        break;
      case 3:
        gallery_event::$function($args[0], $args[1], $args[2]);
        break;
      case 4:      // Context menu events have 4 arguments so lets optimize them
        gallery_event::$function($args[0], $args[1], $args[2], $args[3]);
        break;
      default:
        call_user_func_array(array("gallery_event", $function), $args);
      }
    }

    foreach (self::$active as $module) {
      if ($module->name == "gallery") {
        continue;
      }
      $class = "{$module->name}_event";
      if (class_exists($class) && method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }

    // Give the admin theme a chance to respond, if we're in admin mode.
    if (theme::$is_admin) {
      $class = theme::$admin_theme_name . "_event";
      if (class_exists($class) && method_exists($class, $function)) {
        call_user_func_array(array($class, $function), $args);
      }
    }

    // Give the site theme a chance to respond as well.  It gets a chance even in admin mode, as
    // long as the theme has an admin subdir.
    $class = theme::$site_theme_name . "_event";
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
    if (empty(self::$var_cache)) {
      self::$var_cache = Cache::instance()->get("var_cache");
      if (empty(self::$var_cache)) {
        // Cache doesn't exist, create it now.
        foreach (db::build()
                 ->select("module_name", "name", "value")
                 ->from("vars")
                 ->order_by("module_name")
                 ->order_by("name")
                 ->execute() as $row) {
          // Mute the "Creating default object from empty value" warning below
          @self::$var_cache->{$row->module_name}->{$row->name} = $row->value;
        }
        Cache::instance()->set("var_cache", self::$var_cache, array("vars"));
      }
    }

    if (isset(self::$var_cache->$module_name->$name)) {
      return self::$var_cache->$module_name->$name;
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
    $var = ORM::factory("var")
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
    self::$var_cache = null;
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
    db::build()
      ->update("vars")
      ->set("value", db::expr("`value` + $increment"))
      ->where("module_name", "=", $module_name)
      ->where("name", "=", $name)
      ->execute();

    Cache::instance()->delete("var_cache");
    self::$var_cache = null;
  }

 /**
   * Remove a variable for this module.
   * @param string $module_name
   * @param string $name
   */
  static function clear_var($module_name, $name) {
    db::build()
      ->delete("vars")
      ->where("module_name", "=", $module_name)
      ->where("name", "=", $name)
      ->execute();

    Cache::instance()->delete("var_cache");
    self::$var_cache = null;
  }

 /**
   * Remove all variables for this module.
   * @param string $module_name
   */
  static function clear_all_vars($module_name) {
    db::build()
      ->delete("vars")
      ->where("module_name", "=", $module_name)
      ->execute();

    Cache::instance()->delete("var_cache");
    self::$var_cache = null;
  }

  /**
   * Return the version of the installed module.
   * @param string $module_name
   */
  static function get_version($module_name) {
    return module::get($module_name)->version;
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
    module::deactivate_missing_modules();

    $modules_found = array();
    foreach ($obsolete_modules as $module => $version) {
      if (module::is_active($module) && (module::get_version($module) <= $version)) {
        $modules_found[] = $module;
      }
    }

    if ($modules_found) {
      // Need this to be on one super-long line or else the localization scanner may not work.
      // (ref: http://sourceforge.net/apps/trac/gallery/ticket/1321)
      return t("Recent upgrades to Gallery have made the following modules obsolete: %modules. We recommend that you <a href=\"%url_mod\">deactivate</a> the module(s). For more information, please see the <a href=\"%url_doc\">documentation page</a>.",
               array("modules" => implode(", ", $modules_found),
                     "url_mod" => url::site("admin/modules"),
                     "url_doc" => "http://codex.galleryproject.org/Gallery3:User_guide:Obsolete_modules"));
    }

    return null;
  }
}
