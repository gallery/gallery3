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
class watermark_menu_Core {
  // @todo this needs to get on the admin page at some point

  public static function site_navigation($menu, $theme) {
    $user = user::active();
    if ($user->admin) {
      $menu->get("admin_menu")->append(
        Menu::Factory("dialog")
        ->id("watermark_Load")
        ->label(_("Load Watermark"))
        ->url(url::site("admin/watermark/load")));

      $path = module::get_var("watermark", "watermark_image_path");
      if (!empty($path)) {
        $menu->get("admin_menu")->append(
          Menu::Factory("dialog")
          ->id("watermark_position")
          ->label(_("Set Watermark Position"))
          ->url(url::site("admin/watermark/get_form/$user->id")));
      }
    }
  }
}
