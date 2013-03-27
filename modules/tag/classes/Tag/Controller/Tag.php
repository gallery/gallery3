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

    $root = item::root();
    $template = new Theme_View("page.html", "collection", "tag");
    $template->set_global(
      array("page" => $page,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "tag" => $tag,
            "children" => $tag->items($page_size, $offset),
            "breadcrumbs" => array(
              Breadcrumb::instance($root->title, $root->url())->set_first(),
              Breadcrumb::instance(t("Tag: %tag_name", array("tag_name" => $tag->name)),
                                   $tag->url())->set_last()),
            "children_count" => $children_count));
    $template->content = new View("dynamic.html");
    $template->content->title = t("Tag: %tag_name", array("tag_name" => $tag->name));
    print $template;

    item::set_display_context_callback("Tag_Controller::get_display_context", $tag->id);
  }

  static function get_display_context($item, $tag_id) {
    $tag = ORM::factory("tag", $tag_id);
    $where = array(array("type", "!=", "album"));

    $position = tag::get_position($tag, $item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) = $tag->items(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $tag->items(1, $position, $where);
    }

    $root = item::root();
    return array("position" => $position,
                 "previous_item" => $previous_item,
                 "next_item" => $next_item,
                 "sibling_count" => $tag->items_count($where),
                 "siblings_callback" => array(array($tag, "items"), array()),
                 "breadcrumbs" => array(
                   Breadcrumb::instance($root->title, $root->url())->set_first(),
                   Breadcrumb::instance(t("Tag: %tag_name", array("tag_name" => $tag->name)),
                                        $tag->url("show={$item->id}")),
                   Breadcrumb::instance($item->title, $item->url())->set_last()));
  }
}
