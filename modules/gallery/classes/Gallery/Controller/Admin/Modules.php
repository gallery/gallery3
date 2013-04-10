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
class Gallery_Controller_Admin_Modules extends Controller_Admin {
  public function action_index() {
    // If modules need upgrading, this will get recreated in Module::available()
    SiteStatus::clear("upgrade_now");

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Modules");
    $view->content = new View("admin/modules.html");
    $view->content->available = Module::available();
    $view->content->obsolete_modules_message = Module::get_obsolete_modules_message();
    print $view;
  }


  public function action_confirm() {
    Access::verify_csrf();

    $messages = array("error" => array(), "warn" => array());
    $desired_list = array();
    foreach (Module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      if ($desired = Request::$current->post($module_name) == 1) {
        $desired_list[] = $module_name;
      }
      if ($info->active && !$desired && Module::is_active($module_name)) {
        $messages = array_merge($messages, Module::can_deactivate($module_name));
      } else if (!$info->active && $desired && !Module::is_active($module_name)) {
        $messages = array_merge($messages, Module::can_activate($module_name));
      }
    }

    if (empty($messages["error"]) && empty($messages["warn"])) {
      $this->_do_save();
      $result["reload"] = 1;
    } else {
      $v = new View("admin/modules_confirm.html");
      $v->messages = $messages;
      $v->modules = $desired_list;
      $result["dialog"] = (string)$v;
      $result["allow_continue"] = empty($messages["error"]);
    }
    JSON::reply($result);
  }

  public function action_save() {
    Access::verify_csrf();

    $this->_do_save();
    HTTP::redirect("admin/modules");
  }

  private function _do_save() {
    $changes = new stdClass();
    $changes->activate = array();
    $changes->deactivate = array();
    $activated_names = array();
    $deactivated_names = array();
    foreach (Module::available() as $module_name => $info) {
      if ($info->locked) {
        continue;
      }

      try {
        $desired = Request::$current->post($module_name) == 1;
        if ($info->active && !$desired && Module::is_active($module_name)) {
          Module::deactivate($module_name);
          $changes->deactivate[] = $module_name;
          $deactivated_names[] = t($info->name);
        } else if (!$info->active && $desired && !Module::is_active($module_name)) {
          if (Module::is_installed($module_name)) {
            Module::upgrade($module_name);
          } else {
            Module::install($module_name);
          }
          Module::activate($module_name);
          $changes->activate[] = $module_name;
          $activated_names[] = t($info->name);
        }
      } catch (Exception $e) {
        Message::warning(t("An error occurred while installing the <b>%module_name</b> module",
                           array("module_name" => $info->name)));
        Log::instance()->add(Log::ERROR, (string)$e);
      }
    }

    Module::event("module_change", $changes);

    // @todo this type of collation is questionable from an i18n perspective
    if ($activated_names) {
      Message::success(t("Activated: %names", array("names" => join(", ", $activated_names))));
    }
    if ($deactivated_names) {
      Message::success(t("Deactivated: %names", array("names" => join(", ", $deactivated_names))));
    }
  }
}

