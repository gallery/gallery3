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
class Search_Test extends Unittest_TestCase {
  public function teardown() {
    // Return settings to defaults
    Module::set_var("search", "short_search_fix", false);

    parent::teardown();
  }

  public function test_explode_fulltext_query() {
    $terms = 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)';
    $expected = array("", "appendix", ",", "bar", " -", "cats", " . +", "dog", "* >( ",
                      "entries", ' "', "foo", " ", "grapes", ' " ', "houses", "*)");
    $this->assertEquals($expected, Search::explode_fulltext_query($terms));
  }

  public function test_update_with_short_search_fix() {
    Module::set_var("search", "short_search_fix", true);
    Module::set_var("search", "short_search_prefix", "1Z");

    $name = Test::random_name();
    $item = Test::random_album_unsaved();
    $item->name = $name;
    $item->title = $name;
    $item->description = $name;
    $item->save();

    $this->assertEquals("1Z$name 1Z$name 1Z$name", $item->search_record->data);
  }
}
