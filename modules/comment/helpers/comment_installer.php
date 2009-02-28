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
class comment_installer {
  static function install() {
    $db = Database::instance();
    $version = module::get_version("comment");

    if ($version == 0) {
      $db->query("CREATE TABLE IF NOT EXISTS {comments} (
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
                   `server_http_host` varchar(64) default NULL,
                   `server_http_referer` varchar(255) default NULL,
                   `server_http_user_agent` varchar(128) default NULL,
                   `server_query_string` varchar(64) default NULL,
                   `server_remote_addr` varchar(32) default NULL,
                   `server_remote_host` varchar(64) default NULL,
                   `server_remote_port` varchar(16) default NULL,
                   `state` char(15) default 'unpublished',
                   `text` text,
                   `updated` int(9) NOT NULL,
                 PRIMARY KEY (`id`))
                 ENGINE=InnoDB DEFAULT CHARSET=utf8;");


      block_manager::add("dashboard_center", "comment", "recent_comments");
      module::set_var("comment", "spam_caught", 0);
      module::set_version("comment", 1);
    }
  }

  static function uninstall() {
    $db = Database::instance();
    $sql = "SELECT `item_id` FROM {comments}";
    module::event("item_related_update_batch", $sql);

    $db->query("DROP TABLE IF EXISTS {comments]
`;");
    module::delete("comment");
  }
}
