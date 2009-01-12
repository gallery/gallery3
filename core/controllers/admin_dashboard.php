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
    $view->content = $this->get_blocks($blocks["main"]);
    $view->sidebar = $block_adder . $this->get_blocks($blocks["sidebar"]);
    print $view;
  }

  public function add_block() {
    $form = $this->get_add_block_form();
    if ($form->validate()) {
      list ($module_name, $id) = explode(":", $form->add_block->id->value);
      $blocks = unserialize(module::get_var("core", "dashboard_blocks"));
      $available = $this->get_block_list();

      if ($form->add_block->center->value) {
        $blocks["main"][] = array($module_name, $id);
        message::success(
          t("Added <b>{{title}}</b> block to the main dashboard area",
            array("title" => $available["$module_name:$id"])));
      } else {
        $blocks["sidebar"][] = array($module_name, $id);
        message::success(
          t("Added <b>{{title}}</b> to the dashboard sidebar",
            array("title" => $available["$module_name:$id"])));
      }
      module::set_var("core", "dashboard_blocks", serialize($blocks));
    }
    url::redirect("admin/dashboard");
  }

  public function remove_block($id) {
    access::verify_csrf();
    $blocks = unserialize(module::get_var("core", "dashboard_blocks"));

    if (array_key_exists($id, $blocks["sidebar"])) {
      $deleted = $blocks["sidebar"][$id];
      unset($blocks["sidebar"][$id]);
    } else if (array_key_exists($id, $blocks["main"])) {
      $deleted = $blocks["main"][$id];
      unset($blocks["main"][$id]);
    }

    if (!empty($deleted)) {
      module::set_var("core", "dashboard_blocks", serialize($blocks));
      $available = $this->get_block_list();
      $title = $available[join(":", $deleted)];
      message::success(t("Removed <b>{{title}}</b> block", array("title" => $title)));
    }

    url::redirect("admin");
  }

  private function get_blocks($blocks) {
    $result = "";
    foreach ($blocks as $id => $desc) {
      if (method_exists("$desc[0]_dashboard", "get_block")) {
        $block = call_user_func(array("$desc[0]_dashboard", "get_block"), $desc[1]);
        $block->id = $id;
        $result .= $block;
      }
    }
    return $result;
  }

  private function get_block_list() {
    $blocks = array();
    foreach (module::installed() as $module) {
      if (method_exists("{$module->name}_dashboard", "get_list")) {
        foreach (call_user_func(array("{$module->name}_dashboard", "get_list")) as $id => $title) {
          $blocks["{$module->name}:$id"] = $title;
        }
      }
    }
    return $blocks;
  }

  public function get_add_block_form() {
    $form = new Forge("admin/dashboard/add_block", "", "post");
    $group = $form->group("add_block")->label(t("Add Block"));
    $group->dropdown("id")->label("Available Blocks")->options($this->get_block_list());
    $group->submit("center")->value(t("Add to center"));
    $group->submit("sidebar")->value(t("Add to sidebar"));
    return $form;
  }
}

