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
class info_block_Core {
  static function get_site_list() {
    return array("metadata" => t("Metadata"));
  }

  static function get($block_id, $theme) {
    $block = "";
    switch ($block_id) {
    case "metadata":
      if ($theme->item()) {
        $block = new Block();
        $block->css_id = "g-metadata";
        $block->title = $theme->item()->is_album() ? t("Album info") :
          ($theme->item()->is_movie() ? t("Movie info") : t("Photo info"));
        $block->content = new View("info_block.html");
        if ($theme->item->title && module::get_var("info", "show_title")) {
          $info["title"] = array(
            "label" => t("Title:"),
            "value" => html::purify($theme->item->title)
          );
        }
        if ($theme->item->description && module::get_var("info", "show_description")) {
          $info["description"] = array(
            "label" => t("Description:"),
            "value" => nl2br(html::purify($theme->item->description))
          );
        }
        if (!$theme->item->is_album() && module::get_var("info", "show_name")) {
          $info["file_name"] = array(
            "label" => t("File name:"),
            "value" => html::clean($theme->item->name)
          );
        }
        if ($theme->item->captured && module::get_var("info", "show_captured")) {
          $info["captured"] = array(
            "label" => t("Captured:"),
            "value" => gallery::date_time($theme->item->captured)
          );
        }
        if ($theme->item->owner && module::get_var("info", "show_owner")) {
          $display_name = $theme->item->owner->display_name();
          if ($theme->item->owner->url) {
            $info["owner"] = array(
              "label" => t("Owner:"),
              "value" => html::anchor(
                html::clean($theme->item->owner->url),
                html::clean($display_name))
            );
          } else {
            $info["owner"] = array(
              "label" => t("Owner:"),
              "value" => html::clean($display_name)
            );
          }
        }
        $block->content->metadata = $info;

        module::event("info_block_get_metadata", $block, $theme->item);
      }
      break;
    }
    return $block;
  }
}