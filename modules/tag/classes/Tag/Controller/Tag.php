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
class Tag_Controller_Tag extends Controller {
  public function action_show() {
    $tag_id = $this->arg_required(0, "digit");
    $tag = ORM::factory("Tag", $tag_id);
    if (!$tag->loaded()) {
      throw HTTP_Exception::factory(404);
    }

    $page_size = Module::get_var("gallery", "page_size", 9);
    $show = Request::current()->query("show");

    if ($show) {
      $child = ORM::factory("Item", $show);
      $index = Tag::get_position($tag, $child);
      if ($index) {
        $page = ceil($index / $page_size);
        $uri = "tag/$tag_id/" . urlencode($tag->name);
        $this->redirect($uri . ($page == 1 ? "" : "?page=$page"));
      }
    } else {
      $page = (int) Arr::get(Request::current()->query(), "page", "1");
    }

    $children_count = $tag->items_count();
    $offset = ($page-1) * $page_size;
    $max_pages = max(ceil($children_count / $page_size), 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      $this->redirect(URL::query(array("page" => 1)));
    } else if ($page > $max_pages) {
      $this->redirect(URL::query(array("page" => $max_pages)));
    }

    $root = Item::root();
    $template = new View_Theme("required/page.html", "collection", "tag");
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
    $template->content = new View("required/dynamic.html");
    $template->content->title = t("Tag: %tag_name", array("tag_name" => $tag->name));
    $this->response->body($template);

    Item::set_display_context_callback("Controller_Tag::get_display_context", $tag->id);
  }

  public function action_find_by_name() {
    $tag_name = $this->arg_required(0);
    $tag = ORM::factory("Tag")->where("name", "=", $tag_name)->find();
    if (!$tag->loaded()) {
      // No matching tag was found. If this was an imported tag, this is probably a bug.
      // If the user typed the URL manually, it might just be wrong.
      throw HTTP_Exception::factory(404);
    }
    // We have a matching tag, but this is not the canonical URL - redirect them.
    $this->redirect($tag->abs_url(), 301);
  }

  public static function get_display_context($item, $tag_id) {
    $tag = ORM::factory("Tag", $tag_id);
    $where = array(array("type", "!=", "album"));

    $position = Tag::get_position($tag, $item, $where);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) = $tag->items(3, $position - 2, $where);
    } else {
      $previous_item = null;
      list ($next_item) = $tag->items(1, $position, $where);
    }

    $root = Item::root();
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
