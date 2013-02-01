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

    $orig_name = $source->name;
    $source->parent_id = $target->id;
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

    $old_album_cover_id = $parent->album_cover_item_id;

    model_cache::clear();
    $parent->album_cover_item_id = $item->is_album() ? $item->album_cover_item_id : $item->id;
    $parent->save();
    graphics::generate($parent);

    // Walk up the parent hierarchy and set album covers if necessary
    $grand_parent = $parent->parent();
    if ($grand_parent && access::can("edit", $grand_parent) &&
        $grand_parent->album_cover_item_id == null)  {
      item::make_album_cover($parent);
    }

    // When albums are album covers themselves, we hotlink directly to the target item.  This
    // means that when we change an album cover, the grandparent may have a deep link to the old
    // album cover.  So find any parent albums that had the old item as their album cover and
    // switch them over to the new item.
    if ($old_album_cover_id) {
      foreach ($item->parents(array(array("album_cover_item_id", "=", $old_album_cover_id)))
               as $ancestor) {
        if (access::can("edit", $ancestor)) {
          $ancestor->album_cover_item_id = $parent->album_cover_item_id;
          $ancestor->save();
          graphics::generate($ancestor);
        }
      }
    }
  }

  static function remove_album_cover($album) {
    access::required("view", $album);
    access::required("edit", $album);

    model_cache::clear();
    $album->album_cover_item_id = null;
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
    $result = str_replace("&", "-and-", $filename);
    $result = str_replace(" ", "-", $result);

    // It's not easy to extend the text helper since it's called by the Input class which is
    // referenced in hooks/init_gallery, so it's
    if (class_exists("transliterate")) {
      $result = transliterate::utf8_to_ascii($result);
    } else {
      $result = text::transliterate_to_ascii($result);
    }
    $result = preg_replace("/[^A-Za-z0-9-_]+/", "-", $result);
    $result = preg_replace("/-+/", "-", $result);
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
   * Find an item by its path.  If there's no match, return an empty Item_Model.
   * NOTE: the caller is responsible for performing security checks on the resulting item.
   * @param string $path
   * @return object Item_Model
   */
  static function find_by_path($path) {
    $path = trim($path, "/");

    // The root path name is NULL not "", hence this workaround.
    if ($path == "") {
      return item::root();
    }

    // Check to see if there's an item in the database with a matching relative_path_cache value.
    // Since that field is urlencoded, we must urlencoded the components of the path.
    foreach (explode("/", $path) as $part) {
      $encoded_array[] = rawurlencode($part);
    }
    $encoded_path = join("/", $encoded_array);
    $item = ORM::factory("item")
      ->where("relative_path_cache", "=", $encoded_path)
      ->find();
    if ($item->loaded()) {
      return $item;
    }

    // Since the relative_path_cache field is a cache, it can be unavailable.  If we don't find
    // anything, fall back to checking the path the hard way.
    $paths = explode("/", $path);
    foreach (ORM::factory("item")
             ->where("name", "=", end($paths))
             ->where("level", "=", count($paths) + 1)
             ->find_all() as $item) {
      if (urldecode($item->relative_path()) == $path) {
        return $item;
      }
    }

    return new Item_Model();
  }


  /**
   * Locate an item using the URL.  We assume that the url is in the form /a/b/c where each
   * component matches up with an item slug.  If there's no match, return an empty Item_Model
   * NOTE: the caller is responsible for performing security checks on the resulting item.
   * @param string $url the relative url fragment
   * @return Item_Model
   */
  static function find_by_relative_url($relative_url) {
    // In most cases, we'll have an exact match in the relative_url_cache item field.
    // but failing that, walk down the tree until we find it.  The fallback code will fix caches
    // as it goes, so it'll never be run frequently.
    $item = ORM::factory("item")->where("relative_url_cache", "=", $relative_url)->find();
    if (!$item->loaded()) {
      $segments = explode("/", $relative_url);
      foreach (ORM::factory("item")
               ->where("slug", "=", end($segments))
               ->where("level", "=", count($segments) + 1)
               ->find_all() as $match) {
        if ($match->relative_url() == $relative_url) {
          $item = $match;
        }
      }
    }
    return $item;
  }

  /**
   * Return the root Item_Model
   * @return Item_Model
   */
  static function root() {
    return model_cache::get("item", 1);
  }

  /**
   * Return a query to get a random Item_Model, with optional filters.
   * Usage: item::random_query()->execute();
   *
   * Note: You can add your own ->where() clauses but if your Gallery is
   * small or your where clauses are over-constrained you may wind up with
   * no item.  You should try running this a few times in a loop if you
   * don't get an item back.
   */
  static function random_query() {
    // Pick a random number and find the item that's got nearest smaller number.
    // This approach works best when the random numbers in the system are roughly evenly
    // distributed so this is going to be more efficient with larger data sets.
    return ORM::factory("item")
      ->viewable()
      ->where("rand_key", "<", random::percent())
      ->order_by("rand_key", "DESC");
  }

  /**
   * Find the position of the given item in its parent album.  The resulting
   * value is 1-indexed, so the first child in the album is at position 1.
   *
   * @param Item_Model $item
   * @param array      $where an array of arrays, each compatible with ORM::where()
   */
  static function get_position($item, $where=array()) {
    $album = $item->parent();

    if (!strcasecmp($album->sort_order, "DESC")) {
      $comp = ">";
    } else {
      $comp = "<";
    }
    $query_model = ORM::factory("item");

    // If the comparison column has NULLs in it, we can't use comparators on it
    // and will have to deal with it the hard way.
    $count = $query_model->viewable()
      ->where("parent_id", "=", $album->id)
      ->where($album->sort_column, "IS", null)
      ->merge_where($where)
      ->count_all();

    if (empty($count)) {
      // There are no NULLs in the sort column, so we can just use it directly.
      $sort_column = $album->sort_column;

      $position = $query_model->viewable()
        ->where("parent_id", "=", $album->id)
        ->where($sort_column, $comp, $item->$sort_column)
        ->merge_where($where)
        ->count_all();

      // We stopped short of our target value in the sort (notice that we're
      // using a inequality comparator above) because it's possible that we have
      // duplicate values in the sort column.  An equality check would just
      // arbitrarily pick one of those multiple possible equivalent columns,
      // which would mean that if you choose a sort order that has duplicates,
      // it'd pick any one of them as the child's "position".
      //
      // Fix this by doing a 2nd query where we iterate over the equivalent
      // columns and add them to our position count.
      foreach ($query_model->viewable()
               ->select("id")
               ->where("parent_id", "=", $album->id)
               ->where($sort_column, "=", $item->$sort_column)
               ->merge_where($where)
               ->order_by(array("id" => "ASC"))
               ->find_all() as $row) {
        $position++;
        if ($row->id == $item->id) {
          break;
        }
      }
    } else {
      // There are NULLs in the sort column, so we can't use MySQL comparators.
      // Fall back to iterating over every child row to get to the current one.
      // This can be wildly inefficient for really large albums, but it should
      // be a rare case that the user is sorting an album with null values in
      // the sort column.
      //
      // Reproduce the children() functionality here using Database directly to
      // avoid loading the whole ORM for each row.
      $order_by = array($album->sort_column => $album->sort_order);
      // Use id as a tie breaker
      if ($album->sort_column != "id") {
        $order_by["id"] = "ASC";
      }

      $position = 0;
      foreach ($query_model->viewable()
               ->select("id")
               ->where("parent_id", "=", $album->id)
               ->merge_where($where)
               ->order_by($order_by)
               ->find_all() as $row) {
        $position++;
        if ($row->id == $item->id) {
          break;
        }
      }
    }

    return $position;
  }

  /**
   * Set the display context callback for any future item renders.
   */
  static function set_display_context_callback() {
    if (!request::user_agent("robot")) {
      $args = func_get_args();
      Cache::instance()->set("display_context_" . $sid = Session::instance()->id(), $args,
                             array("display_context"));
    }
  }

  /**
   * Get rid of the display context callback
   */
  static function clear_display_context_callback() {
    Cache::instance()->delete("display_context_" . $sid = Session::instance()->id());
  }

  /**
   * Call the display context callback for the given item
   */
  static function get_display_context($item) {
    if (!request::user_agent("robot")) {
      $args = Cache::instance()->get("display_context_" . $sid = Session::instance()->id());
      $callback = $args[0];
      $args[0] = $item;
    }

    if (empty($callback)) {
      $callback = "Albums_Controller::get_display_context";
      $args = array($item);
    }
    return call_user_func_array($callback, $args);
  }

  /**
   * Reset all child weights of a given album to a monotonically increasing sequence based on the
   * current sort order of the album.
   */
  static function resequence_child_weights($album) {
    $weight = 0;
    foreach ($album->children() as $child) {
      $child->weight = ++$weight;
      $child->save();
    }
  }
}