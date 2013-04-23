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
class Var_Test extends Unittest_Testcase {
  public function test_add_parameter() {
    Module::set_var("Var_Test", "Parameter", "original value");
    $this->assertEquals("original value", Module::get_var("Var_Test", "Parameter"));

    Module::set_var("Var_Test", "Parameter", "updated value");
    $this->assertEquals("updated value", Module::get_var("Var_Test", "Parameter"));
  }

  public function test_clear_parameter() {
    Module::set_var("Var_Test", "Parameter", "original value");
    Module::clear_var("Var_Test", "Parameter");
    $this->assertEquals(null, Module::get_var("Var_Test", "Parameter"));
  }

  public function test_clear_all_module_parameters() {
    Module::set_var("Var_Test", "Parameter1", "original value");
    Module::set_var("Var_Test", "Parameter2", "original value");
    Module::clear_all_vars("Var_Test");
    $this->assertEquals(null, Module::get_var("Var_Test", "Parameter1"));
    $this->assertEquals(null, Module::get_var("Var_Test", "Parameter2"));
  }

  public function test_incr_parameter() {
    Module::set_var("Var_Test", "Parameter", "original value");
    Module::incr_var("Var_Test", "Parameter");
    $this->assertEquals("1", Module::get_var("Var_Test", "Parameter"));

    Module::set_var("Var_Test", "Parameter", "2");
    Module::incr_var("Var_Test", "Parameter", "9");
    $this->assertEquals("11", Module::get_var("Var_Test", "Parameter"));

    Module::incr_var("Var_Test", "NonExistent", "9");
    $this->assertEquals(null, Module::get_var("Var_Test", "NonExistent"));
  }
}