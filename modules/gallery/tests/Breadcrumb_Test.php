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
class Breadcrumb_Test extends Unittest_TestCase {
  protected $album;
  protected $item;

  public function test_build_breadcrumbs_for_item() {
    $album = Test::random_album();
    $item = Test::random_photo($album);

    $expected = array();
    $expected[] = Breadcrumb::factory(
      Item::root()->title, Item::root()->url("show={$album->id}"))->set_first();
    $expected[] =
      Breadcrumb::factory($album->title, $album->url("show={$item->id}"));
    $expected[] = Breadcrumb::factory($item->title, $item->url())->set_last();
    $this->assertEquals($expected, Breadcrumb::array_from_item_parents($item));
  }
}
