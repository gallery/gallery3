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
class Info_Hook_InfoBlock {
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
        $block->content = new View("info/block.html");
        if ($theme->item->title && Module::get_var("info", "show_title")) {
          $info["title"] = array(
            "label" => t("Title:"),
            "value" => HTML::purify($theme->item->title)
          );
        }
        if ($theme->item->description && Module::get_var("info", "show_description")) {
          $info["description"] = array(
            "label" => t("Description:"),
            "value" => nl2br(HTML::purify($theme->item->description))
          );
        }
        if (!$theme->item->is_album() && Module::get_var("info", "show_name")) {
          $info["file_name"] = array(
            "label" => t("File name:"),
            "value" => HTML::clean($theme->item->name)
          );
        }
        if ($theme->item->captured && Module::get_var("info", "show_captured")) {
          $info["captured"] = array(
            "label" => t("Captured:"),
            "value" => Gallery::date_time($theme->item->captured)
          );
        }
        if ($theme->item->owner && Module::get_var("info", "show_owner")) {
          $display_name = $theme->item->owner->display_name();
          if ($theme->item->owner->url) {
            $info["owner"] = array(
              "label" => t("Owner:"),
              "value" => HTML::anchor(
                HTML::clean($theme->item->owner->url),
                HTML::clean($display_name))
            );
          } else {
            $info["owner"] = array(
              "label" => t("Owner:"),
              "value" => HTML::clean($display_name)
            );
          }
        }
        if (($theme->item->width && $theme->item->height) &&
            Module::get_var("info", "show_dimensions")) {
            $info["size"] = array(
                "label" => t("Dimensions:"),
                "value" => t(
                  "%width x %height px",
                  array("width" => $theme->item->width, "height" => $theme->item->height))
            );
        }

        $block->content->metadata = $info;

        Module::event("info_block_get_metadata", $block, $theme->item);
      }
      break;
    }
    return $block;
  }
}
