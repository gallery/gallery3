<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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

class core_event_Core {
  static function group_created($group) {
    access::add_group($group);
  }

  static function group_before_delete($group) {
    access::delete_group($group);
  }

  static function item_created($item) {
    access::add_item($item);
  }

  static function item_before_delete($item) {
    access::delete_item($item);
  }

  static function organize_form_creation($event_parms) {
    if (count($event_parms->itemids) > 1) {
      return ;
    }

    $item = ORM::factory("item")
      ->in("id", $event_parms->itemids[0])
      ->find();

    $generalPane = new View("organize_edit_general.html");
    $generalPane->item = $item;

    $event_parms->panes[] = array("label" => $item->is_album() ? t("Edit Album") : t("Edit Photo"),
                                  "content" => $generalPane);

    if ($item->is_album()) {
      $sortPane = new View("organize_edit_sort.html");
      $sortPane->sort_by = $item->sort_column;
      $sortPane->sort_order =
        empty($item->sort_order) || $item->sort_order == "ASC" ? t("Ascending") : t("Descending");

      $event_parms->panes[] = array("label" => t("Sort Order"),  "content" => $sortPane);
    }
  }
}
