<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
class Dynamic_Controller extends Controller {
  public function updates() {
    print $this->_show("updates");
  }

  public function popular() {
    print $this->_show("popular");
  }

  private function _show($album) {
    $page_size = module::get_var("gallery", "page_size", 9);
    $page = $this->input->get("page", "1");

    $album_defn = unserialize(module::get_var("dynamic", $album));
    $children_count = $album_defn->limit;
    if (empty($children_count)) {
      $children_count = ORM::factory("item")
        ->viewable()
        ->where("type !=", "album")
        ->count_all();
    }

    $offset = ($page-1) * $page_size;

    $max_pages = ceil($children_count / $page_size);

    // Make sure that the page references a valid offset
    if ($page < 1 || ($children_count && $page > ceil($children_count / $page_size))) {
      Kohana::show_404();
    }

    $template = new Theme_View("page.html", "dynamic");
    $template->set_global("page_size", $page_size);
    $template->set_global("children", ORM::factory("item")
                          ->viewable()
                          ->where("type !=", "album")
                          ->orderby($album_defn->key_field, "DESC")
                          ->find_all($page_size, $offset));
    $template->set_global("children_count", $children_count);
    $template->content = new View("dynamic.html");
    $template->content->title = t($album_defn->title);

    print $template;
  }

}