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
class Item_Model extends ORM_MPTT {
  protected $children = 'items';
  protected $sorting = array();

  var $rules = array(
    "name" => "required|length[0,255]",
    "title" => "required|length[0,255]",
    "description" => "length[0,65535]"
  );

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    return item::viewable($this);
  }

  /**
   * Is this item an album?
   * @return true if it's an album
   */
  public function is_album() {
    return $this->type == 'album';
  }

  /**
   * Is this item a photo?
   * @return true if it's a photo
   */
  public function is_photo() {
    return $this->type == 'photo';
  }

  /**
   * Is this item a movie?
   * @return true if it's a movie
   */
  public function is_movie() {
    return $this->type == 'movie';
  }

  public function delete() {
    $old = clone $this;
    module::event("item_before_delete", $this);

    $parent = $this->parent();
    if ($parent->album_cover_item_id == $this->id) {
      item::remove_album_cover($parent);
    }

    $path = $this->file_path();
    $resize_path = $this->resize_path();
    $thumb_path = $this->thumb_path();

    parent::delete();
    if (is_dir($path)) {
      @dir::unlink($path);
      @dir::unlink(dirname($resize_path));
      @dir::unlink(dirname($thumb_path));
    } else {
      @unlink($path);
      @unlink($resize_path);
      @unlink($thumb_path);
    }

    module::event("item_deleted", $old);
  }

  /**
   * Move this item to the specified target.
   * @chainable
   * @param   Item_Model $target  Target item (must be an album)
   * @return  ORM_MPTT
   */
  function move_to($target) {
    if (!$target->is_album()) {
      throw new Exception("@todo INVALID_MOVE_TYPE $target->type");
    }

    if ($this->id == 1) {
      throw new Exception("@todo INVALID_SOURCE root album");
    }

    $original_path = $this->file_path();
    $original_resize_path = $this->resize_path();
    $original_thumb_path = $this->thumb_path();
    $original_parent = $this->parent();

    parent::move_to($target, true);
    model_cache::clear();
    $this->relative_path_cache = null;

    rename($original_path, $this->file_path());
    if ($this->is_album()) {
      @rename(dirname($original_resize_path), dirname($this->resize_path()));
      @rename(dirname($original_thumb_path), dirname($this->thumb_path()));
      Database::instance()
        ->update("items",
                 array("relative_path_cache" => null,
                       "relative_url_cache" => null),
                 array("left_ptr >" => $this->left_ptr, "right_ptr <" => $this->right_ptr));
    } else {
      @rename($original_resize_path, $this->resize_path());
      @rename($original_thumb_path, $this->thumb_path());
    }

    module::event("item_moved", $this, $original_parent);
    return $this;
  }

  /**
   * Rename the underlying file for this item to a new name.  Move all the files.  This requires a
   * save.
   *
   * @chainable
   */
  public function rename($new_name) {
    if ($new_name == $this->name) {
      return;
    }

    if (strpos($new_name, "/")) {
      throw new Exception("@todo NAME_CANNOT_CONTAIN_SLASH");
    }

    $old_relative_path = urldecode($this->relative_path());
    $new_relative_path = dirname($old_relative_path) . "/" . $new_name;
    @rename(VARPATH . "albums/$old_relative_path", VARPATH . "albums/$new_relative_path");
    @rename(VARPATH . "resizes/$old_relative_path", VARPATH . "resizes/$new_relative_path");
    @rename(VARPATH . "thumbs/$old_relative_path", VARPATH . "thumbs/$new_relative_path");
    $this->name = $new_name;

    if ($this->is_album()) {
      Database::instance()
        ->update("items",
                 array("relative_path_cache" => null,
                       "relative_url_cache" => null),
                 array("left_ptr >" => $this->left_ptr, "right_ptr <" => $this->right_ptr));
    }

    return $this;
  }

  /**
   * Return the server-relative url to this item, eg:
   *   /gallery3/index.php/BobsWedding?page=2
   *   /gallery3/index.php/BobsWedding/Eating-Cake.jpg
   *
   * @param string $query the query string (eg "show=3")
   */
  public function url($query=null) {
    $url = url::site($this->relative_url());
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * Return the full url to this item, eg:
   *   http://example.com/gallery3/index.php/BobsWedding?page=2
   *   http://example.com/gallery3/index.php/BobsWedding/Eating-Cake.jpg
   *
   * @param string $query the query string (eg "show=3")
   */
  public function abs_url($query=null) {
    $url = url::abs_site($this->relative_url());
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * album: /var/albums/album1/album2
   * photo: /var/albums/album1/album2/photo.jpg
   */
  public function file_path() {
    return VARPATH . "albums/" . urldecode($this->relative_path());
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/
   * photo: http://example.com/gallery3/var/albums/album1/photo.jpg
   */
  public function file_url($full_uri=false) {
    $relative_path = "var/albums/" . $this->relative_path();
    return $full_uri ? url::abs_file($relative_path) : url::file($relative_path);
  }

  /**
   * album: /var/resizes/album1/.thumb.jpg
   * photo: /var/albums/album1/photo.thumb.jpg
   */
  public function thumb_path() {
    $base = VARPATH . "thumbs/" . urldecode($this->relative_path());
    if ($this->is_photo()) {
      return $base;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg";
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      return preg_replace("/...$/", "jpg", $base);
    }
  }

  /**
   * Return true if there is a thumbnail for this item.
   */
  public function has_thumb() {
    return $this->thumb_width && $this->thumb_height;
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.thumb.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.thumb.jpg
   */
  public function thumb_url($full_uri=false) {
    $cache_buster = "?m=" . $this->updated;
    $relative_path = "var/thumbs/" . $this->relative_path();
    $base = ($full_uri ? url::abs_file($relative_path) : url::file($relative_path));
    if ($this->is_photo()) {
      return $base . $cache_buster;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg" . $cache_buster;
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      $base = preg_replace("/...$/", "jpg", $base);
      return $base . $cache_buster;
    }
  }

  /**
   * album: /var/resizes/album1/.resize.jpg
   * photo: /var/albums/album1/photo.resize.jpg
   */
  public function resize_path() {
    return VARPATH . "resizes/" . urldecode($this->relative_path()) .
      ($this->is_album() ? "/.album.jpg" : "");
  }

  /**
   * album: http://example.com/gallery3/var/resizes/album1/.resize.jpg
   * photo: http://example.com/gallery3/var/albums/album1/photo.resize.jpg
   */
  public function resize_url($full_uri=false) {
    $relative_path = "var/resizes/" . $this->relative_path();
    return ($full_uri ? url::abs_file($relative_path) : url::file($relative_path)) .
      ($this->is_album() ? "/.album.jpg" : "");
  }

  /**
   * Rebuild the relative_path_cache and relative_url_cache.
   */
  private function _build_relative_caches() {
    $names = array();
    $slugs = array();
    foreach (Database::instance()
             ->select(array("name", "slug"))
             ->from("items")
             ->where("left_ptr <=", $this->left_ptr)
             ->where("right_ptr >=", $this->right_ptr)
             ->where("id <>", 1)
             ->orderby("left_ptr", "ASC")
             ->get() as $row) {
      $names[] = rawurlencode($row->name);
      $slugs[] = rawurlencode($row->slug);
    }
    $this->relative_path_cache = implode($names, "/");
    $this->relative_url_cache = implode($slugs, "/");
    $this->save();
  }

  /**
   * Return the relative path to this item's file.  Note that the components of the path are
   * urlencoded so if you want to use this as a filesystem path, you need to call urldecode
   * on it.
   * @return string
   */
  public function relative_path() {
    if (!$this->loaded) {
      return;
    }

    if (!isset($this->relative_path_cache)) {
      $this->_build_relative_caches();
    }
    return $this->relative_path_cache;
  }

  /**
   * Return the relative url to this item's file.
   * @return string
   */
  public function relative_url() {
    if (!$this->loaded) {
      return;
    }

    if (!isset($this->relative_url_cache)) {
      $this->_build_relative_caches();
    }
    return $this->relative_url_cache;
  }

  /**
   * @see ORM::__get()
   */
  public function __get($column) {
    if ($column == "owner") {
      // This relationship depends on an outside module, which may not be present so handle
      // failures gracefully.
      try {
        return model_cache::get("user", $this->owner_id);
      } catch (Exception $e) {
        return null;
      }
    } else {
      return parent::__get($column);
    }
  }

  /**
   * @see ORM::__set()
   */
  public function __set($column, $value) {
    if ($column == "name") {
      $this->relative_path_cache = null;
    } else if ($column == "slug") {
      if ($this->slug != $value) {
        // Clear the relative url cache for this item and all children
        $this->relative_url_cache = null;
        if ($this->is_album()) {
          Database::instance()
            ->update("items",
                     array("relative_url_cache" => null),
                     array("left_ptr >" => $this->left_ptr, "right_ptr <" => $this->right_ptr));
        }
      }
    }
    parent::__set($column, $value);
  }

  /**
   * @see ORM::save()
   */
  public function save() {
    $significant_changes = $this->changed;
    unset($significant_changes["view_count"]);
    unset($significant_changes["relative_url_cache"]);
    unset($significant_changes["relative_path_cache"]);

    if (!empty($this->changed) && $significant_changes) {
      $this->updated = time();
      if (!$this->loaded) {
        $this->created = $this->updated;
        $this->weight = item::get_max_weight();
      } else {
        $send_event = 1;
      }
    }
    parent::save();
    if (isset($send_event)) {
      module::event("item_updated", $this->original(), $this);
    }
    return $this;
  }

  /**
   * Return the Item_Model representing the cover for this album.
   * @return Item_Model or null if there's no cover
   */
  public function album_cover() {
    if (!$this->is_album()) {
      return null;
    }

    if (empty($this->album_cover_item_id)) {
      return null;
    }

    try {
      return model_cache::get("item", $this->album_cover_item_id);
    } catch (Exception $e) {
      // It's possible (unlikely) that the item was deleted, if so keep going.
      return null;
    }
  }

  /**
   * Find the position of the given child id in this album.  The resulting value is 1-indexed, so
   * the first child in the album is at position 1.
   */
  public function get_position($child_id) {
    if ($this->sort_order == "DESC") {
      $comp = ">";
    } else {
      $comp = "<";
    }

    $db = Database::instance();
    $position = $db->query("
      SELECT COUNT(*) AS position FROM {items}
      WHERE `parent_id` = {$this->id}
        AND `{$this->sort_column}` $comp (SELECT `{$this->sort_column}`
                                          FROM {items} WHERE `id` = $child_id)")
      ->current()->position;

    // We stopped short of our target value in the sort (notice that we're using a < comparator
    // above) because it's possible that we have duplicate values in the sort column.  An
    // equality check would just arbitrarily pick one of those multiple possible equivalent
    // columns, which would mean that if you choose a sort order that has duplicates, it'd pick
    // any one of them as the child's "position".
    //
    // Fix this by doing a 2nd query where we iterate over the equivalent columns and add them to
    // our base value.
    $result = $db->query("
      SELECT id FROM {items}
      WHERE `parent_id` = {$this->id}
        AND `{$this->sort_column}` = (SELECT `{$this->sort_column}`
                                      FROM {items} WHERE `id` = $child_id)
      ORDER BY `id` ASC");
    foreach ($result as $row) {
      $position++;
      if ($row->id == $child_id) {
        break;
      }
    }

    return $position;
  }

  /**
   * Return an <img> tag for the thumbnail.
   * @param array $extra_attrs  Extra attributes to add to the img tag
   * @param int (optional) $max Maximum size of the thumbnail (default: null)
   * @param boolean (optional) $center_vertically Center vertically (default: false)
   * @return string
   */
  public function thumb_img($extra_attrs=array(), $max=null, $center_vertically=false) {
    list ($height, $width) = $this->scale_dimensions($max);
    if ($center_vertically && $max) {
      // The constant is divide by 2 to calculate the file and 10 to convert to em
      $margin_top = ($max - $height) / 20;
      $extra_attrs["style"] = "margin-top: {$margin_top}em";
      $extra_attrs["title"] = $this->title;
    }
    $attrs = array_merge($extra_attrs,
            array(
              "src" => $this->thumb_url(),
              "alt" => $this->title,
              "width" => $width,
              "height" => $height)
            );
    // html::image forces an absolute url which we don't want
    return "<img" . html::attributes($attrs) . "/>";
  }

  /**
   * Calculate the largest width/height that fits inside the given maximum, while preserving the
   * aspect ratio.
   * @param int $max Maximum size of the largest dimension
   * @return array
   */
  public function scale_dimensions($max) {
    $width = $this->thumb_width;
    $height = $this->thumb_height;

    if ($height) {
      if (isset($max)) {
        if ($width > $height) {
          $height = (int)($max * ($height / $width));
          $width = $max;
        } else {
          $width = (int)($max * ($width / $height));
          $height = $max;
        }
      }
    } else {
      // Missing thumbnail, can happen on albums with no photos yet.
      // @todo we should enforce a placeholder for those albums.
      $width = 0;
      $height = 0;
    }
    return array($height, $width);
  }

  /**
   * Return an <img> tag for the resize.
   * @param array $extra_attrs  Extra attributes to add to the img tag
   * @return string
   */
  public function resize_img($extra_attrs) {
    $attrs = array_merge($extra_attrs,
            array("src" => $this->resize_url(),
              "alt" => $this->title,
              "width" => $this->resize_width,
              "height" => $this->resize_height)
            );
    // html::image forces an absolute url which we don't want
    return "<img" . html::attributes($attrs) . "/>";
  }

  /**
   * Return a flowplayer <script> tag for movies
   * @param array $extra_attrs
   * @return string
   */
  public function movie_img($extra_attrs) {
    $v = new View("movieplayer.html");
    $v->attrs = array_merge($extra_attrs,
      array("style" => "display:block;width:{$this->width}px;height:{$this->height}px"));
    if (empty($v->attrs["id"])) {
       $v->attrs["id"] = "gMovieId-{$this->id}";
    }
    return $v;
  }

  /**
   * Return all of the children of this album.  Unless you specify a specific sort order, the
   * results will be ordered by this album's sort order.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @param   array    orderby
   * @return array ORM
   */
  function children($limit=null, $offset=0, $where=array(), $orderby=null) {
    if (empty($orderby)) {
      $orderby = array($this->sort_column => $this->sort_order);
    }
    return parent::children($limit, $offset, $where, $orderby);
  }

  /**
   * Return the children of this album, and all of it's sub-albums.  Unless you specify a specific
   * sort order, the results will be ordered by this album's sort order.  Note that this
   * album's sort order is imposed on all sub-albums, regardless of their sort order.
   *
   * @chainable
   * @param   integer  SQL limit
   * @param   integer  SQL offset
   * @param   array    additional where clauses
   * @return object ORM_Iterator
   */
  function descendants($limit=null, $offset=0, $where=array(), $orderby=null) {
    if (empty($orderby)) {
      $orderby = array($this->sort_column => $this->sort_order);
    }
    return parent::descendants($limit, $offset, $where, $orderby);
  }
}
