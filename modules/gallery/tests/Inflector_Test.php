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
class Inflector_Test extends Unittest_TestCase {
  public function test_convert_module_to_class_name() {
    $this->assertEquals("Gallery",    Inflector::convert_module_to_class_name("gallery"));
    $this->assertEquals("FooBar",     Inflector::convert_module_to_class_name("foo_bar"));
    $this->assertEquals("G2Import",   Inflector::convert_module_to_class_name("g2_import"));
    $this->assertEquals("M4vModule",  Inflector::convert_module_to_class_name("m4v_module"));
    $this->assertEquals("JSBach",     Inflector::convert_module_to_class_name("j_s_bach"));
    $this->assertEquals("OrmExample", Inflector::convert_module_to_class_name("orm_example"));
  }

  public function test_convert_class_to_module_name() {
    $this->assertEquals("gallery",     Inflector::convert_class_to_module_name("Gallery"));
    $this->assertEquals("foo_bar",     Inflector::convert_class_to_module_name("FooBar"));
    $this->assertEquals("g2_import",   Inflector::convert_class_to_module_name("G2Import"));
    $this->assertEquals("m4v_module",  Inflector::convert_class_to_module_name("M4vModule"));
    $this->assertEquals("j_s_bach",    Inflector::convert_class_to_module_name("JSBach"));
    $this->assertEquals("orm_example", Inflector::convert_class_to_module_name("OrmExample"));
  }
}