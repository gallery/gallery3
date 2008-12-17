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
class watermark_Core {
  public static function get_watermark_form() {
    $form = new Forge("admin/watermark/load", "", "post",
      array("id" => "gUploadWatermarkForm", "enctype" => "multipart/form-data"));
    $group = $form->group("add_watermark")->label(_("Upload Watermark"));
    $group->upload("file")->label(_("Watermark"))->rules("allow[jpg,png,gif],size[1M]");
    return $form;
  }

  public static function get_watermark_postion_form($position="southeast") {
    $form = new Forge("admin/watermark/position", "", "post");
    $group = $form->group("watermark_position")->label(_("Update Position"));
    $group->dropdown("position")->label(_("Watermark Position"))
      ->options(array("northwest",  "north",  "northeast",
                      "west",       "center", "east",
                      "southwest",  "south",  "southeast"))
      ->selected("8");
    return $form;
  }
}