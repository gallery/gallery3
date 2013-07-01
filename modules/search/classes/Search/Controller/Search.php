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
    $q = $this->request->query("q");

    $album_id = Arr::get($this->request->query(), "album", Item::root()->id);
    $album = ORM::factory("Item", $album_id);
    if (!Access::can("view", $album) || !$album->is_album()) {
      $album = Item::root();
    }

    $root = Item::root();
    $template = new View_Theme("required/page.html", "collection", "search");
    $template->set_global(array(
      "children_query" => Search::search_query_base($q, $album),
      "children_order_by" => array("score" => "DESC", "id" => "ASC"),
      "breadcrumbs" => array(
        Breadcrumb::instance($root->title, $root->url())->set_first(),
        Breadcrumb::instance($q, URL::abs_site("search?q=" . urlencode($q)))->set_last())
    ));

    $template->content = new View("search/results.html");
    $template->content->album = $album;
    $template->content->q = $q;
    $template->init_paginator();

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
