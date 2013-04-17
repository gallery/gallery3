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
class Inflector_Test extends Unittest_Testcase {
  public function convert_module_to_class_name_test() {
    $this->assert_equal("Gallery",    Inflector::convert_module_to_class_name("gallery"));
    $this->assert_equal("FooBar",     Inflector::convert_module_to_class_name("foo_bar"));
    $this->assert_equal("G2Import",   Inflector::convert_module_to_class_name("g2_import"));
    $this->assert_equal("M4vModule",  Inflector::convert_module_to_class_name("m4v_module"));
    $this->assert_equal("JSBach",     Inflector::convert_module_to_class_name("j_s_bach"));
    $this->assert_equal("OrmExample", Inflector::convert_module_to_class_name("orm_example"));
  }

  public function convert_class_to_module_name_test() {
    $this->assert_equal("gallery",     Inflector::convert_class_to_module_name("Gallery"));
    $this->assert_equal("foo_bar",     Inflector::convert_class_to_module_name("FooBar"));
    $this->assert_equal("g2_import",   Inflector::convert_class_to_module_name("G2Import"));
    $this->assert_equal("m4v_module",  Inflector::convert_class_to_module_name("M4vModule"));
    $this->assert_equal("j_s_bach",    Inflector::convert_class_to_module_name("JSBach"));
    $this->assert_equal("orm_example", Inflector::convert_class_to_module_name("OrmExample"));
  }
}