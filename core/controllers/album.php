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
    $this->template->item = $item;

    $this->template->header = new View("page_header.html");
    $this->template->header->item = $item;

    $this->template->footer = new View("page_footer.html");
    $this->template->footer->item = $item;

    $this->template->content = new View("album.html");
    $this->template->content->item = $item;
    $this->template->content->maxRows = 3;
    $this->template->content->maxColumns = 3;

    $this->template->sidebar = new View("page_sidebar.html");
    $this->template->sidebar->item = $item;

  }
}
