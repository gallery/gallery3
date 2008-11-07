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
class Album_Controller extends Template_Controller {
  public $template = "page.html";

  public function View($id) {
    $item = ORM::factory("item")->where("id", $id)->find();
    if (empty($item->id)) {
      return Kohana::show_404();
    }

    /** @todo: these need to be pulled from the database */
    $theme_name = "default";
    $page_size = 9;

    $page = $this->input->get("page", "1");
    $theme = new Theme($theme_name, $this->template);

    $this->template->content = new View("album.html");
    $this->template->set_global('page_size', $page_size);
    $this->template->set_global('item', $item);
    $this->template->set_global('children', $item->children($page_size, ($page-1) * $page_size));
    $this->template->set_global('parents', $item->parents());
    $this->template->set_global('theme', $theme);

    /** @todo: move this up to a base class */
    if (Session::instance()->get("use_profiler", false)) {
      $profiler = new Profiler();
      print $profiler->render();
    }
  }
}
