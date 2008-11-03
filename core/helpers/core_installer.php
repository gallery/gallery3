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
class core_installer {
  public function install() {
    $db = Database::instance();
    try {
      $base_version = ORM::factory("module")->where("name", "core")->find()->version;
    } catch (Exception $e) {
      if ($e->getMessage() == "Table modules does not exist in your database.") {
	$base_version = 0;
      } else {
	throw $e;
      }
    }

    if ($base_version == 0) {
      $db->query("CREATE TABLE `modules` (
		   `id` int(9) NOT NULL auto_increment,
		   `name` char(255) default NULL,
		   `version` int(9) default NULL,
		   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
		 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `items` (
		   `id` int(9) NOT NULL auto_increment,
		   `type` char(32) default NULL,
		   `title` char(255) default NULL,
		   `path` char(255) default NULL,
		   `left` int(9) default NULL,
		   `right` int(9) default NULL,
		   `parent_id` int(9) default NULL,
		   `scope` int(9) default NULL,
		   PRIMARY KEY (`id`),
		   KEY `parent_id` (`parent_id`),
		   KEY `type` (`type`))
		 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      foreach (array("albums", "thumbnails") as $dir) {
	@mkdir(VARPATH . $dir);
      }

      $core = ORM::factory("module")->where("name", "core")->find();
      $core->name = "core";
      $core->version = 1;
      $core->save();

      $root = ORM::factory("item");
      $root->title = "Gallery";
      $root->make_root();
    }
  }

  public function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `items`;");
    $db->query("DROP TABLE IF EXISTS `modules`;");
  }
}
