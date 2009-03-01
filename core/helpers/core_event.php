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

class core_event_Core {
  static function group_created($group) {
    access::add_group($group);
  }

  static function group_before_delete($group) {
    access::delete_group($group);
  }

  static function item_created($item) {
    access::add_item($item);
  }

  static function item_before_delete($item) {
    access::delete_item($item);
  }

  static function start_batch() {
    $batch_id = Session::instance()->get("batch_id");
    if (empty($batch_id)) {
      $batch_id = mt_rand();
      Session::instance()->set("batch_id", $batch_id);
    }
  }

  static function end_batch() {
    Session::instance()->delete("batch_id");
  }
}
