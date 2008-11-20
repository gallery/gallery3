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
class dynamic_block_Core{
  const HEAD_LINK = "link";
  const HEAD_SCRIPT = "script";
  const HEADER_TYPE = "header";
  const FOOTER_TYPE = "footer";
  const SIDE_BAR_TYPE = "sidebar";
  const CONTENT_ALBUM = "album";
  const CONTENT_PHOTO = "photo";
  
  public static function define_blocks($module, $callbacks) {
    // @todo create unit test for this
    foreach ($callbacks as $type => $method) {
      $block = ORM::factory("block");
      $block->module = $module;
      $block->type = $type;
      $block->method = $method;
      $block->save();
    }
  }

  public static function remove_blocks($module) {
    // @todo and don't forget one for this
    try {
      ORM::factory("block")->where("module",$module)->find()->delete();
    } catch (Exception $e) {
      Kohana::log("error", $e);
    }
  }
}
