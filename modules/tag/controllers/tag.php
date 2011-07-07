<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Tag_Controller extends Controller {
  public function __call($function, $args) {
    $tag_id = $function;
    $tag = ORM::factory("tag")->where("id", "=", $tag_id)->find();
    $page_size = module::get_var("gallery", "page_size", 9);

    $input = Input::instance();
    $show = $input->get("show");

    if ($show) {
      $child = ORM::factory("item", $show);
      $index = tag::get_position($tag, $child);
      if ($index) {
        $page = ceil($index / $page_size);
        $uri = "tag/$tag_id/" . urlencode($tag->name);
        url::redirect($uri . ($page == 1 ? "" : "?page=$page"));
      }
    } else {
      $page = (int) $input->get("page", "1");
    }

    $children_count = $tag->items_count();
    $offset = ($page-1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      url::redirect(url::merge(array("page" => 1)));
    } else if ($page > $max_pages) {
      url::redirect(url::merge(array("page" => $max_pages)));
    }

    $title = t("Tag: %tag_name", array("tag_name" => $tag->name));
    Display_Context::factory()
      ->set_context_callback("tag::get_display_context")
      ->set_data(array("tag" => $tag,
                       "title" => $title))
      ->save();

    $template = new Theme_View("page.html", "collection", "tag");
    $template->set_global(array("page" => $page,
                                "max_pages" => $max_pages,
                                "page_size" => $page_size,
                                "tag" => $tag,
                                "children" => $tag->items($page_size, $offset),
                                "children_count" => $children_count));
    $template->content = new View("dynamic.html");
    $template->content->title = $title;

    print $template;
  }
}
