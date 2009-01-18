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
class Admin_Dashboard_Controller extends Admin_Controller {
  public function index() {
    $blocks = unserialize(module::get_var("core", "dashboard_blocks"));

    $block_adder = new Block();
    $block_adder->id = "core:block_adder";
    $block_adder->title = t("Dashboard Content");
    $block_adder->content = $this->get_add_block_form();

    $view = new Admin_View("admin.html");
    $view->content = block_manager::get_html("dashboard_center");
    $view->sidebar = $block_adder . block_manager::get_html("dashboard_sidebar");
    print $view;
  }

  public function add_block() {
    $form = $this->get_add_block_form();
    if ($form->validate()) {
      list ($module_name, $block_id) = explode(":", $form->add_block->id->value);
      $blocks = block_manager::get_active();
      $available = block_manager::get_available();

      if ($form->add_block->center->value) {
        block_manager::add("dashboard_center", $module_name, $block_id);
        message::success(
          t("Added <b>%title</b> block to the dashboard center",
            array("title" => $available["$module_name:$id"])));
      } else {
        block_manager::add("dashboard_sidebar", $module_name, $block_id);
        message::success(
          t("Added <b>%title</b> to the dashboard sidebar",
            array("title" => $available["$module_name:$id"])));
      }
    }
    url::redirect("admin/dashboard");
  }

  public function remove_block($id) {
    access::verify_csrf();
    $blocks = block_manager::get_active();
    if (array_key_exists($id, $blocks["sidebar"])) {
      $deleted = $blocks["sidebar"][$id];
      block_manager::remove("dashboard_sidebar", $id);
    } else if (array_key_exists($id, $blocks["main"])) {
      $deleted = $blocks["main"][$id];
      block_manager::remove("dashboard_main", $id);
    }

    if (!empty($deleted)) {
      $available = block_manager::get_available();
      $title = $available[join(":", $deleted)];
      message::success(t("Removed <b>%title</b> block", array("title" => $title)));
    }

    url::redirect("admin");
  }

  public function get_add_block_form() {
    $form = new Forge("admin/dashboard/add_block", "", "post");
    $group = $form->group("add_block")->label(t("Add Block"));
    $group->dropdown("id")->label("Available Blocks")->options(block_manager::get_available());
    $group->submit("center")->value(t("Add to center"));
    $group->submit("sidebar")->value(t("Add to sidebar"));
    return $form;
  }
}
