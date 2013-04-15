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
class Gallery_Controller_Admin_Sidebar extends Controller_Admin {
  public function action_index() {
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Manage sidebar");
    $view->content = new View("admin/sidebar.html");
    $view->content->csrf = Access::csrf_token();
    $view->content->available = new View("admin/sidebar_blocks.html");
    $view->content->active = new View("admin/sidebar_blocks.html");
    list($view->content->available->blocks, $view->content->active->blocks) = $this->_get_blocks();
    print $view;
  }

  public function action_update() {
    Access::verify_csrf();

    $available_blocks = BlockManager::get_available_site_blocks();

    $active_blocks = array();
    foreach ((array) Request::current()->query("block") as $block_id) {
      $active_blocks[md5($block_id)] = explode(":", (string) $block_id);
    }
    BlockManager::set_active("site_sidebar", $active_blocks);

    $result = array("result" => "success");
    list($available, $active) = $this->_get_blocks();
    $v = new View("admin/sidebar_blocks.html");
    $v->blocks = $available;
    $result["available"] = $v->render();
    $v = new View("admin/sidebar_blocks.html");
    $v->blocks = $active;
    $result["active"] = $v->render();
    $message = t("Updated sidebar blocks");
    $result["message"] = (string) $message;
    JSON::reply($result);
  }

  private function _get_blocks() {
    $active_blocks = array();
    $available_blocks = BlockManager::get_available_site_blocks();
    foreach (BlockManager::get_active("site_sidebar") as $block) {
      $id = "{$block[0]}:{$block[1]}";
      if (!empty($available_blocks[$id])) {
        $active_blocks[$id] = $available_blocks[$id];
        unset($available_blocks[$id]);
      }
    }
    return array($available_blocks, $active_blocks);
  }
}

