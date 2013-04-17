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
  public function add_parameter_test() {
    Module::set_var("Var_Test", "Parameter", "original value");
    $this->assert_equal("original value", Module::get_var("Var_Test", "Parameter"));

    Module::set_var("Var_Test", "Parameter", "updated value");
    $this->assert_equal("updated value", Module::get_var("Var_Test", "Parameter"));
  }

  public function clear_parameter_test() {
    Module::set_var("Var_Test", "Parameter", "original value");
    Module::clear_var("Var_Test", "Parameter");
    $this->assert_equal(null, Module::get_var("Var_Test", "Parameter"));
  }

  public function clear_all_module_parameters_test() {
    Module::set_var("Var_Test", "Parameter1", "original value");
    Module::set_var("Var_Test", "Parameter2", "original value");
    Module::clear_all_vars("Var_Test");
    $this->assert_equal(null, Module::get_var("Var_Test", "Parameter1"));
    $this->assert_equal(null, Module::get_var("Var_Test", "Parameter2"));
  }

  public function incr_parameter_test() {
    Module::set_var("Var_Test", "Parameter", "original value");
    Module::incr_var("Var_Test", "Parameter");
    $this->assert_equal("1", Module::get_var("Var_Test", "Parameter"));

    Module::set_var("Var_Test", "Parameter", "2");
    Module::incr_var("Var_Test", "Parameter", "9");
    $this->assert_equal("11", Module::get_var("Var_Test", "Parameter"));

    Module::incr_var("Var_Test", "NonExistent", "9");
    $this->assert_equal(null, Module::get_var("Var_Test", "NonExistent"));
  }
}