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
    $modules = Module::available();

    // Build our form object.  This has all of the module info embedded in it, too.
    $form = Formo::form()
      ->attr("id", "g-module-update-form")
      ->add("confirmed", "input|hidden", 0)
      ->add("modules", "group");
    foreach ($modules as $module_name => $module_info) {
      $form->modules->add($module_name, "checkbox", Module::is_active($module_name));
      $form->modules->$module_name->set("info", $module_info);
      if ($module_info->locked) {
        $form->modules->$module_name
          ->attr("disabled", "disabled")
          ->set("can_be_empty", false);  // Keep the orig values since disabled fields don't return.
      }
    }
    $form->add("submit", "input|submit", t("Update"));

    if ($form->load()->validate()) {
      // If confirmed is still 0, we need to check modules' can_activate/can_deactivate functions.
      if (!$form->confirmed->val()) {
        // Build the list of error/warn messages.
        $messages = array("error" => array(), "warn" => array());
        foreach ($modules as $module_name => $module_info) {
          if ($module_info->locked) {
            continue;
          }

          $desired = (bool) $form->modules->$module_name->val();
          if ($module_info->active && !$desired) {
            $messages = array_merge($messages, Module::can_deactivate($module_name));
          } else if (!$module_info->active && $desired) {
            $messages = array_merge($messages, Module::can_activate($module_name));
          }
        }

        // If the list isn't empty, then we need to return a confirmation dialog - build it.
        if (!empty($messages["error"]) || !empty($messages["warn"])) {
          // Modify our form - make module elements into hidden inputs, set confirmed to 1.
          foreach ($form->modules->as_array() as $module) {
            $module->set("driver", "input|hidden")
                   ->attr("class", "")
                   ->val((int) $module->val());
          }
          $form->confirmed->val(1);

          // Build the confirmation dialog view.
          $view = new View("admin/modules_confirm.html");
          $view->form = $form;
          $view->messages = $messages;

          // Send off the confirmation dialog.
          $this->response->json(array(
            "result" => "success",
            "dialog" => (string)$view,
            "allow_continue" => empty($messages["error"])
          ));
          return;
        }
      }

      // We're clear to proceed - activate/deactivate the modules as needed.
      $changes = new stdClass();
      $changes->activate = array();
      $changes->deactivate = array();
      $activated_names = array();
      $deactivated_names = array();

      foreach ($modules as $module_name => $module_info) {
        if ($module_info->locked) {
          continue;
        }

        try {
          $desired = (bool) $form->modules->$module_name->val();
          if ($module_info->active && !$desired) {
            Module::deactivate($module_name);
            $changes->deactivate[] = $module_name;
            $deactivated_names[] = t($module_info->name);
          } else if (!$module_info->active && $desired) {
            if (Module::is_installed($module_name)) {
              Module::upgrade($module_name);
            } else {
              Module::install($module_name);
            }
            Module::activate($module_name);
            $changes->activate[] = $module_name;
            $activated_names[] = t($module_info->name);
          }
        } catch (Exception $e) {
          Message::warning(t("An error occurred while installing the <b>%module_name</b> module",
                             array("module_name" => $module_info->name)));
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

      // If we got here after a confirmation dialog, we'll need an ajax reply.
      if ($this->request->is_ajax()) {
        $this->response->json(array(
          "result" => "success",
          "reload" => 1
        ));
        return;
      }
      // @todo: this redirect shouldn't be necessary... something is strange with the add/remove
      // from path code, and if we don't reload we can't find the required/admin.html view (in the
      // gallery module).  There's a bug elsewhere that needs to be caught.
      $this->redirect($this->request->uri());
    }

    // Build and return the view.
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Modules");
    $view->content = new View("admin/modules.html");
    $view->content->form = $form;
    $view->content->obsolete_modules_message = Module::get_obsolete_modules_message();

    $this->response->body($view);
  }
}
