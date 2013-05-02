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
class Gallery_Formo_Driver_ORM_Kohana extends Formo_Core_Driver_ORM_Kohana {
  /**
   * Save the ORM model "model" using the values of the field's children.
   * Any ORM validation errors will be translated to form field errors.
   * Optionally, "check" determines whether the model validation is checked
   * before saving (default: true).  The result can be found in the field's
   * "orm_passed" variable.
   *
   *   Example: to load the values, check, and then save the model:
   *     $form->orm("save", array("model" => $model));
   *     $passed = $form->get("orm_passed");
   *   Example: to load the values and just save the model (without check):
   *     $form->orm("save", array("model" => $model, "check" => false));
   *     $passed = $form->get("orm_passed");
   *
   * Note that this automatically flattens subgroups and discards fields that
   * don't exist in the model (e.g. "submit").
   *
   * @todo: consider recasting this as a patch to send upstream to the Formo project.
   */
  public static function save(array $array) {
    $model = $array["model"];
    $field = $array["field"];
    $check = Arr::get($array, "check", true);

    // Load the values in the model.  ORM silently discards fields that don't exist.
    $model->values(Arr::flatten($field->as_array("val")));

    // Save it, set orm_passed, and translate ORM errors if needed.
    try {
      if ($check) {
        $model->check();
      }
      $model->save();
      $field->set("orm_passed", true);
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors() as $alias => $errors) {
        $field->find($alias)->error($errors[0]);
      }
      $field->set("orm_passed", false);
    }
  }
}
