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
   * Link the ORM model "model" to the form element.  This loads the values of
   * the model into the form element (typically a group/form) and adds a callback
   * for form validation to load the form values back into the model and validate it.
   * Any ORM validation errors will be translated to form field errors.
   * The linked model can be found in the field's "linked_orm_model" variable.
   *
   *   Example: to link the form to item 3:
   *     $item = ORM::factory("Item", 3);
   *     $form = ....; // define the form
   *     $form->orm("link", array("model" => $item));
   *     if ($form->load()->validate()) {
   *       // Model has been updated with the form values and passed validation - all set!
   *       $item->save();
   *     }
   *
   * Only elements that are common to both the form and the model are linked, and
   * the rest are ignored.  Note that this automatically flattens form subgroups.
   *
   * @todo: consider recasting this as a patch to send upstream to the Formo project.
   * We'd need to include relationship support for this to be compatible with stuff upstream.
   */
  public static function link(array $array) {
    $model = $array["model"];
    $field = $array["field"];

    // Load the values in the form.  Arr::overwrite() silently discards fields that don't exist.
    $vals = Arr::overwrite(Arr::flatten($field->as_array("val")), $model->as_array());
    foreach ($vals as $alias => $val) {
      $field->find($alias)->val($val);
    }

    $field->set("linked_orm_model", $model);
    $field->callback("pass", array("Formo_Driver_ORM_Kohana::load_and_check"));
  }

  /**
   * This callback is used during form validation to load the form values back into
   * the model, perform model validation, and translate ORM validation errors to
   * form errors.  This function needs to be public, but should not be called directly.
   */
  public static function load_and_check($field) {
    $model = $field->get("linked_orm_model");

    // Load the values in the model.  ORM silently discards fields that don't exist.
    $model->values(Arr::flatten($field->as_array("val")));

    // Save it and translate ORM errors if needed.
    try {
      $model->check();
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors() as $alias => $errors) {
        // This uses only the first error for each field.
        $field->find($alias)->error($errors[0]);
      }
    }
  }
}
