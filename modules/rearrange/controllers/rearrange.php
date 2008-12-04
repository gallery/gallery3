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
class Rearrange_Controller extends Controller {

  public function show($id=null) {
    $view = new View("rearrange_item_list.html");

    $isRoot = empty($id);
    $item = ORM::factory("item", $isRoot ? 1 : $id);

    $view->children = $isRoot ? array($item) : $item->children();

    print $view;
  }

  public function move($source_id, $target_id) {
    $source = ORM_MPTT::factory("item", $source_id);
    $target = ORM_MPTT::factory("item", $target_id);

    try {
      $source->moveTo($target);
      print "success";
    } catch (Exception $e) {
      Kohana::log("error", $e->getMessage() . "\n" + $e->getTraceAsString());
      header("HTTP/1.1 500");
      print  $e->getMessage();
    }
  }

}