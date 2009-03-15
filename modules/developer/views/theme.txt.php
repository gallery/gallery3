<?php defined("SYSPATH") or die("No direct script access."); ?>
<?= "<?php defined(\"SYSPATH\") or die(\"No direct script access.\");" ?>
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
class <?= $module ?>_theme {
  static function sidebar_blocks($theme) {
    $block = new Block();
    $block->css_id = "g<?= $css_id ?>";
    $block->title = t("<?= $name ?>");
    $block->content = new View("<?= $module ?>_block.html");

    $block->content->item = ORM::factory("item", 1);

    return $block;
  }
  
<? if (!empty($callbacks["album_blocks"])): ?>
  static function album_blocks($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["album_bottom"])): ?>
  static function album_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["album_top"])): ?>
  static function album_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_credits"])): ?>
  static function admin_credits($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["photo"])): ?>
  static function admin_footer($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_header_top"])): ?>
  static function admin_header_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_header_bottom"])): ?>
  static function admin_header_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_page_bottom"])): ?>
  static function admin_page_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_page_top"])): ?>
  static function admin_page_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["admin_head"])): ?>
  static function admin_head($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["credits"])): ?>
  static function credits($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["dynamic_bottom"])): ?>
  static function dynamic_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["dynamic_top"])): ?>
  static function dynamic_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["footer"])): ?>
  static function footer($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["head"])): ?>
  static function head($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["header_bottom"])): ?>
  static function header_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["header_top"])): ?>
  static function header_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["page_bottom"])): ?>
  static function page_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["pae_top"])): ?>
  static function page_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["photo_blocks"])): ?>
  static function photo_blocks($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["photo_bottom"])): ?>
  static function photo_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["photo_top"])): ?>
  static function photo_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["sidebar_bottom"])): ?>
  static function sidebar_bottom($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["sidebar_top"])): ?>
  static function sidebar_top($theme) {
  }
  
<? endif ?>
<? if (!empty($callbacks["thumb_bottom"])): ?>
     static function thumb_bottom($theme, $child) {
  }
  
<? endif ?>
<? if (!empty($callbacks["thumb_info"])): ?>
     static function thumb_info($theme, $child) {
  }
  
<? endif ?>
<? if (!empty($callbacks["thumb_top"])): ?>
     static function thumb_top($theme, $child) {
  }
  
<? endif ?>
}
