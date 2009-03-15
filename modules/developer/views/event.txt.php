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
class <?= $module ?>_event {
<? if (!empty($callbacks["batch_complete"])): ?>
  static function batch_complete() {
  }
    
<? endif ?>
<? if (!empty($callbacks["comment_add_form"])): ?>
  static function comment_add_form($form) {
  }
    
<? endif ?>
<? if (!empty($callbacks["comment_created"])): ?>
  static function comment_created($theme, $args) {
  }
    
<? endif ?>
<? if (!empty($callbacks["comment_updated"])): ?>
  static function comment_updated($old, $new) {
  }
    
<? endif ?>
<? if (!empty($callbacks["group_before_delete"])): ?>
  static function group_before_delete($group) {
  }
    
<? endif ?>
<? if (!empty($callbacks["group_created"])): ?>
  static function group_created($group) {
  }
    
<? endif ?>
<? if (!empty($callbacks["item_before_delete"])): ?>
  static function item_before_delete($item) {
  }
    
<? endif ?>
<? if (!empty($callbacks["item_created"])): ?>
  static function item_created($item) {
  }
    
<? endif ?>
<? if (!empty($callbacks["item_related_update"])): ?>
  static function item_related_update($item) {
  }
    
<? endif ?>
<? if (!empty($callbacks["item_related_update_batch"])): ?>
  static function item_related_update_batch($sql) {
  }
    
<? endif ?>
<? if (!empty($callbacks["item_updated"])): ?>
  static function item_updated($old, $new) {
  }
    
<? endif ?>
<? if (!empty($callbacks["user_before_delete"])): ?>
  static function user_before_delete($user) {
  }
    
<? endif ?>
<? if (!empty($callbacks["user_created"])): ?>
  static function user_created($user) {
  }
    
<? endif ?>
<? if (!empty($callbacks["user_login"])): ?>
  static function user_login($user) {
  }
    
<? endif ?>
<? if (!empty($callbacks["user_logout"])): ?>
  static function user_logout($user) {
  }
<? endif ?>
}
