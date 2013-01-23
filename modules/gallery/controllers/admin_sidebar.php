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
class Admin_Sidebar_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->page_title = t("Manage sidebar");
    $view->content = new View("admin_sidebar.html");
    $view->content->csrf = access::csrf_token();
    $view->content->available = new View("admin_sidebar_blocks.html");
    $view->content->active = new View("admin_sidebar_blocks.html");
    list($view->content->available->blocks, $view->content->active->blocks) = $this->_get_blocks();
    print $view;
  }

  public function update() {
    access::verify_csrf();

    $available_blocks = block_manager::get_available_site_blocks();

    $active_blocks = array();
    foreach (Input::instance()->get("block", array()) as $block_id) {
      $active_blocks[md5($block_id)] = explode(":", (string) $block_id);
    }
    block_manager::set_active("site_sidebar", $active_blocks);

    $result = array("result" => "success");
    list($available, $active) = $this->_get_blocks();
    $v = new View("admin_sidebar_blocks.html");
    $v->blocks = $available;
    $result["available"] = $v->render();
    $v = new View("admin_sidebar_blocks.html");
    $v->blocks = $active;
    $result["active"] = $v->render();
    $message = t("Updated sidebar blocks");
    $result["message"] = (string) $message;
    json::reply($result);
  }

  private function _get_blocks() {
    $active_blocks = array();
    $available_blocks = block_manager::get_available_site_blocks();
    foreach (block_manager::get_active("site_sidebar") as $block) {
      $id = "{$block[0]}:{$block[1]}";
      if (!empty($available_blocks[$id])) {
        $active_blocks[$id] = $available_blocks[$id];
        unset($available_blocks[$id]);
      }
    }
    return array($available_blocks, $active_blocks);
  }
}

