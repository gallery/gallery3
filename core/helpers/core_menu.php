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

class core_menu_Core {
  public static function items($menus, $theme) {
    $menus->append(new Menu(_("HOME"), url::base()));
    $menus->append(new Menu(_("BROWSE"), url::site("albums/1")));

    $user = Session::instance()->get('user', null);
    if ($user) {
      $upload_menu = new Menu(_("UPLOAD"));
      $upload_menu->append(
        new Menu(_("Add Item"), "#form/add/photos/" . $theme->item()->id));
      $upload_menu->append(
        new Menu(_("Local Upload"), "#photo/form/local_upload/" . $theme->item()->id));
      $menus->append($upload_menu);

      $admin_menu = new Menu(_("ADMIN"));

      // @todo need to do a permission check here
      $admin_menu->append(
        new Menu(_("Edit Item"), "#photo/form/local_upload/" . $theme->item()->id));

      if ($user->admin) {
        $admin_menu->append(
          new Menu(_("Site Admin"), url::site("admin")));
      }

      $menus->append($admin_menu);
    }
  }

}
