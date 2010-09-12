<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
    $tag_name = $function;
    $tag = ORM::factory("tag")->where("name", "=", $tag_name)->find();
    $page_size = module::get_var("gallery", "page_size", 9);
    $page = (int) Input::instance()->get("page", "1");
    $children_count = $tag->items_count();
    $offset = ($page-1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      url::redirect($album->abs_url());
    } else if ($page > $max_pages) {
      url::redirect($album->abs_url("page=$max_pages"));
    }

    $template = new Theme_View("page.html", "collection", "tag");
    $template->set_global("page", $page);
    $template->set_global("max_pages", $max_pages);
    $template->set_global("page_size", $page_size);
    $template->set_global("tag", $tag);
    $template->set_global("children", $tag->items($page_size, $offset));
    $template->set_global("children_count", $children_count);
    $template->content = new View("dynamic.html");
    $template->content->title = t("Tag: %tag_name", array("tag_name" => $tag->name));

    print $template;
  }
}
