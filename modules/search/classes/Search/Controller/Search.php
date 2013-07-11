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

    $template = new View_Theme("required/page.html", "collection", "search");
    $template->set_global(array(
      "collection_query_callback" => array("Search::get_search_query", array($q, $album)),
      "breadcrumbs_callback"      => array("Search::get_breadcrumbs",  array($q, $album)),
      "collection_order_by"       => array("score" => "DESC", "id" => "ASC")
    ));
    $template->init_collection();

    $template->content = new View("search/results.html");
    $template->content->album = $album;
    $template->content->q = $q;

    $this->response->body($template);
  }
}
