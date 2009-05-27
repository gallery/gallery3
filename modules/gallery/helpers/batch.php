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
class batch_Core {
  static function start() {
    $session = Session::instance();
    $session->set("batch_level", $session->get("batch_level", 0) + 1);
  }

  static function stop() {
    $session = Session::instance();
    $batch_level = $session->get("batch_level", 0) - 1;
    if ($batch_level > 0) {
      $session->set("batch_level", $batch_level);
    } else {
      $session->delete("batch_level");
      module::event("batch_complete");
    }
  }

  static function in_progress() {
    return Session::instance()->get("batch_level", 0) > 0;
  }
}
