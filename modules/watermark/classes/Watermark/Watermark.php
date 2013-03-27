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
class watermark_Core {
  static function get_add_form() {
    for ($i = 1; $i <= 100; $i++) {
      $range[$i] = "$i%";
    }

    $form = new Forge("admin/watermarks/add", "", "post", array("id" => "g-add-watermark-form"));
    $group = $form->group("add_watermark")->label(t("Upload watermark"));
    $group->upload("file")->label(t("Watermark"))->rules("allow[jpg,png,gif]|size[1MB]|required")
      ->error_messages("required", "You must select a watermark")
      ->error_messages("invalid_type", "The watermark must be a JPG, GIF or PNG")
      ->error_messages("max_size", "The watermark is too big (1 MB max)");
    $group->dropdown("position")->label(t("Watermark position"))
      ->options(self::positions())
      ->selected("southeast");
    $group->dropdown("transparency")->label(t("Transparency (100% = completely transparent)"))
      ->options($range)
      ->selected(1);
    $group->submit("")->value(t("Upload"));
    return $form;
  }

  static function get_edit_form() {
    for ($i = 1; $i <= 100; $i++) {
      $range[$i] = "$i%";
    }

    $form = new Forge("admin/watermarks/edit", "", "post", array("id" => "g-edit-watermark-form"));
    $group = $form->group("edit_watermark")->label(t("Edit Watermark"));
    $group->dropdown("position")->label(t("Watermark Position"))
      ->options(self::positions())
      ->selected(module::get_var("watermark", "position"));
    $group->dropdown("transparency")->label(t("Transparency (100% = completely transparent)"))
      ->options($range)
      ->selected(module::get_var("watermark", "transparency"));
    $group->submit("")->value(t("Save"));
    return $form;
  }

  static function get_delete_form() {
    $form = new Forge("admin/watermarks/delete", "", "post", array("id" => "g-delete-watermark-form"));
    $group = $form->group("delete_watermark")->label(t("Really delete Watermark?"));
    $group->submit("")->value(t("Delete"));
    return $form;
  }

  static function positions() {
    return array("northwest" => t("Northwest"),
                 "north" => t("North"),
                 "northeast" => t("Northeast"),
                 "west" => t("West"),
                 "center" => t("Center"),
                 "east" => t("East"),
                 "southwest" => t("Southwest"),
                 "south" => t("South"),
                 "southeast" => t("Southeast"));
  }

  static function position($key) {
    $positions = self::positions();
    return $positions[$key];
  }
}