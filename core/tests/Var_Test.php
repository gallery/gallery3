<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Var_Test extends Unit_Test_Case {
  public function add_parameter_test() {
    module::set_var("core", "Parameter", "original value");
    $this->assert_equal("original value", module::get_var("core", "Parameter"));

    module::set_var("core", "Parameter", "updated value");
    $this->assert_equal("updated value", module::get_var("core", "Parameter"));

    module::set_var("core", "Parameter2", "new parameter");
    $core = module::get("core");

    $params = $core->vars->select_list("name", "value");
    $this->assert_false(empty($params["Parameter"]));
    $this->assert_equal("updated value", $params["Parameter"]);
    $this->assert_false(empty($params["Parameter2"]));
    $this->assert_equal("new parameter", $params["Parameter2"]);
  }
}