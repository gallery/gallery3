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
class core_Core {
  static function maintenance_mode() {
    $maintenance_mode = Kohana::config("core.maintenance_mode", false, false);

    if (Router::$controller != "login" && !empty($maintenance_mode) && !user::active()->admin) {
      Router::$controller = "maintenance";
      Router::$controller_path = APPPATH . "controllers/maintenance.php";
      Router::$method = "index";
    }
  }

  static function move_item($source, $target) {
    access::required("edit", $source);
    access::required("edit", $target);
    $source->move_to($target);

    // If the target has no cover item, make this it.
    if ($target->album_cover_item_id == null)  {
      $target->album_cover_item_id =
        $source->is_album() ? $source->album_cover_item_id : $source->id;
      $target->save();
      graphics::generate($target);
    }
  }
}