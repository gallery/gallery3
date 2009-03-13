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
$config["methods"] = array(
  "theme" => array("album_blocks" => "",     "album_bottom" => "",
                   "album_top" => "",        "admin_credits" => "",
                   "admin_footer" => "",     "admin_header_top" => "",
                   "admin_header_bottom" => "", "admin_page_bottom" => "",
                   "admin_page_top" => "",   "admin_head" => "",
                   "credits" => "",          "dynamic_bottom" => "",
                   "dynamic_top" => "",      "footer" => "",
                   "head" => "",             "header_bottom" => "",
                   "header_top" => "",       "page_bottom" => "",
                   "page_top" => "",         "photo_blocks" => "",
                   "photo_bottom" => "",     "photo_top" => "",
                   "sidebar_blocks" => "",   "sidebar_bottom" => "",
                   "sidebar_top" => "",      "thumb_bottom" => "\$child",
                   "thumb_info" => "\$child",       "thumb_top" => "\$child"),
  "menu" => array("admin" => "\$menu, \$theme", "album" => "\$menu, \$theme",
                  "photo" => "\$menu, \$theme", "site" => "\$menu, \$theme"),
  "block" => array("get" => "\$block_id", "get_list" => ""),
  "event" => array("batch_complete" => "",  "comment_add_form" => "\$form",
                   "comment_created" => "\$theme, \$args", "comment_updated" => "\$old, \$new",
                   "group_before_delete" => "\$group", "group_created" => "\$group",
                   "item_before_delete" => "\$item", "item_created" => "\$item",
                   "item_related_update" => "\$item", "item_related_update_batch" => "\$sql",
                   "item_updated" => "\$old, \$new", "user_before_delete" => "\$user",
                   "user_created" => "\$user", "user_login" => "\$user",
                   "user_logout" => "\$user" ),
  "installer" => array("install" => "", "uninstall" => ""));