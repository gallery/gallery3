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
  public static function install() {
    $db = Database::instance();
    $version = 0;
    try {
      $version = module::get_version("core");
    } catch (Exception $e) {
      if ($e->getCode() != E_DATABASE_ERROR) {
        Kohana::log("error", $e);
        throw $e;
      }
    }

    if ($version == 0) {
      $db->query("CREATE TABLE `access_caches` (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9),
                   PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `access_intents` (
                   `id` int(9) NOT NULL auto_increment,
                   `item_id` int(9),
                   PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `items` (
                   `id` int(9) NOT NULL auto_increment,
                   `type` char(32) NOT NULL,
                   `title` char(255) default NULL,
                   `description` char(255) default NULL,
                   `name` char(255) default NULL,
                   `mime_type` char(64) default NULL,
                   `left` int(9) NOT NULL,
                   `right` int(9) NOT NULL,
                   `parent_id` int(9) NOT NULL,
                   `level` int(9) NOT NULL,
                   `width` int(9) default NULL,
                   `height` int(9) default NULL,
                   `thumbnail_width` int(9) default NULL,
                   `thumbnail_height` int(9) default NULL,
                   `resize_width` int(9) default NULL,
                   `resize_height` int(9) default NULL,
                   `owner_id` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   KEY `parent_id` (`parent_id`),
                   KEY `type` (`type`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `modules` (
                   `id` int(9) NOT NULL auto_increment,
                   `name` char(255) default NULL,
                   `version` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      $db->query("CREATE TABLE `permissions` (
                   `id` int(9) NOT NULL auto_increment,
                   `name` char(255) default NULL,
                   `version` int(9) default NULL,
                   PRIMARY KEY (`id`),
                   UNIQUE KEY(`name`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      foreach (array("albums", "resizes") as $dir) {
        @mkdir(VARPATH . $dir);
      }

      access::register_permission("view");
      access::register_permission("edit");

      $root = ORM::factory("item");
      $root->type = 'album';
      $root->title = "Gallery";
      $root->description = "Welcome to your Gallery3";
      $root->left = 1;
      $root->right = 2;
      $root->parent_id = 0;
      $root->level = 1;
      $root->set_thumbnail(DOCROOT . "core/tests/test.jpg", 200, 150)
        ->save();

      access::add_item($root);
      access::allow(0, "view", $root->id);
      access::deny(0, "edit", $root->id);

      module::set_version("core", 1);
    }
  }

  public static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS `access_cache`;");
    $db->query("DROP TABLE IF EXISTS `access_intent`;");
    $db->query("DROP TABLE IF EXISTS `permissions`;");
    $db->query("DROP TABLE IF EXISTS `items`;");
    $db->query("DROP TABLE IF EXISTS `modules`;");
    system("/bin/rm -rf " . VARPATH . "albums");
    system("/bin/rm -rf " . VARPATH . "resizes");
  }
}
