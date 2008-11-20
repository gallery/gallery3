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
class Media_RSS_Controller extends Controller {
  public static $LIMIT = 10;
  public function feed($id) {
    $item = ORM::factory("item", $id)->find();
    if (!$item->loaded) {
      return Kohana::show_404();
    }

    $view = new View("feed.mrss");
    $view->item = $item;

    $offset = $this->input->get("offset", 0);
    $view->children = $item->decendents(Media_RSS_Controller::$LIMIT, $offset, "photo");

    if (!empty($offset)) {
      $view->prevOffset = $offset - Media_RSS_Controller::$LIMIT;
    }
    if ($view->children->count() >= Media_RSS_Controller::$LIMIT) {
      $view->nextOffset = $offset + Media_RSS_Controller::$LIMIT;
    }

    header("Content-type: application/rss+xml");
    print $view;
  }
}