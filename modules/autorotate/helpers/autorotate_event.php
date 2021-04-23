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
class autorotate_event_Core {
  // The assumption is that the exception was logged at a lower level, but we
  // don't want to screw up the processing that was generating the notification
  // so we don't pass the exception up the call stack
  static function item_created($item) {
    try {
      autorotate::rotate_item($item);
    } catch (Exception $e) {
      Kohana_Log::add("error", "@todo autorotate_event::item_created() failed");
      Kohana_Log::add("error", $e->getMessage() . "\n" . $e->getTraceAsString());
    }
  }
}