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
class Gallery_Inflector extends Kohana_Inflector {
  /**
   * Converts a module/model name to its corresponding class name (e.g. "foo_bar" --> "FooBar").
   * This is similar to Inflector::camelize($name) except that it capitalizes the first letter
   * and assumes the input has no \n\r\t\v, which lets it skip regex for efficiency.
   *
   *   $name = Inflector::convert_module_to_class_name("gallery");     // "Gallery"
   *   $name = Inflector::convert_module_to_class_name("foo_bar");     // "FooBar"
   *   $name = Inflector::convert_module_to_class_name("g2_import");   // "G2Import"
   *   $name = Inflector::convert_module_to_class_name("m4v_module");  // "M4vModule"
   *   $name = Inflector::convert_module_to_class_name("j_s_bach");    // "JSBach"
   *   $name = Inflector::convert_module_to_class_name("orm_example"); // "OrmExample"
   *
   * @param   string  $name  module_or_model_name
   * @return  string        ClassName
   */
  public static function convert_module_to_class_name($name) {
    return str_replace(" ", "", ucwords(strtolower(str_replace("_", " ", $name))));
  }

  /**
   * Converts a class name to its corresponding module/model name (e.g. "FooBar" --> "foo_bar").
   * This is similar to Inflector::decamelize($name, "_") except that it considers every capital
   * letter the start of a new word, even if it follows a number or another capital letter.  This
   * ensures that it's the reverse transformation of Inflector::module_to_class_name($name).
   *
   *   $name = Inflector::convert_class_to_module_name("Gallery");    // "gallery"
   *   $name = Inflector::convert_class_to_module_name("FooBar");     // "foo_bar"
   *   $name = Inflector::convert_class_to_module_name("G2Import");   // "g2_import"
   *   $name = Inflector::convert_class_to_module_name("M4vModule");  // "m4v_module"
   *   $name = Inflector::convert_class_to_module_name("JSBach");     // "j_s_bach"
   *   $name = Inflector::convert_class_to_module_name("OrmExample"); // "orm_example"
   *
   * @param   string  $name  ClassName
   * @return  string        module_or_model_name
   */
  public static function convert_class_to_module_name($name) {
    return trim(strtolower(preg_replace("/([A-Z])/", "_$1", $name)), "_");
  }
}
