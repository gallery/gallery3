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
class gallery_quick_Core {
  static function get_quick_buttons($item, $page_type) {
    $buttons = self::buttons($item, $page_type);
    foreach (module::active() as $module) {
      if ($module->name == "gallery") {
        continue;
      }
      $class_name = "{$module->name}_quick";
      if (method_exists($class_name, "buttons")) {
        $module_buttons = call_user_func(array($class_name, "buttons"), $item, $page_type);
        foreach (array("left", "center", "right", "additional") as $position) {
          if (!empty($module_buttons[$position])) {
            $buttons[$position] = array_merge($buttons[$position], $module_buttons[$position]);
          }
        }
      }
    }

    $sorted_buttons->main = array();
    foreach (array("left", "center", "right") as $position) {
      $sorted_buttons->main = array_merge($sorted_buttons->main, $buttons[$position]);
    }

    $sorted_buttons->additional = $buttons["additional"];
    $max_display = empty($sorted_buttons->additional) ? 6 : 5;
    if (count($sorted_buttons->main) >= $max_display) {
      $to_move = array_slice($sorted_buttons->main, 5);
      $sorted_buttons->additional = array_merge($to_move, $sorted_buttons->additional);
      for ($i = count($sorted_buttons->main); $i >= 5; $i--) {
        unset($sorted_buttons->main[$i]);
      }
    }

    Kohana::log("error", ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
    Kohana::log("error", Kohana::debug($sorted_buttons));
    Kohana::log("error", "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    return $sorted_buttons;
  }

  static function buttons($item, $page_type) {
    $elements = array("left" => array(), "center" => array(), "right" => array(),
                      "additional"  => array());
    switch ($item->type) {
    case "movie":
      $edit_title = t("Edit this movie");
      $move_title = t("Move this movie to another album");
      $cover_title = t("Choose this movie as the album cover");
      $delete_title = t("Delete this movie");
      break;
    case "album":
      $edit_title = t("Edit this album");
      $move_title = t("Move this album to another album");
      $cover_title = t("Choose this album as the album cover");
      $delete_title = t("Delete this album");
      break;
    default:
      $edit_title = t("Edit this photo");
      $move_title = t("Move this photo to another album");
      $cover_title = t("Choose this photo as the album cover");
      $delete_title = t("Delete this photo");
      break;
    }

    $csrf = access::csrf_token();
    $elements["left"][] = (object)array(
      "title" => $edit_title,
      "class" => "gDialogLink gButtonLink",
      "icon" => "ui-icon-pencil",
      "href" => url::site("quick/form_edit/$item->id?page_type=$page_type"));
    if ($item->is_photo() && graphics::can("rotate")) {
      $elements["left"][] =
        (object)array(
          "title" => t("Rotate 90 degrees counter clockwise"),
          "class" => "gButtonLink",
          "icon" => "ui-icon-rotate-ccw",
          "href" => url::site("quick/form_edit/$item->id/ccw?csrf=$csrf&?page_type=$page_type"));
      $elements["left"][] =
        (object)array(
          "title" => t("Rotate 90 degrees clockwise"),
          "class" => "gButtonLink",
          "icon" => "ui-icon-rotate-cw",
          "href" => url::site("quick/form_edit/$item->id/cw?csrf=$csrf&page_type=$page_type"));
    }

    // Don't move photos from the photo page; we don't yet have a good way of redirecting after move
    if ($page_type == "album") {
      $elements["left"][] = (object)array(
        "title" => $move_title,
        "class" => "gDialogLink gButtonLink",
        "icon" => "ui-icon-folder-open",
        "href" => url::site("move/browse/$item->id"));
    }

    if (access::can("edit", $item->parent())) {
      $disabledState =
        $item->type == "album" && empty($item->album_cover_item_id) ? " ui-state-disabled" : "";
      $elements["right"][] = (object)array(
        "title" => $cover_title,
        "class" => "gButtonLink{$disabledState}",
        "icon" => "ui-icon-star",
        "href" => url::site("quick/make_album_cover/$item->id?csrf=$csrf&page_type=$page_type"));

      $elements["right"][] = (object)array(
        "title" => $delete_title,
        "class" => "gButtonLink",
        "icon" => "ui-icon-trash",
        "id" => "gQuickDelete",
        "href" => url::site("quick/form_delete/$item->id?csrf=$csrf&page_type=$page_type"));
    }

    if ($item->is_album()) {
      $elements["additional"][] = (object)array(
        "title" => t("Add a photo"),
        "class" => "add_item gDialogLink",
        "href" => url::site("simple_uploader/app/$item->id"));
      $elements["additional"][] = (object)array(
        "title" => t("Add an album"),
        "class" => "add_album gDialogLink",
        "href" => url::site("form/add/albums/$item->id?type=album"));
      $elements["additional"][] = (object)array(
        "title" => t("Edit permissions"),
        "class" => "permissions gDialogLink",
        "href" => url::site("permissions/browse/$item->id"));
    }
    return $elements;
  }
}
