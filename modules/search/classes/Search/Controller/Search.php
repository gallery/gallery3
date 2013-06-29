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
class Search_Controller_Search extends Controller {
  public function action_index() {
    $page_size = Module::get_var("gallery", "page_size", 9);
    $q = $this->request->query("q");
    $show = $this->request->query("show");

    $album_id = Arr::get($this->request->query(), "album", Item::root()->id);
    $album = ORM::factory("Item", $album_id);
    if (!Access::can("view", $album) || !$album->is_album()) {
      $album = Item::root();
    }

    if ($show) {
      $child = ORM::factory("Item", $show);
      $index = Search::get_position_within_album($child, $q, $album);
      if ($index) {
        $page = ceil($index / $page_size);
        $this->redirect(URL::abs_site("search" .
          "?q=" . urlencode($q) .
          "&album=" . urlencode($album->id) .
          ($page == 1 ? "" : "&page=$page")));
      }
    }

    $page = Arr::get($this->request->query(), "page", 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      $page = 1;
    }

    $offset = ($page - 1) * $page_size;

    list ($count, $result) = Search::search_within_album($q, $album, $page_size, $offset);

    $max_pages = max(ceil($count / $page_size), 1);

    $template = new View_Theme("required/page.html", "collection", "search");
    $root = Item::root();
    $template->set_global(
      array("page" => $page,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "breadcrumbs" => array(
              Breadcrumb::instance($root->title, $root->url())->set_first(),
              Breadcrumb::instance($q, URL::abs_site("search?q=" . urlencode($q)))->set_last(),
            ),
            "children_count" => $count));

    $template->content = new View("search/results.html");
    $template->content->album = $album;
    $template->content->items = $result;
    $template->content->q = $q;

    $this->response->body($template);

    Item::set_display_context_callback("Controller_Search::get_display_context", $album, $q);
  }

  public static function get_display_context($item, $album, $q) {
    $position = Search::get_position_within_album($item, $q, $album);

    if ($position > 1) {
      list ($count, $result_data) = Search::search_within_album($q, $album, 3, $position - 2);
      list ($previous_item, $ignore, $next_item) = $result_data;
    } else {
      $previous_item = null;
      list ($count, $result_data) = Search::search_within_album($q, $album, 1, $position);
      list ($next_item) = $result_data;
    }

    $search_url = URL::abs_site("search" .
      "?q=" . urlencode($q) .
      "&album=" . urlencode($album->id) .
      "&show={$item->id}");
    $root = Item::root();

    return array("position" => $position,
                 "previous_item" => $previous_item,
                 "next_item" => $next_item,
                 "sibling_count" => $count,
                 "siblings_callback" => array("Controller_Search::get_siblings", array($q, $album)),
                 "breadcrumbs" => array(
                   Breadcrumb::instance($root->title, $root->url())->set_first(),
                   Breadcrumb::instance(t("Search: %q", array("q" => $q)), $search_url),
                   Breadcrumb::instance($item->title, $item->url())->set_last()));
  }

  public static function get_siblings($q, $album, $limit, $offset) {
    if (!isset($limit)) {
      $limit = 100;
    }
    if (!isset($offset)) {
      $offset = 1;
    }
    $result = Search::search_within_album($q, $album, $limit, $offset);
    return $result[1];
  }
}
