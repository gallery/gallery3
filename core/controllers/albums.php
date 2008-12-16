<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Albums_Controller extends Items_Controller {

  /**
   *  @see Rest_Controller::_show($resource)
   */
  public function _show($item) {
    if (!access::can("view", $item)) {
      return Kohana::show_404();
    }

    $theme_name = module::get_var("core", "active_theme", "default");
    $page_size = module::get_var("core", "page_size", 9);

    $template = new Theme_View("page.html", "album", $theme_name);

    $page = $this->input->get("page", "1");

    $template->set_global('page_size', $page_size);
    $template->set_global('item', $item);

    // Make sure that the page references a valid offset
    $children_count = $item->children_count();
    while (($offset = ($page - 1) * $page_size) > $children_count && $page != 1) {
      $page--;
    }
    $template->set_global('children', $item->children($page_size, $offset));
    $template->set_global('children_count', $children_count);
    $template->set_global('parents', $item->parents());
    $template->content = new View("album.html");

    print $template;
  }

  /**
   *  @see Rest_Controller::_form_add($parameters)
   */
  public function _form_add($parent_id) {
    $parent = ORM::factory("item", $parent_id);

    print album::get_add_form($parent)->render();
  }

}
