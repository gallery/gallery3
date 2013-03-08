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
class Admin_Modules_Controller extends Admin_Controller {
  public function index() {
    // If modules need upgrading, this will get recreated in module::available()
    site_status::clear("upgrade_now");

    $view = new Admin_View("admin.html");
    $view->page_title = t("Modules");
    $view->content = new View("admin_modules.html");
    $view->content->available = module::available();
    $view->content->obsolete_modules_message = module::get_obsolete_modules_message();
    print $view;
  }


  public function confirm() {
    access::verify_csrf();

    $messages = array("error" => array(), "warn" => array());
    $desired_list = array();
    foreach (module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      if ($desired = Input::instance()->post($module_name) == 1) {
        $desired_list[] = $module_name;
      }
      if ($info->active && !$desired && module::is_active($module_name)) {
        $messages = array_merge($messages, module::can_deactivate($module_name));
      } else if (!$info->active && $desired && !module::is_active($module_name)) {
        $messages = array_merge($messages, module::can_activate($module_name));
      }
    }

    if (empty($messages["error"]) && empty($messages["warn"])) {
      $this->_do_save();
      $result["reload"] = 1;
    } else {
      $v = new View("admin_modules_confirm.html");
      $v->messages = $messages;
      $v->modules = $desired_list;
      $result["dialog"] = (string)$v;
      $result["allow_continue"] = empty($messages["error"]);
    }
    json::reply($result);
  }

  public function save() {
    access::verify_csrf();

    $this->_do_save();
    url::redirect("admin/modules");
  }

  private function _do_save() {
    $changes = new stdClass();
    $changes->activate = array();
    $changes->deactivate = array();
    $activated_names = array();
    $deactivated_names = array();
    foreach (module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      try {
        $desired = Input::instance()->post($module_name) == 1;
        if ($info->active && !$desired && module::is_active($module_name)) {
          module::deactivate($module_name);
          $changes->deactivate[] = $module_name;
          $deactivated_names[] = t($info->name);
        } else if (!$info->active && $desired && !module::is_active($module_name)) {
          if (module::is_installed($module_name)) {
            module::upgrade($module_name);
          } else {
            module::install($module_name);
          }
          module::activate($module_name);
          $changes->activate[] = $module_name;
          $activated_names[] = t($info->name);
        }
      } catch (Exception $e) {
        message::warning(t("An error occurred while installing the <b>%module_name</b> module",
                           array("module_name" => $info->name)));
        Kohana_Log::add("error", (string)$e);
      }
    }

    module::event("module_change", $changes);

    // @todo this type of collation is questionable from an i18n perspective
    if ($activated_names) {
      message::success(t("Activated: %names", array("names" => join(", ", $activated_names))));
    }
    if ($deactivated_names) {
      message::success(t("Deactivated: %names", array("names" => join(", ", $deactivated_names))));
    }
  }
}

