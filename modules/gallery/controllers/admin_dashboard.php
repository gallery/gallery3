<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Admin_Dashboard_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = new View("admin_dashboard.html");
    $view->content->blocks = block_manager::get_html("dashboard_center");
    $view->sidebar = "<div id=\"gAdminDashboardSidebar\">" .
      block_manager::get_html("dashboard_sidebar") .
      "</div>";
    print $view;
  }

  public function add_block() {
    $form = core_block::get_add_block_form();
    if ($form->validate()) {
      list ($module_name, $id) = explode(":", $form->add_block->id->value);
      $available = block_manager::get_available();

      if ($form->add_block->center->value) {
        block_manager::add("dashboard_center", $module_name, $id);
        message::success(
          t("Added <b>%title</b> block to the dashboard center",
            array("title" => $available["$module_name:$id"])));
      } else {
        block_manager::add("dashboard_sidebar", $module_name, $id);
        message::success(
          t("Added <b>%title</b> to the dashboard sidebar",
            array("title" => $available["$module_name:$id"])));
      }
    }
    url::redirect("admin/dashboard");
  }

  public function remove_block($id) {
    access::verify_csrf();
    $blocks_center = block_manager::get_active("dashboard_center");
    $blocks_sidebar = block_manager::get_active("dashboard_sidebar");

    if (array_key_exists($id, $blocks_sidebar)) {
      $deleted = $blocks_sidebar[$id];
      block_manager::remove("dashboard_sidebar", $id);
    } else if (array_key_exists($id, $blocks_center)) {
      $deleted = $blocks_center[$id];
      block_manager::remove("dashboard_center", $id);
    }

    if (!empty($deleted)) {
      $available = block_manager::get_available();
      $title = $available[join(":", $deleted)];
      message::success(t("Removed <b>%title</b> block", array("title" => $title)));
    }

    url::redirect("admin");
  }

  public function reorder() {
    access::verify_csrf();
    $active_set = array();
    foreach (array("dashboard_sidebar", "dashboard_center") as $location) {
      foreach (block_manager::get_active($location) as $id => $info) {
        $active_set[$id] = $info;
      }
    }

    foreach (array("dashboard_sidebar", "dashboard_center") as $location) {
      $new_blocks = array();
      foreach ($this->input->get($location, array()) as $id) {
        $new_blocks[$id] = $active_set[$id];
      }
      block_manager::set_active($location, $new_blocks);
    }

    $this->_force_block_adder();
  }
}
