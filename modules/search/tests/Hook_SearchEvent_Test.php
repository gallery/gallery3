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
class Hook_SearchEvent_Test extends Unittest_TestCase {
  public function teardown() {
    // Return settings to defaults
    Module::set_var("search", "wildcard_mode", "append_stem");
    Module::set_var("search", "short_search_fix", false);

    parent::teardown();
  }

  public function test_search_terms() {
    Module::set_var("search", "wildcard_mode", "none");
    Module::set_var("search", "short_search_fix", false);

    $terms = 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)';
    $expected = array(
      "boolean"          => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)',
      "natural_language" => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)',
      "index"            => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)'
    );

    $terms = Search::explode_fulltext_query($terms);
    foreach ($expected as $type => $value) {
      $test = new ArrayObject($terms);
      Hook_SearchEvent::search_terms($test, $type);
      $this->assertEquals($value, implode("", (array)$test));
    }
  }

  public function test_search_terms_with_wildcard_append() {
    Module::set_var("search", "wildcard_mode", "append");
    Module::set_var("search", "short_search_fix", false);

    $terms = 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)';
    $expected = array(
      "boolean"          => 'appendix*,bar* -cats* . +dog* >( entries* "foo grapes " houses*)',
      "natural_language" => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)',
      "index"            => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)'
    );

    $terms = Search::explode_fulltext_query($terms);
    foreach ($expected as $type => $value) {
      $test = new ArrayObject($terms);
      Hook_SearchEvent::search_terms($test, $type);
      $this->assertEquals($value, implode("", (array)$test));
    }
  }

  public function test_search_terms_with_wildcard_append_stem() {
    Module::set_var("search", "wildcard_mode", "append_stem");
    Module::set_var("search", "short_search_fix", false);

    $terms = 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)';
    $expected = array(
      "boolean"          => 'appendi*,bar* -cat* . +dog* >( entr* "foo grapes " houses*)',
      "natural_language" => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)',
      "index"            => 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)'
    );

    $terms = Search::explode_fulltext_query($terms);
    foreach ($expected as $type => $value) {
      $test = new ArrayObject($terms);
      Hook_SearchEvent::search_terms($test, $type);
      $this->assertEquals($value, implode("", (array)$test));
    }
  }

  public function test_search_terms_with_short_search_fix() {
    Module::set_var("search", "wildcard_mode", "none");
    Module::set_var("search", "short_search_fix", true);
    Module::set_var("search", "short_search_prefix", "1Z");

    $terms = 'appendix,bar -cats . +dog* >( entries "foo grapes " houses*)';
    $expected = array(
      "boolean"          => '1Zappendix,1Zbar -1Zcats . +1Zdog* >( 1Zentries "1Zfoo 1Zgrapes " 1Zhouses*)',
      "natural_language" => '1Zappendix,1Zbar -1Zcats . +1Zdog* >( 1Zentries "1Zfoo 1Zgrapes " 1Zhouses*)',
      "index"            => '1Zappendix,1Zbar -1Zcats . +1Zdog* >( 1Zentries "1Zfoo 1Zgrapes " 1Zhouses*)'
    );

    $terms = Search::explode_fulltext_query($terms);
    foreach ($expected as $type => $value) {
      $test = new ArrayObject($terms);
      Hook_SearchEvent::search_terms($test, $type);
      $this->assertEquals($value, implode("", (array)$test));
    }
  }
}
