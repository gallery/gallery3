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
class Organize_Controller extends Controller {
  private static $_MICRO_THUMB_SIZE = 90;
  private static $_MICRO_THUMB_PADDING = 5;

  public function index($item_id=1) {
    $item = ORM::factory("item", $item_id);
    $root = ($item->id == 1) ? $item : ORM::factory("item", 1);

    $v = new View("organize.html");
    $v->root = $root;
    $v->item = $item;
    $v->album_tree = $this->tree($item, $root);

    $v->edit_form = new View("organize_edit.html");
    $v->edit_form->button_pane = new View("organize_button_pane.html");
 
    print $v;
  }

  public function content($item_id) {
    $item = ORM::factory("item", $item_id);
    $width = $this->input->get("width");
    $height = $this->input->get("height");
    $offset = $this->input->get("offset", 0);
    $thumbsize = self::$_MICRO_THUMB_SIZE + 2 * self::$_MICRO_THUMB_PADDING;
    $page_size = ceil($width / $thumbsize) * ceil($height / $thumbsize);
 
    $v = new View("organize_thumb_grid.html");
    $v->children = $item->children($page_size, $offset);
    $v->thumbsize = self::$_MICRO_THUMB_SIZE;
    $v->padding = self::$_MICRO_THUMB_PADDING;
    $v->offset = $offset;

    print json_encode(array("count" => $v->children->count(),
                            "data" => $v->__toString()));
  }

  public function header($item_id) {
    $item = ORM::factory("item", $item_id);

    print json_encode(array("title" => $item->title,
                            "description" => empty($item->description) ? "" : $item->description));
  }

  public function tree($item, $parent) {
    $albums = ORM::factory("item")
      ->where(array("parent_id" => $parent->id, "type" => "album"))
      ->orderby(array("title" => "ASC"))
      ->find_all();

    $v = new View("organize_album.html");
    $v->album = $parent;
    $v->selected = $parent->id == $item->id;
    
    if ($albums->count()) {
      $v->album_icon = $parent->id == 1 || $v->selected ? "ui-icon-minus" : "ui-icon-plus";
    } else {
      $v->album_icon = "";
    }

    $v->children = "";
    foreach ($albums as $album) {
      $v->children .= $this->tree($item, $album);
    }
    return $v->__toString();
  }
}