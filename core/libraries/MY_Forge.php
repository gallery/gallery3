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

class Forge extends Forge_Core {
  public function render($template="form.html", $custom=false) {
    return parent::render($template, $custom);
  }

  /**
   * Associate validation rules defined in the model with this form.
   */
  public function add_rules_from($model) {
    foreach ($this->inputs as $name => $input) {
      if (isset($input->inputs)) {
        $input->add_rules_from($model);
      }
      if (isset($model->rules[$name])) {
        $input->rules($model->rules[$name]);
      }
    }
  }
}