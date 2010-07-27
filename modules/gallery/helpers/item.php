<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2010 Bharat Mediratta
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
class item_Core {
  static function move($source, $target) {
    access::required("view", $source);
    access::required("view", $target);
    access::required("edit", $source);
    access::required("edit", $target);

    $parent = $source->parent();
    if ($parent->album_cover_item_id == $source->id) {
      if ($parent->children_count() > 1) {
        foreach ($parent->children(2) as $child) {
          if ($child->id != $source->id) {
            $new_cover_item = $child;
            break;
          }
        }
        item::make_album_cover($new_cover_item);
      } else {
        item::remove_album_cover($parent);
      }
    }

    $source->parent_id = $target->id;

    // Moving may result in name or slug conflicts.  If that happens, try up to 5 times to pick a
    // random name (or slug) to avoid the conflict.
    $orig_name = $source->name;
    $orig_name_filename = pathinfo($source->name, PATHINFO_FILENAME);
    $orig_name_extension = pathinfo($source->name, PATHINFO_EXTENSION);
    $orig_slug = $source->slug;
    for ($i = 0; $i < 5; $i++) {
      try {
        $source->save();
        if ($orig_name != $source->name) {
          switch ($source->type) {
          case "album":
            message::info(
              t("Album <b>%old_name</b> renamed to <b>%new_name</b> to avoid a conflict",
                array("old_name" => $orig_name, "new_name" => $source->name)));
            break;

          case "photo":
            message::info(
              t("Photo <b>%old_name</b> renamed to <b>%new_name</b> to avoid a conflict",
                array("old_name" => $orig_name, "new_name" => $source->name)));
            break;

          case "movie":
            message::info(
              t("Movie <b>%old_name</b> renamed to <b>%new_name</b> to avoid a conflict",
                array("old_name" => $orig_name, "new_name" => $source->name)));
            break;
          }
        }
        break;
      } catch (ORM_Validation_Exception $e) {
        $rand = rand(10, 99);
        $errors = $e->validation->errors();
        if (isset($errors["name"])) {
          $source->name = $orig_name_filename . "-{$rand}." . $orig_name_extension;
          unset($errors["name"]);
        }
        if (isset($errors["slug"])) {
          $source->slug = $orig_slug . "-{$rand}";
          unset($errors["slug"]);
        }

        if ($errors) {
          // There were other validation issues-- we don't know how to handle those
          throw $e;
        }
      }
    }

    // If the target has no cover item, make this it.
    if ($target->album_cover_item_id == null)  {
      item::make_album_cover($source);
    }
  }

  static function make_album_cover($item) {
    $parent = $item->parent();
    access::required("view", $item);
    access::required("view", $parent);
    access::required("edit", $parent);

    model_cache::clear();
    $parent->album_cover_item_id = $item->is_album() ? $item->album_cover_item_id : $item->id;
    if ($item->thumb_dirty) {
      $parent->thumb_dirty = 1;
      graphics::generate($parent);
    } else {
      copy($item->thumb_path(), $parent->thumb_path());
      $parent->thumb_width = $item->thumb_width;
      $parent->thumb_height = $item->thumb_height;
    }
    $parent->save();
    $grand_parent = $parent->parent();
    if ($grand_parent && access::can("edit", $grand_parent) &&
        $grand_parent->album_cover_item_id == null)  {
      item::make_album_cover($parent);
    }
  }

  static function remove_album_cover($album) {
    access::required("view", $album);
    access::required("edit", $album);
    @unlink($album->thumb_path());

    model_cache::clear();
    $album->album_cover_item_id = null;
    $album->thumb_width = 0;
    $album->thumb_height = 0;
    $album->thumb_dirty = 1;
    $album->save();
    graphics::generate($album);
  }

  /**
   * Sanitize a filename into something presentable as an item title
   * @param string $filename
   * @return string title
   */
  static function convert_filename_to_title($filename) {
    $title = strtr($filename, "_", " ");
    $title = preg_replace("/\..{3,4}$/", "", $title);
    $title = preg_replace("/ +/", " ", $title);
    return $title;
  }

  /**
   * Convert a filename into something we can use as a url component.
   * @param string $filename
   */
  static function convert_filename_to_slug($filename) {
    $result = pathinfo($filename, PATHINFO_FILENAME);
    $result = preg_replace("/[^A-Za-z0-9-_]+/", "-", $result);
    return trim($result, "-");
  }

  /**
   * Display delete confirmation message and form
   * @param object $item
   * @return string form
   */
  static function get_delete_form($item) {
    $page_type = Input::instance()->get("page_type");
    $from_id = Input::instance()->get("from_id");
    $form = new Forge(
      "quick/delete/$item->id?page_type=$page_type&from_id=$from_id", "",
      "post", array("id" => "g-confirm-delete"));
    $group = $form->group("confirm_delete")->label(t("Confirm Deletion"));
    $group->submit("")->value(t("Delete"));
    $form->script("")
      ->url(url::abs_file("modules/gallery/js/item_form_delete.js"));
    return $form;
  }

  /**
   * Get the next weight value
   */
  static function get_max_weight() {
    // Guard against an empty result when we create the first item.  It's unfortunate that we
    // have to check this every time.
    // @todo: figure out a better way to bootstrap the weight.
    $result = db::build()
      ->select("weight")->from("items")
      ->order_by("weight", "desc")->limit(1)
      ->execute()->current();
    return ($result ? $result->weight : 0) + 1;
  }

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  static function viewable($model) {
    $view_restrictions = array();
    if (!identity::active_user()->admin) {
      foreach (identity::group_ids_for_active_user() as $id) {
        $view_restrictions[] = array("items.view_$id", "=", access::ALLOW);
      }
    }

    if (count($view_restrictions)) {
      $model->and_open()->merge_or_where($view_restrictions)->close();
    }

    return $model;
  }

  /**
   * Return the root Item_Model
   * @return Item_Model
   */
  static function root() {
    return model_cache::get("item", 1);
  }

  /**
   * Return a query to get a random Item_Model, with optional filters
   */
  static function random_query() {
    // Pick a random number and find the item that's got nearest smaller number.
    // This approach works best when the random numbers in the system are roughly evenly
    // distributed so this is going to be more efficient with larger data sets.
    return ORM::factory("item")
      ->viewable()
      ->where("rand_key", "<", ((float)mt_rand()) / (float)mt_getrandmax())
      ->order_by("rand_key", "DESC");
  }
}