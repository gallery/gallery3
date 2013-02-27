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
class gallery_installer {
  static function install() {
    $db = Database::instance();
    $db->query("CREATE TABLE {access_caches} (
                 `id` int(9) NOT NULL auto_increment,
                 `item_id` int(9),
                 PRIMARY KEY (`id`),
                 KEY (`item_id`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {access_intents} (
                 `id` int(9) NOT NULL auto_increment,
                 `item_id` int(9),
                 PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");

    // Using a simple index instead of a unique key for the
    // key column to avoid handling of concurrency issues
    // on insert. Thus allowing concurrent inserts on the
    // same cache key, as does Memcache / xcache.
    $db->query("CREATE TABLE {caches} (
                `id` int(9) NOT NULL auto_increment,
                `key` varchar(255) NOT NULL,
                `tags` varchar(255),
                `expiration` int(9) NOT NULL,
                `cache` longblob,
                PRIMARY KEY (`id`),
                UNIQUE KEY (`key`),
                KEY (`tags`))
                DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {failed_auths} (
                `id` int(9) NOT NULL auto_increment,
                `count` int(9) NOT NULL,
                `name` varchar(255) NOT NULL,
                `time` int(9) NOT NULL,
                PRIMARY KEY (`id`))
                DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {graphics_rules} (
                 `id` int(9) NOT NULL auto_increment,
                 `active` BOOLEAN default 0,
                 `args` varchar(255) default NULL,
                 `module_name` varchar(64) NOT NULL,
                 `operation` varchar(64) NOT NULL,
                 `priority` int(9) NOT NULL,
                 `target`  varchar(32) NOT NULL,
                 PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {incoming_translations} (
                 `id` int(9) NOT NULL auto_increment,
                 `key` char(32) NOT NULL,
                 `locale` char(10) NOT NULL,
                 `message` text NOT NULL,
                 `revision` int(9) DEFAULT NULL,
                 `translation` text,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`key`, `locale`),
                 KEY `locale_key` (`locale`, `key`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {items} (
                 `id` int(9) NOT NULL auto_increment,
                 `album_cover_item_id` int(9) default NULL,
                 `captured` int(9) default NULL,
                 `created` int(9) default NULL,
                 `description` text default NULL,
                 `height` int(9) default NULL,
                 `left_ptr` int(9) NOT NULL,
                 `level` int(9) NOT NULL,
                 `mime_type` varchar(64) default NULL,
                 `name` varchar(255) default NULL,
                 `owner_id` int(9) default NULL,
                 `parent_id` int(9) NOT NULL,
                 `rand_key` decimal(11,10) default NULL,
                 `relative_path_cache` varchar(255) default NULL,
                 `relative_url_cache` varchar(255) default NULL,
                 `resize_dirty` boolean default 1,
                 `resize_height` int(9) default NULL,
                 `resize_width` int(9) default NULL,
                 `right_ptr` int(9) NOT NULL,
                 `slug` varchar(255) default NULL,
                 `sort_column` varchar(64) default NULL,
                 `sort_order` char(4) default 'ASC',
                 `thumb_dirty` boolean default 1,
                 `thumb_height` int(9) default NULL,
                 `thumb_width` int(9) default NULL,
                 `title` varchar(255) default NULL,
                 `type` varchar(32) NOT NULL,
                 `updated` int(9) default NULL,
                 `view_count` int(9) default 0,
                 `weight` int(9) NOT NULL default 0,
                 `width` int(9) default NULL,
                 PRIMARY KEY (`id`),
                 KEY `parent_id` (`parent_id`),
                 KEY `type` (`type`),
                 KEY `random` (`rand_key`),
                 KEY `weight` (`weight` DESC),
                 KEY `left_ptr` (`left_ptr`),
                 KEY `relative_path_cache` (`relative_path_cache`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {logs} (
                 `id` int(9) NOT NULL auto_increment,
                 `category` varchar(64) default NULL,
                 `html` varchar(255) default NULL,
                 `message` text default NULL,
                 `referer` varchar(255) default NULL,
                 `severity` int(9) default 0,
                 `timestamp` int(9) default 0,
                 `url` varchar(255) default NULL,
                 `user_id` int(9) default 0,
                 PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {messages} (
                 `id` int(9) NOT NULL auto_increment,
                 `key` varchar(255) default NULL,
                 `severity` varchar(32) default NULL,
                 `value` text default NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`key`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {modules} (
                 `id` int(9) NOT NULL auto_increment,
                 `active` BOOLEAN default 0,
                 `name` varchar(64) default NULL,
                 `version` int(9) default NULL,
                 `weight` int(9) default NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`name`),
                 KEY (`weight`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {outgoing_translations} (
                 `id` int(9) NOT NULL auto_increment,
                 `base_revision` int(9) DEFAULT NULL,
                 `key` char(32) NOT NULL,
                 `locale` char(10) NOT NULL,
                 `message` text NOT NULL,
                 `translation` text,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`key`, `locale`),
                 KEY `locale_key` (`locale`, `key`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {permissions} (
                 `id` int(9) NOT NULL auto_increment,
                 `display_name` varchar(64) default NULL,
                 `name` varchar(64) default NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`name`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {sessions} (
                `session_id` varchar(127) NOT NULL,
                `data` text NOT NULL,
                `last_activity` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`session_id`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {tasks} (
                `id` int(9) NOT NULL auto_increment,
                `callback` varchar(128) default NULL,
                `context` text NOT NULL,
                `done` boolean default 0,
                `name` varchar(128) default NULL,
                `owner_id` int(9) default NULL,
                `percent_complete` int(9) default 0,
                `state` varchar(32) default NULL,
                `status` varchar(255) default NULL,
                `updated` int(9) default NULL,
                PRIMARY KEY (`id`),
                KEY (`owner_id`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {themes} (
                 `id` int(9) NOT NULL auto_increment,
                 `name` varchar(64) default NULL,
                 `version` int(9) default NULL,
                 PRIMARY KEY (`id`),
                 UNIQUE KEY(`name`))
               DEFAULT CHARSET=utf8;");

    $db->query("CREATE TABLE {vars} (
                `id` int(9) NOT NULL auto_increment,
                `module_name` varchar(64) NOT NULL,
                `name` varchar(64) NOT NULL,
                `value` text,
                PRIMARY KEY (`id`),
                UNIQUE KEY(`module_name`, `name`))
               DEFAULT CHARSET=utf8;");

    foreach (array("albums", "logs", "modules", "resizes", "thumbs", "tmp", "uploads") as $dir) {
      @mkdir(VARPATH . $dir);
      if (in_array($dir, array("logs", "tmp", "uploads"))) {
        self::_protect_directory(VARPATH . $dir);
      }
    }

    access::register_permission("view", "View");
    access::register_permission("view_full", "View full size");
    access::register_permission("edit", "Edit");
    access::register_permission("add", "Add");

    // Mark for translation (must be the same strings as used above)
    t("View full size");
    t("View");
    t("Edit");
    t("Add");

    // Hardcode the first item to sidestep ORM validation rules
    $now = time();
    db::build()->insert(
      "items",
      array("created" => $now,
            "description" => "",
            "left_ptr" => 1,
            "level" => 1,
            "parent_id" => 0,
            "resize_dirty" => 1,
            "right_ptr" => 2,
            "sort_column" => "weight",
            "sort_order" => "ASC",
            "thumb_dirty" => 1,
            "title" => "Gallery",
            "type" => "album",
            "updated" => $now,
            "weight" => 1))
      ->execute();
    $root = ORM::factory("item", 1);
    access::add_item($root);

    module::set_var("gallery", "active_site_theme", "wind");
    module::set_var("gallery", "active_admin_theme", "admin_wind");
    module::set_var("gallery", "page_size", 9);
    module::set_var("gallery", "thumb_size", 200);
    module::set_var("gallery", "resize_size", 640);
    module::set_var("gallery", "default_locale", "en_US");
    module::set_var("gallery", "image_quality", 75);
    module::set_var("gallery", "image_sharpen", 15);
    module::set_var("gallery", "upgrade_checker_auto_enabled", true);

    // Add rules for generating our thumbnails and resizes
    graphics::add_rule(
      "gallery", "thumb", "gallery_graphics::resize",
      array("width" => 200, "height" => 200, "master" => Image::AUTO),
      100);
    graphics::add_rule(
      "gallery", "resize", "gallery_graphics::resize",
      array("width" => 640, "height" => 640, "master" => Image::AUTO),
      100);

    // Instantiate default themes (site and admin)
    foreach (array("wind", "admin_wind") as $theme_name) {
      $theme_info = new ArrayObject(parse_ini_file(THEMEPATH . $theme_name . "/theme.info"),
                                    ArrayObject::ARRAY_AS_PROPS);
      $theme = ORM::factory("theme");
      $theme->name = $theme_name;
      $theme->version = $theme_info->version;
      $theme->save();
    }

    block_manager::add("dashboard_sidebar", "gallery", "block_adder");
    block_manager::add("dashboard_sidebar", "gallery", "stats");
    block_manager::add("dashboard_sidebar", "gallery", "platform_info");
    block_manager::add("dashboard_sidebar", "gallery", "project_news");
    block_manager::add("dashboard_center", "gallery", "welcome");
    block_manager::add("dashboard_center", "gallery", "upgrade_checker");
    block_manager::add("dashboard_center", "gallery", "photo_stream");
    block_manager::add("dashboard_center", "gallery", "log_entries");

    module::set_var("gallery", "choose_default_tookit", 1);
    module::set_var("gallery", "date_format", "Y-M-d");
    module::set_var("gallery", "date_time_format", "Y-M-d H:i:s");
    module::set_var("gallery", "time_format", "H:i:s");
    module::set_var("gallery", "show_credits", 1);
    // Mark string for translation
    $powered_by_string = t("Powered by <a href=\"%url\">%gallery_version</a>",
                           array("locale" => "root"));
    module::set_var("gallery", "credits", (string) $powered_by_string);
    module::set_var("gallery", "simultaneous_upload_limit", 5);
    module::set_var("gallery", "admin_area_timeout", 90 * 60);
    module::set_var("gallery", "maintenance_mode", 0);
    module::set_var("gallery", "visible_title_length", 15);
    module::set_var("gallery", "favicon_url", "lib/images/favicon.ico");
    module::set_var("gallery", "apple_touch_icon_url", "lib/images/apple-touch-icon.png");
    module::set_var("gallery", "email_from", "");
    module::set_var("gallery", "email_reply_to", "");
    module::set_var("gallery", "email_line_length", 70);
    module::set_var("gallery", "email_header_separator", serialize("\n"));
    module::set_var("gallery", "show_user_profiles_to", "registered_users");
    module::set_var("gallery", "extra_binary_paths", "/usr/local/bin:/opt/local/bin:/opt/bin");
    module::set_var("gallery", "timezone", null);
    module::set_var("gallery", "lock_timeout", 1);
    module::set_var("gallery", "movie_extract_frame_time", 3);
    module::set_var("gallery", "movie_allow_uploads", "autodetect");
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      module::set_var("gallery", "date_format", "Y-M-d");
      module::set_var("gallery", "date_time_format", "Y-M-d H:i:s");
      module::set_var("gallery", "time_format", "H:i:s");
      module::set_version("gallery", $version = 2);
    }

    if ($version == 2) {
      module::set_var("gallery", "show_credits", 1);
      module::set_version("gallery", $version = 3);
    }

    if ($version == 3) {
      $db->query("CREATE TABLE {caches} (
                 `id` varchar(255) NOT NULL,
                 `tags` varchar(255),
                 `expiration` int(9) NOT NULL,
                 `cache` text,
                 PRIMARY KEY (`id`),
                 KEY (`tags`))
                 DEFAULT CHARSET=utf8;");
      module::set_version("gallery", $version = 4);
    }

    if ($version == 4) {
      Cache::instance()->delete_all();
      $db->query("ALTER TABLE {caches} MODIFY COLUMN `cache` LONGBLOB");
      module::set_version("gallery", $version = 5);
    }

    if ($version == 5) {
      Cache::instance()->delete_all();
      $db->query("ALTER TABLE {caches} DROP COLUMN `id`");
      $db->query("ALTER TABLE {caches} ADD COLUMN `key` varchar(255) NOT NULL");
      $db->query("ALTER TABLE {caches} ADD COLUMN `id` int(9) NOT NULL auto_increment PRIMARY KEY");
      module::set_version("gallery", $version = 6);
    }

    if ($version == 6) {
      module::clear_var("gallery", "version");
      module::set_version("gallery", $version = 7);
    }

    if ($version == 7) {
      $groups = identity::groups();
      $permissions = ORM::factory("permission")->find_all();
      foreach($groups as $group) {
        foreach($permissions as $permission) {
          // Update access intents
          $db->query("ALTER TABLE {access_intents} MODIFY COLUMN {$permission->name}_{$group->id} BINARY(1) DEFAULT NULL");
          // Update access cache
          if ($permission->name === "view") {
            $db->query("ALTER TABLE {items} MODIFY COLUMN {$permission->name}_{$group->id} BINARY(1) DEFAULT FALSE");
          } else {
            $db->query("ALTER TABLE {access_caches} MODIFY COLUMN {$permission->name}_{$group->id} BINARY(1) NOT NULL DEFAULT FALSE");
          }
        }
      }
      module::set_version("gallery", $version = 8);
    }

    if ($version == 8) {
      $db->query("ALTER TABLE {items} CHANGE COLUMN `left`  `left_ptr`  INT(9) NOT NULL;");
      $db->query("ALTER TABLE {items} CHANGE COLUMN `right` `right_ptr` INT(9) NOT NULL;");
      module::set_version("gallery", $version = 9);
    }

    if ($version == 9) {
      $db->query("ALTER TABLE {items} ADD KEY `weight` (`weight` DESC);");

      module::set_version("gallery", $version = 10);
    }

    if ($version == 10) {
      module::set_var("gallery", "image_sharpen", 15);

      module::set_version("gallery", $version = 11);
    }

    if ($version == 11) {
      $db->query("ALTER TABLE {items} ADD COLUMN `relative_url_cache` varchar(255) DEFAULT NULL");
      $db->query("ALTER TABLE {items} ADD COLUMN `slug` varchar(255) DEFAULT NULL");

      // This is imperfect since some of the slugs may contain invalid characters, but it'll do
      // for now because we don't want a lengthy operation here.
      $db->query("UPDATE {items} SET `slug` = `name`");

      // Flush all path caches because we're going to start urlencoding them.
      $db->query("UPDATE {items} SET `relative_url_cache` = NULL, `relative_path_cache` = NULL");
      module::set_version("gallery", $version = 12);
    }

    if ($version == 12) {
      if (module::get_var("gallery", "active_site_theme") == "default") {
        module::set_var("gallery", "active_site_theme", "wind");
      }
      if (module::get_var("gallery", "active_admin_theme") == "admin_default") {
        module::set_var("gallery", "active_admin_theme", "admin_wind");
      }
      module::set_version("gallery", $version = 13);
    }

    if ($version == 13) {
      // Add rules for generating our thumbnails and resizes
      Database::instance()->query(
        "UPDATE {graphics_rules} SET `operation` = CONCAT('gallery_graphics::', `operation`);");
      module::set_version("gallery", $version = 14);
    }

    if ($version == 14) {
      $sidebar_blocks = block_manager::get_active("site_sidebar");
      if (empty($sidebar_blocks)) {
        $available_blocks = block_manager::get_available_site_blocks();
        foreach  (array_keys(block_manager::get_available_site_blocks()) as $id) {
          $sidebar_blocks[] = explode(":", $id);
        }
        block_manager::set_active("site_sidebar", $sidebar_blocks);
      }
      module::set_version("gallery", $version = 15);
    }

    if ($version == 15) {
      module::set_var("gallery", "identity_provider", "user");
      module::set_version("gallery", $version = 16);
    }

    // Convert block keys to an md5 hash of the module and block name
    if ($version == 16) {
      foreach (array("dashboard_sidebar", "dashboard_center", "site_sidebar") as $location) {
        $blocks = block_manager::get_active($location);
        $new_blocks = array();
        foreach ($blocks as $block) {
          $new_blocks[md5("{$block[0]}:{$block[1]}")] = $block;
        }
        block_manager::set_active($location, $new_blocks);
      }
      module::set_version("gallery", $version = 17);
    }

    // We didn't like md5 hashes so convert block keys back to random keys to allow duplicates.
    if ($version == 17) {
      foreach (array("dashboard_sidebar", "dashboard_center", "site_sidebar") as $location) {
        $blocks = block_manager::get_active($location);
        $new_blocks = array();
        foreach ($blocks as $block) {
          $new_blocks[random::int()] = $block;
        }
        block_manager::set_active($location, $new_blocks);
      }
      module::set_version("gallery", $version = 18);
    }

    // Rename blocks_site.sidebar to blocks_site_sidebar
    if ($version == 18) {
      $blocks = block_manager::get_active("site.sidebar");
      block_manager::set_active("site_sidebar", $blocks);
      module::clear_var("gallery", "blocks_site.sidebar");
      module::set_version("gallery", $version = 19);
    }

    // Set a default for the number of simultaneous uploads
    // Version 20 was reverted in 57adefc5baa7a2b0dfcd3e736e80c2fa86d3bfa2, so skip it.
    if ($version == 19 || $version == 20) {
      module::set_var("gallery", "simultaneous_upload_limit", 5);
      module::set_version("gallery", $version = 21);
    }

    // Update the graphics rules table so that the maximum height for resizes is 640 not 480.
    // Fixes ticket #671
    if ($version == 21) {
      $resize_rule = ORM::factory("graphics_rule")
        ->where("id", "=", "2")
        ->find();
      // make sure it hasn't been changed already
      $args = unserialize($resize_rule->args);
      if ($args["height"] == 480 && $args["width"] == 640) {
        $args["height"] = 640;
        $resize_rule->args = serialize($args);
        $resize_rule->save();
      }
      module::set_version("gallery", $version = 22);
    }

    // Update slug values to be legal.  We should have done this in the 11->12 upgrader, but I was
    // lazy.  Mea culpa!
    if ($version == 22) {
      foreach (db::build()
               ->from("items")
               ->select("id", "slug")
               ->where(db::expr("`slug` REGEXP '[^_A-Za-z0-9-]'"), "=", 1)
               ->execute() as $row) {
        $new_slug = item::convert_filename_to_slug($row->slug);
        if (empty($new_slug)) {
          $new_slug = random::int();
        }
        db::build()
          ->update("items")
          ->set("slug", $new_slug)
          ->set("relative_url_cache", null)
          ->where("id", "=", $row->id)
          ->execute();
      }
      module::set_version("gallery", $version = 23);
    }

    if ($version == 23) {
      $db->query("CREATE TABLE {failed_logins} (
                  `id` int(9) NOT NULL auto_increment,
                  `count` int(9) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `time` int(9) NOT NULL,
                  PRIMARY KEY (`id`))
                  DEFAULT CHARSET=utf8;");
      module::set_version("gallery", $version = 24);
    }

    if ($version == 24) {
      foreach (array("logs", "tmp", "uploads") as $dir) {
        self::_protect_directory(VARPATH . $dir);
      }
      module::set_version("gallery", $version = 25);
    }

    if ($version == 25) {
      db::build()
        ->update("items")
        ->set("title", db::expr("`name`"))
        ->and_open()
        ->where("title", "IS", null)
        ->or_where("title", "=", "")
        ->close()
        ->execute();
      module::set_version("gallery", $version = 26);
    }

    if ($version == 26) {
      if (in_array("failed_logins", Database::instance()->list_tables())) {
        $db->query("RENAME TABLE {failed_logins} TO {failed_auths}");
      }
      module::set_version("gallery", $version = 27);
    }

    if ($version == 27) {
      // Set the admin area timeout to 90 minutes
      module::set_var("gallery", "admin_area_timeout", 90 * 60);
      module::set_version("gallery", $version = 28);
    }

    if ($version == 28) {
      module::set_var("gallery", "credits", "Powered by <a href=\"%url\">%gallery_version</a>");
      module::set_version("gallery", $version = 29);
    }

    if ($version == 29) {
      $db->query("ALTER TABLE {caches} ADD KEY (`key`);");
      module::set_version("gallery", $version = 30);
    }

    if ($version == 30) {
      module::set_var("gallery", "maintenance_mode", 0);
      module::set_version("gallery", $version = 31);
    }

    if ($version == 31) {
      $db->query("ALTER TABLE {modules} ADD COLUMN `weight` int(9) DEFAULT NULL");
      $db->query("ALTER TABLE {modules} ADD KEY (`weight`)");
      db::update("modules")
        ->set("weight", db::expr("`id`"))
        ->execute();
      module::set_version("gallery", $version = 32);
    }

    if ($version == 32) {
      $db->query("ALTER TABLE {items} ADD KEY (`left_ptr`)");
      module::set_version("gallery", $version = 33);
    }

    if ($version == 33) {
      $db->query("ALTER TABLE {access_caches} ADD KEY (`item_id`)");
      module::set_version("gallery", $version = 34);
    }

    if ($version == 34) {
      module::set_var("gallery", "visible_title_length", 15);
      module::set_version("gallery", $version = 35);
    }

    if ($version == 35) {
      module::set_var("gallery", "favicon_url", "lib/images/favicon.ico");
      module::set_version("gallery", $version = 36);
    }

    if ($version == 36) {
      module::set_var("gallery", "email_from", "admin@example.com");
      module::set_var("gallery", "email_reply_to", "public@example.com");
      module::set_var("gallery", "email_line_length", 70);
      module::set_var("gallery", "email_header_separator", serialize("\n"));
      module::set_version("gallery", $version = 37);
    }

    // Changed our minds and decided that the initial value should be empty
    // But don't just reset it blindly, only do it if the value is version 37 default
    if ($version == 37) {
      $email = module::get_var("gallery", "email_from", "");
      if ($email == "admin@example.com") {
        module::set_var("gallery", "email_from", "");
      }
      $email = module::get_var("gallery", "email_reply_to", "");
      if ($email == "admin@example.com") {
        module::set_var("gallery", "email_reply_to", "");
      }
      module::set_version("gallery", $version = 38);
    }

    if ($version == 38) {
      module::set_var("gallery", "show_user_profiles_to", "registered_users");
      module::set_version("gallery", $version = 39);
    }

    if ($version == 39) {
      module::set_var("gallery", "extra_binary_paths", "/usr/local/bin:/opt/local/bin:/opt/bin");
      module::set_version("gallery", $version = 40);
    }

    if ($version == 40) {
      module::clear_var("gallery", "_cache");
      module::set_version("gallery", $version = 41);
    }

    if ($version == 41) {
      $db->query("TRUNCATE TABLE {caches}");
      $db->query("ALTER TABLE {caches} DROP INDEX `key`, ADD UNIQUE `key` (`key`)");
      module::set_version("gallery", $version = 42);
    }

    if ($version == 42) {
      $db->query("ALTER TABLE {items} CHANGE `description` `description` text DEFAULT NULL");
      module::set_version("gallery", $version = 43);
    }

    if ($version == 43) {
      $db->query("ALTER TABLE {items} CHANGE `rand_key` `rand_key` DECIMAL(11, 10)");
      module::set_version("gallery", $version = 44);
    }

    if ($version == 44) {
      $db->query("ALTER TABLE {messages} CHANGE `value` `value` text default NULL");
      module::set_version("gallery", $version = 45);
    }

    if ($version == 45) {
      // Splice the upgrade_checker block into the admin dashboard at the top
      // of the page, but under the welcome block if it's in the first position.
      $blocks = block_manager::get_active("dashboard_center");
      $index = count($blocks) && current($blocks) == array("gallery", "welcome") ? 1 : 0;
      array_splice($blocks, $index, 0, array(random::int() => array("gallery", "upgrade_checker")));
      block_manager::set_active("dashboard_center", $blocks);

      module::set_var("gallery", "upgrade_checker_auto_enabled", true);
      module::set_version("gallery", $version = 46);
    }

    if ($version == 46) {
      module::set_var("gallery", "apple_touch_icon_url", "lib/images/apple-touch-icon.png");
      module::set_version("gallery", $version = 47);
    }

    if ($version == 47 || $version == 48) {
      // Add configuration variable to set timezone.  Defaults to the currently
      // used timezone (from PHP configuration).  Note that in v48 we were
      // setting this value incorrectly, so we're going to stomp this value for v49.
      module::set_var("gallery", "timezone", null);
      module::set_version("gallery", $version = 49);
    }

    if ($version == 49) {
      // In v49 we changed the Item_Model validation code to disallow files with two dots in them,
      // but we didn't rename any files which fail to validate, so as soon as you do anything to
      // change those files (eg. as a side effect of getting the url or file path) it fails to
      // validate.  Fix those here.  This might be slow, but if it times out it can just pick up
      // where it left off.
      foreach (db::build()
               ->from("items")
               ->select("id")
               ->where("type", "<>", "album")
               ->where(db::expr("`name` REGEXP '\\\\..*\\\\.'"), "=", 1)
               ->order_by("id", "asc")
               ->execute() as $row) {
        set_time_limit(30);
        $item = ORM::factory("item", $row->id);
        $item->name = legal_file::smash_extensions($item->name);
        $item->save();
      }
      module::set_version("gallery", $version = 50);
    }

    if ($version == 50) {
      // In v51, we added a lock_timeout variable so that administrators could edit the time out
      // from 1 second to a higher variable if their system runs concurrent parallel uploads for
      // instance.
      module::set_var("gallery", "lock_timeout", 1);
      module::set_version("gallery", $version = 51);
    }

    if ($version == 51) {
      // In v52, we added functions to the legal_file helper that map photo and movie file
      // extensions to their mime types (and allow extension of the list by other modules).  During
      // this process, we correctly mapped m4v files to video/x-m4v, correcting a previous error
      // where they were mapped to video/mp4.  This corrects the existing items.
      db::build()
        ->update("items")
        ->set("mime_type", "video/x-m4v")
        ->where("name", "REGEXP", "\.m4v$") // case insensitive since name column is utf8_general_ci
        ->execute();
      module::set_version("gallery", $version = 52);
    }

    if ($version == 52) {
      // In v53, we added the ability to change the default time used when extracting frames from
      // movies.  Previously we hard-coded this at 3 seconds, so we use that as the default.
      module::set_var("gallery", "movie_extract_frame_time", 3);
      module::set_version("gallery", $version = 53);
    }

    if ($version == 53) {
      // In v54, we changed how we check for name and slug conflicts in Item_Model.  Previously,
      // we checked the whole filename.  As a result, "foo.jpg" and "foo.png" were not considered
      // conflicting if their slugs were different (a rare case in practice since server_add and
      // uploader would give them both the same slug "foo").  Now, we check the filename without its
      // extension.  This upgrade stanza fixes any conflicts where they were previously allowed.

      // This might be slow, but if it times out it can just pick up where it left off.

      // Find and loop through each conflict (e.g. "foo.jpg", "foo.png", and "foo.flv" are one
      // conflict; "bar.jpg", "bar.png", and "bar.flv" are another)
      foreach (db::build()
               ->select_distinct(array("parent_base_name" =>
                 db::expr("CONCAT(`parent_id`, ':', LOWER(SUBSTR(`name`, 1, LOCATE('.', `name`) - 1)))")))
               ->select(array("C" => "COUNT(\"*\")"))
               ->from("items")
               ->where("type", "<>", "album")
               ->having("C", ">", 1)
               ->group_by("parent_base_name")
               ->execute() as $conflict) {
        list ($parent_id, $base_name) = explode(":", $conflict->parent_base_name, 2);
        $base_name_escaped = Database::escape_for_like($base_name);
        // Loop through the items for each conflict
        foreach (db::build()
                 ->from("items")
                 ->select("id")
                 ->where("type", "<>", "album")
                 ->where("parent_id", "=", $parent_id)
                 ->where("name", "LIKE", "{$base_name_escaped}.%")
                 ->limit(1000000)  // required to satisfy SQL syntax (no offset without limit)
                 ->offset(1)       // skips the 0th item
                 ->execute() as $row) {
          set_time_limit(30);
          $item = ORM::factory("item", $row->id);
          $item->name = $item->name;  // this will force Item_Model to check for conflicts on save
          $item->save();
        }
      }
      module::set_version("gallery", $version = 54);
    }

    if ($version == 54) {
      $db->query("ALTER TABLE {items} ADD KEY `relative_path_cache` (`relative_path_cache`)");
      module::set_version("gallery", $version = 55);
    }

    if ($version == 55) {
      // In v56, we added the ability to change the default behavior regarding movie uploads.  It
      // can be set to "always", "never", or "autodetect" to match the previous behavior where they
      // are allowed only if FFmpeg is found.
      module::set_var("gallery", "movie_allow_uploads", "autodetect");
      module::set_version("gallery", $version = 56);
    }

    if ($version == 56) {
      // Cleanup possible instances where resize_dirty of albums or movies was set to 0.  This is
      // unlikely to have occurred, and doesn't currently matter much since albums and movies don't
      // have resize images anyway.  However, it may be useful to be consistent here going forward.
      db::build()
        ->update("items")
        ->set("resize_dirty", 1)
        ->where("type", "<>", "photo")
        ->execute();
      module::set_version("gallery", $version = 57);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $db->query("DROP TABLE IF EXISTS {access_caches}");
    $db->query("DROP TABLE IF EXISTS {access_intents}");
    $db->query("DROP TABLE IF EXISTS {graphics_rules}");
    $db->query("DROP TABLE IF EXISTS {incoming_translations}");
    $db->query("DROP TABLE IF EXISTS {failed_auths}");
    $db->query("DROP TABLE IF EXISTS {items}");
    $db->query("DROP TABLE IF EXISTS {logs}");
    $db->query("DROP TABLE IF EXISTS {modules}");
    $db->query("DROP TABLE IF EXISTS {outgoing_translations}");
    $db->query("DROP TABLE IF EXISTS {permissions}");
    $db->query("DROP TABLE IF EXISTS {sessions}");
    $db->query("DROP TABLE IF EXISTS {tasks}");
    $db->query("DROP TABLE IF EXISTS {themes}");
    $db->query("DROP TABLE IF EXISTS {vars}");
    $db->query("DROP TABLE IF EXISTS {caches}");
    foreach (array("albums", "resizes", "thumbs", "uploads",
                   "modules", "logs", "database.php") as $entry) {
      system("/bin/rm -rf " . VARPATH . $entry);
    }
  }

  static function _protect_directory($dir) {
    $fp = @fopen("$dir/.htaccess", "w+");
    fwrite($fp, "DirectoryIndex .htaccess\nSetHandler Gallery_Security_Do_Not_Remove\n" .
           "Options None\n<IfModule mod_rewrite.c>\nRewriteEngine off\n</IfModule>\n" .
           "Order allow,deny\nDeny from all\n");
    fclose($fp);
  }
}
