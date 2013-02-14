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
class Search_Controller extends Controller {
  public function index() {
    $page_size = module::get_var("gallery", "page_size", 9);
    $q = Input::instance()->get("q");
    $q_with_more_terms = search::add_query_terms($q);
    $show = Input::instance()->get("show");

    $album_id = Input::instance()->get("album", item::root()->id);
    $album = ORM::factory("item", $album_id);
    if (!access::can("view", $album) || !$album->is_album()) {
      $album = item::root();
    }

    if ($show) {
      $child = ORM::factory("item", $show);
      $index = search::get_position_within_album($child, $q_with_more_terms, $album);
      if ($index) {
        $page = ceil($index / $page_size);
        url::redirect(url::abs_site("search" .
          "?q=" . urlencode($q) .
          "&album=" . urlencode($album->id) .
          ($page == 1 ? "" : "&page=$page")));
      }
    }

    $page = Input::instance()->get("page", 1);

    // Make sure that the page references a valid offset
    if ($page < 1) {
      $page = 1;
    }

    $offset = ($page - 1) * $page_size;

    list ($count, $result) =
      search::search_within_album($q_with_more_terms, $album, $page_size, $offset);

    $max_pages = max(ceil($count / $page_size), 1);

    $template = new Theme_View("page.html", "collection", "search");
    $root = item::root();
    $template->set_global(
      array("page" => $page,
            "max_pages" => $max_pages,
            "page_size" => $page_size,
            "breadcrumbs" => array(
              Breadcrumb::instance($root->title, $root->url())->set_first(),
              Breadcrumb::instance($q, url::abs_site("search?q=" . urlencode($q)))->set_last(),
            ),
            "children_count" => $count));

    $template->content = new View("search.html");
    $template->content->album = $album;
    $template->content->items = $result;
    $template->content->q = $q;

    print $template;

    item::set_display_context_callback("Search_Controller::get_display_context", $album, $q);
  }

  static function get_display_context($item, $album, $q) {
    $q_with_more_terms = search::add_query_terms($q);
    $position = search::get_position_within_album($item, $q_with_more_terms, $album);

    if ($position > 1) {
      list ($count, $result_data) =
        search::search_within_album($q_with_more_terms, $album, 3, $position - 2);
      list ($previous_item, $ignore, $next_item) = $result_data;
    } else {
      $previous_item = null;
      list ($count, $result_data) =
        search::search_within_album($q_with_more_terms, $album, 1, $position);
      list ($next_item) = $result_data;
    }

    $search_url = url::abs_site("search" .
      "?q=" . urlencode($q) .
      "&album=" . urlencode($album->id) .
      "&show={$item->id}");
    $root = item::root();

    return array("position" => $position,
                 "previous_item" => $previous_item,
                 "next_item" => $next_item,
                 "sibling_count" => $count,
                 "siblings_callback" => array("Search_Controller::get_siblings", array($q, $album)),
                 "breadcrumbs" => array(
                   Breadcrumb::instance($root->title, $root->url())->set_first(),
                   Breadcrumb::instance(t("Search: %q", array("q" => $q)), $search_url),
                   Breadcrumb::instance($item->title, $item->url())->set_last()));
  }

  static function get_siblings($q, $album, $limit, $offset) {
    if (!isset($limit)) {
      $limit = 100;
    }
    if (!isset($offset)) {
      $offset = 1;
    }
    $result = search::search_within_album(search::add_query_terms($q), $album, $limit, $offset);
    return $result[1];
  }
}
