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
class Gallery_Controller_Permissions extends Controller {
  function browse($id) {
    $item = ORM::factory("item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

    if (!$item->is_album()) {
      Access::forbidden();
    }

    $view = new View("gallery/permissions_browse.html");
    $view->htaccess_works = Access::htaccess_works();
    $view->item = $item;
    $view->parents = $item->parents();
    $view->form = $this->_get_form($item);

    print $view;
  }

  function form($id) {
    $item = ORM::factory("item", $id);
    Access::required("view", $item);
    Access::required("edit", $item);

    if (!$item->is_album()) {
      Access::forbidden();
    }

    print $this->_get_form($item);
  }

  function change($command, $group_id, $perm_id, $item_id) {
    Access::verify_csrf();

    $group = Identity::lookup_group($group_id);
    $perm = ORM::factory("permission", $perm_id);
    $item = ORM::factory("item", $item_id);
    Access::required("view", $item);
    Access::required("edit", $item);

    if (!empty($group) && $perm->loaded() && $item->loaded()) {
      switch($command) {
      case "allow":
        Access::allow($group, $perm->name, $item);
        break;

      case "deny":
        Access::deny($group, $perm->name, $item);
        break;

      case "reset":
        Access::reset($group, $perm->name, $item);
        break;
      }

      // If the active user just took away their own edit permissions, give it back.
      if ($perm->name == "edit") {
        if (!Access::user_can(Identity::active_user(), "edit", $item)) {
          Access::allow($group, $perm->name, $item);
        }
      }
    }
  }

  private function _get_form($item) {
    $view = new View("gallery/permissions_form.html");
    $view->item = $item;
    $view->groups = Identity::groups();
    $view->permissions = ORM::factory("Permission")->find_all();
    return $view;
  }
}
