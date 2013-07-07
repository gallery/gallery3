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
class Tag_Hook_TagInstaller {
  static function install() {
    $db = Database::instance();
    $db->query(Database::CREATE, "CREATE TABLE IF NOT EXISTS {tags} (
                 `id` int(9) NOT NULL auto_increment,
                 `count` int(10) unsigned NOT NULL DEFAULT 0,
                 `name` varchar(128) NOT NULL,
                 `slug` varchar(128) NOT NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`name`))
               DEFAULT CHARSET=utf8;");

    $db->query(Database::CREATE, "CREATE TABLE IF NOT EXISTS {items_tags} (
                 `id` int(9) NOT NULL auto_increment,
                 `item_id` int(9) NOT NULL,
                 `tag_id` int(9) NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY(`tag_id`, `id`),
                 KEY(`item_id`, `id`))
               DEFAULT CHARSET=utf8;");
    Module::set_var("tag", "tag_cloud_size", 30);
  }

  static function upgrade($version) {
    $db = Database::instance();

    if ($version == 1) {
      $db->query(Database::ALTER, "ALTER TABLE {tags} MODIFY COLUMN `name` VARCHAR(128)");
      Module::set_version("tag", $version = 2);
    }

    if ($version == 2) {
      Module::set_var("tag", "tag_cloud_size", 30);
      Module::set_version("tag", $version = 3);
    }

    // In v4, we explicitly prohibit empty names, names with commas, or names with untrimmed spaces.
    // Although not previously prevented explicitly, the standard UI did not allow this, so the
    // likelihood that we have something to fix is *very* small.  We need to do this *before*
    // the slug upgrade or else we run the risk of tripping ORM validation errors.
    if ($version == 3) {
      // Replace commas with underscores, trim leading/trailing spaces.
      foreach (DB::select("id", "name")
               ->from("tags")
               ->where(DB::expr("`name` REGEXP ','"), "=", 1)
               ->or_where(DB::expr("`name` REGEXP '^\\\\s'"), "=", 1)
               ->or_where(DB::expr("`name` REGEXP '\\\\s$'"), "=", 1)
               ->as_object()
               ->execute() as $row) {
        $new_name = trim(str_replace(",", "_", $row->name));
        DB::update("tags")
          ->set(array("name" => $new_name))
          ->where("id", "=", $row->id)
          ->execute();
      }

      // Give empty tag names a random (non-empty) name.
      foreach (DB::select("id", "name")
               ->from("tags")
               ->where("name", "=", "")
               ->as_object()
               ->execute() as $row) {
        $new_name = "tag_" . Random::int(0, 999999);
        DB::update("tags")
          ->set(array("name" => $new_name))
          ->where("id", "=", $row->id)
          ->execute();
      }

      Module::set_version("tag", $version = 4);
    }

    // In v5, we added slugs to the tags, similar to item slugs.  This code is based on
    // the gallery module updates for version 11->12, 22->23, and 53->54.
    if ($version == 4) {
      // First, let's add the new column and lazily copy the tag name to the tag slug.  This is
      // quick, but the steps following may not be.  In case the slower steps stall, we need to
      // be sure that we don't run this part twice as that could cause badness.
      if (!array_key_exists("slug", $db->list_columns("tags"))) {
        $db->query(Database::ALTER, "ALTER TABLE {tags} ADD COLUMN `slug` varchar(128) NOT NULL");
        $db->query(Database::UPDATE, "UPDATE {tags} SET `slug` = `name`");
      }

      // Then, try to quickly fix the cases where this results in an illegal slug.
      foreach (DB::select("id", "slug")
               ->from("tags")
               ->where(DB::expr("`slug` REGEXP '[^_A-Za-z0-9-]'"), "=", 1)
               ->as_object()
               ->execute() as $row) {
        $new_slug = Item::convert_filename_to_slug($row->slug);
        if (empty($new_slug)) {
          // This will likely cause conflicts, but we'll catch those next.
          $new_slug = "tag";
        }
        DB::update("tags")
          ->set(array("slug" => $new_slug))
          ->where("id", "=", $row->id)
          ->execute();
      }

      // Finally, fix any conflicting tag slugs.  This might be slow, but if it times out
      // it can just pick up where it left off.
      foreach (DB::select("slug")
               ->distinct(true)
               ->select(array(DB::expr('COUNT("*")'), "C"))
               ->from("tags")
               ->having("C", ">", 1)
               ->group_by("slug")
               ->as_object()
               ->execute() as $conflict) {
        // Loop through the tags for each conflict
        foreach (DB::select("id")
                 ->from("tags")
                 ->where("slug", "=", $conflict->slug)
                 ->limit(1000000)  // required to satisfy SQL syntax (no offset without limit)
                 ->offset(1)       // skips the 0th item
                 ->as_object()
                 ->execute() as $row) {
          set_time_limit(30);
          $tag = ORM::factory("Tag", $row->id);
          $tag->save();  // Model_Tag will check for conflicts on save
        }
      }
      Module::set_version("tag", $version = 5);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query(Database::DROP, "DROP TABLE IF EXISTS {tags};");
    $db->query(Database::DROP, "DROP TABLE IF EXISTS {items_tags};");
  }
}
