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
class scheduler_installer {
  static function install() {
    $db = Database::instance();
    $db->query("CREATE TABLE {schedules} (
                 `id` int(9) NOT NULL auto_increment,
                 `name` varchar(128) NOT NULL,
                 `task_callback` varchar(128) NOT NULL,
                 `task_id` int(9) NULL,
                 `next_run_datetime` int(9) NOT NULL,
                 `interval` int(9) NOT NULL,
                 `busy` bool NOT NULL DEFAULT 0,
                 PRIMARY KEY (`id`),
                 KEY `run_date` (`next_run_datetime`, `busy`),
                 UNIQUE KEY (`name`))
               DEFAULT CHARSET=utf8;");
    module::set_version("scheduler", $version = 1);
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {schedules}");
  }
}
