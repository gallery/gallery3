<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
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
class Breadcrumb_Test extends Gallery_Unit_Test_Case {
  private $album;
  private $item;

  public function setup() {
    $this->album = test::random_album();
    $this->item = test::random_photo($this->album);
    $this->album->reload();
  }

  public function teardown() {
    $this->album = null;
    $this->item = null;
  }

  public function build_breadcrumbs_for_item_test() {
    $breadcrumbs = Breadcrumb::for_item($this->item);
    $this->assert_equal("Gallery", $breadcrumbs[0]->title());
    $this->assert_equal($this->album->title, $breadcrumbs[1]->title());
    $this->assert_equal($this->item->title, $breadcrumbs[2]->title());
  }

  public function build_breadcrumbs_from_items_test() {
    $breadcrumbs = Breadcrumb::build(
      Breadcrumb::instance(item::root()->title, "/")->id(item::root()->id),
      Breadcrumb::instance($this->album->title, $this->album->relative_path())->id($this->album->id),
      Breadcrumb::instance($this->item->title, $this->item->relative_path())->id($this->item->id));
    $this->assert_equal("Gallery", $breadcrumbs[0]->title());
    $this->assert_equal($this->album->title, $breadcrumbs[1]->title());
    $this->assert_equal($this->item->title, $breadcrumbs[2]->title());
  }
}