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
class Comment_Hook_CommentInstaller {
  static function install() {
    $db = Database::instance();
    $db->query(Database::CREATE, "CREATE TABLE IF NOT EXISTS {comments} (
                 `author_id` int(9) default NULL,
                 `created` int(9) NOT NULL,
                 `guest_email` varchar(128) default NULL,
                 `guest_name` varchar(128) default NULL,
                 `guest_url` varchar(255) default NULL,
                 `id` int(9) NOT NULL auto_increment,
                 `item_id` int(9) NOT NULL,
                 `server_http_accept_charset` varchar(64) default NULL,
                 `server_http_accept_encoding` varchar(64) default NULL,
                 `server_http_accept_language` varchar(64) default NULL,
                 `server_http_accept` varchar(128) default NULL,
                 `server_http_connection` varchar(64) default NULL,
                 `server_http_referer` varchar(255) default NULL,
                 `server_http_user_agent` varchar(128) default NULL,
                 `server_name` varchar(64) default NULL,
                 `server_query_string` varchar(64) default NULL,
                 `server_remote_addr` varchar(40) default NULL,
                 `server_remote_host` varchar(255) default NULL,
                 `server_remote_port` varchar(16) default NULL,
                 `state` varchar(15) default 'unpublished',
                 `text` text,
                 `updated` int(9) NOT NULL,
               PRIMARY KEY (`id`))
               DEFAULT CHARSET=utf8;");

    Module::set_var("comment", "spam_caught", 0);
    Module::set_var("comment", "access_permissions", "everybody");
    Module::set_var("comment", "rss_visible", "all");
  }

  static function upgrade($version) {
    $db = Database::instance();
    if ($version == 1) {
      $db->query(Database::ALTER, "ALTER TABLE {comments} CHANGE `state` `state` varchar(15) default 'unpublished'");
      Module::set_version("comment", $version = 2);
    }

    if ($version == 2) {
      Module::set_var("comment", "access_permissions", "everybody");
      Module::set_version("comment", $version = 3);
    }

    if ($version == 3) {
      // 40 bytes for server_remote_addr is enough to swallow the longest
      // representation of an IPv6 addy.
      //
      // 255 bytes for server_remote_host is enough to swallow the longest
      // legit DNS entry, with a few bytes to spare.
      $db->query(Database::ALTER,
        "ALTER TABLE {comments} CHANGE `server_remote_addr` `server_remote_addr` varchar(40)");
      $db->query(Database::ALTER,
        "ALTER TABLE {comments} CHANGE `server_remote_host` `server_remote_host` varchar(255)");
      Module::set_version("comment", $version = 4);
    }

    if ($version == 4) {
      Module::set_var("comment", "rss_visible", "all");
      Module::set_version("comment", $version = 5);
    }

    // In version 5 we accidentally set the installer variable to rss_available when it should
    // have been rss_visible.  Migrate it over now, if necessary.
    if ($version == 5) {
      if (!Module::get_var("comment", "rss_visible")) {
        Module::set_var("comment", "rss_visible", Module::get_var("comment", "rss_available"));
      }
      Module::clear_var("comment", "rss_available");
      Module::set_version("comment", $version = 6);
    }

    // In version 6 we accidentally left the install value of "rss_visible" to "both" when it
    // should have been "all"
    if ($version == 6) {
      if (Module::get_var("comment", "rss_visible") == "both") {
        Module::set_var("comment", "rss_visible", "all");
      }
      Module::set_version("comment", $version = 7);
    }

    if ($version == 7) {
      // In version 8 we started using SERVER_NAME instead of HTTP_HOST to be more robust
      // against potential XSS attacks.
      $db->query(Database::ALTER,
        "ALTER TABLE {comments} CHANGE `server_http_host` `server_name` varchar(64) default NULL");
      Module::set_version("comment", $version = 8);
    }
  }

  static function uninstall() {
    $db = Database::instance();

    // Notify listeners that we're deleting some data.  This is probably going to be very
    // inefficient for large uninstalls, and we could make it better by doing things like passing
    // a SQL fragment through so that the listeners could use subselects.  But by using a single,
    // simple event API we lighten the load on module developers.
    foreach (ORM::factory("Item")
             ->join("comments", "item.id", "comment.item_id")
             ->find_all() as $item) {
      Module::event("item_related_update", $item);
    }
    $db->query(Database::DROP, "DROP TABLE IF EXISTS {comments};");
  }
}
