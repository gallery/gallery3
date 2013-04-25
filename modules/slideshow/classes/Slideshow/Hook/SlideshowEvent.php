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
class Slideshow_Hook_SlideshowEvent {
  static function pre_deactivate($data) {
    if ($data->module == "rss") {
      $data->messages["warn"][] = t("The Slideshow module requires the RSS module.");
    }
  }

  static function module_change($changes) {
    if (!Module::is_active("rss") || in_array("rss", $changes->deactivate)) {
      SiteStatus::warning(
        t("The Slideshow module requires the RSS module.  <a href=\"%url\">Activate the RSS module now</a>",
          array("url" => HTML::mark_clean(URL::site("admin/modules")))),
        "slideshow_needs_rss");
    } else {
      SiteStatus::clear("slideshow_needs_rss");
    }
  }

  static function album_menu($menu, $theme) {
    $max_scale = Module::get_var("slideshow", "max_scale");
    if ($theme->item()->descendants_count(array(array("type", "=", "photo")))) {
      $menu->append(Menu::factory("link")
                    ->id("slideshow")
                    ->label(t("View slideshow"))
                    ->url("javascript:cooliris.embed.show(" .
                          "{maxScale:$max_scale,feed:'" . self::_feed_url($theme) . "'})")
                    ->css_id("g-slideshow-link"));
    }
  }

  static function photo_menu($menu, $theme) {
    $max_scale = Module::get_var("slideshow", "max_scale");
    $menu->append(Menu::factory("link")
                  ->id("slideshow")
                  ->label(t("View slideshow"))
                  ->url("javascript:cooliris.embed.show(" .
                        "{maxScale:$max_scale,feed:'" . self::_feed_url($theme) . "'})")
                  ->css_id("g-slideshow-link"));
  }

  static function tag_menu($menu, $theme) {
    $max_scale = Module::get_var("slideshow", "max_scale");
    $menu->append(Menu::factory("link")
                  ->id("slideshow")
                  ->label(t("View slideshow"))
                  ->url("javascript:cooliris.embed.show(" .
                        "{maxScale:$max_scale,feed:'" . self::_feed_url($theme) . "'})")
                  ->css_id("g-slideshow-link"));
  }

  protected static function _feed_url($theme) {
    if ($item = $theme->item()) {
      if (!$item->is_album()) {
        $item = $item->parent();
      }
      return Rss::url("gallery/album/{$item->id}");
    } else {
      return Rss::url("tag/tag/{$theme->tag()->id}");
    }
  }
}
