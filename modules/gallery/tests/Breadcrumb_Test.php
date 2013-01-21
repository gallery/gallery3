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
class Breadcrumb_Test extends Gallery_Unit_Test_Case {
  private $album;
  private $item;

  public function build_breadcrumbs_for_item_test() {
    $album = test::random_album();
    $item = test::random_photo($album);

    $expected = array();
    $expected[] = Breadcrumb::instance(
      item::root()->title, item::root()->url("show={$album->id}"))->set_first();
    $expected[] =
      Breadcrumb::instance($album->title, $album->url("show={$item->id}"));
    $expected[] = Breadcrumb::instance($item->title, $item->url())->set_last();
    $this->assert_equal($expected, Breadcrumb::array_from_item_parents($item));
  }
}