<?php defined("SYSPATH") or die("No direct script access.");
/**
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
class Search_Controller extends Controller {
  public function index() {
    $page_size = module::get_var("core", "page_size", 9);
    $q = $this->input->get("q");
    $page = $this->input->get("page", 1);
    $offset = ($page - 1) * $page_size;

    // Make sure that the page references a valid offset
    if ($page < 1) {
      $page = 1;
    }

    list ($count, $result) = search::search($q, $page_size, $offset);
    $template = new Theme_View("page.html", "search");
    $template->set_global("page_size", $page_size);
    $template->set_global("children_count", $count);

    $template->content = new View("search.html");
    $template->content->items = $result;
    $template->content->q = $q;

    print $template;
  }
}
