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
class Item_Model_Core extends ORM_MPTT {
  protected $children = "items";
  protected $sorting = array();
  protected $data_file = null;

  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->created = time();
      $this->rand_key = ((float)mt_rand()) / (float)mt_getrandmax();
      $this->thumb_dirty = 1;
      $this->resize_dirty = 1;
      $this->sort_column = "created";
      $this->sort_order = "ASC";
      $this->owner_id = identity::active_user()->id;
    }
  }

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

  public function delete($ignored_id=null) {
    if ($this->id == 1) {
      $v = new Validation(array("id"));
      $v->add_error("id", "cant_delete_root_album");
      ORM_Validation_Exception::handle_validation($this->table_name, $v);
    }

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
      // Take some precautions against accidentally deleting way too much
      $delete_resize_path = dirname($resize_path);
      $delete_thumb_path = dirname($thumb_path);
      if ($delete_resize_path == VARPATH . "resizes" ||
          $delete_thumb_path == VARPATH . "thumbs" ||
          $path == VARPATH . "albums") {
        throw new Exception(
          "@todo DELETING_TOO_MUCH ($delete_resize_path, $delete_thumb_path, $path)");
      }
      @dir::unlink($path);
      @dir::unlink($delete_resize_path);
      @dir::unlink($delete_thumb_path);
    } else {
      @unlink($path);
      @unlink($resize_path);
      @unlink($thumb_path);
    }

    module::event("item_deleted", $old);
  }

  /**
   * Specify the path to the data file associated with this item.  To actually associate it,
   * you still have to call save().
   * @chainable
   */
  public function set_data_file($data_file) {
    $this->data_file = $data_file;
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
    return ($full_uri ? url::abs_file($relative_path) : url::file($relative_path))
      . "?m={$this->updated}";
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
    $cache_buster = "?m={$this->updated}";
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
      ($this->is_album() ? "/.album.jpg" : "")
      . "?m={$this->updated}";
  }

  /**
   * Rebuild the relative_path_cache and relative_url_cache.
   */
  private function _build_relative_caches() {
    $names = array();
    $slugs = array();
    foreach (db::build()
             ->select(array("name", "slug"))
             ->from("items")
             ->where("left_ptr", "<=", $this->left_ptr)
             ->where("right_ptr", ">=", $this->right_ptr)
             ->where("id", "<>", 1)
             ->order_by("left_ptr", "ASC")
             ->execute() as $row) {
      // Don't encode the names segment
      $names[] = rawurlencode($row->name);
      $slugs[] = rawurlencode($row->slug);
    }
    $this->relative_path_cache = implode($names, "/");
    $this->relative_url_cache = implode($slugs, "/");
    return $this;
  }

  /**
   * Return the relative path to this item's file.  Note that the components of the path are
   * urlencoded so if you want to use this as a filesystem path, you need to call urldecode
   * on it.
   * @return string
   */
  public function relative_path() {
    if (!$this->loaded()) {
      return;
    }

    if (!isset($this->relative_path_cache)) {
      $this->_build_relative_caches()->save();
    }
    return $this->relative_path_cache;
  }

  /**
   * Return the relative url to this item's file.
   * @return string
   */
  public function relative_url() {
    if (!$this->loaded()) {
      return;
    }

    if (!isset($this->relative_url_cache)) {
      $this->_build_relative_caches()->save();
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
        return identity::lookup_user($this->owner_id);
      } catch (Exception $e) {
        return null;
      }
    } else {
      return parent::__get($column);
    }
  }

  /**
   * Handle any business logic necessary to create or modify an item.
   * @see ORM::save()
   *
   * @return ORM Item_Model
   */
  public function save() {
    $significant_changes = $this->changed;
    unset($significant_changes["view_count"]);
    unset($significant_changes["relative_url_cache"]);
    unset($significant_changes["relative_path_cache"]);

    if ((!empty($this->changed) && $significant_changes) || isset($this->data_file)) {
      $this->updated = time();
      if (!$this->loaded()) {
        // Create a new item.

        // Set a weight if it's missing.  We don't do this in the constructor because it's not a
        // simple assignment.
        if (empty($this->weight)) {
          $this->weight = item::get_max_weight();
        }

        // Make an url friendly slug from the name, if necessary
        if (empty($this->slug)) {
          $tmp = pathinfo($this->name, PATHINFO_FILENAME);
          $tmp = preg_replace("/[^A-Za-z0-9-_]+/", "-", $tmp);
          $this->slug = trim($tmp, "-");

          // If the filename is all invalid characters, then the slug may be empty here.  Pick a
          // random value.
          if (empty($this->slug)) {
            $this->slug = (string)rand(1000, 9999);
          }
        }

        // Get the width, height and mime type from our data file for photos and movies.
        if ($this->is_photo() || $this->is_movie()) {
          if ($this->is_photo()) {
            list ($this->width, $this->height, $this->mime_type, $extension) =
              photo::get_file_metadata($this->data_file);
          } else if ($this->is_movie()) {
            list ($this->width, $this->height, $this->mime_type, $extension) =
              movie::get_file_metadata($this->data_file);
          }

          // Force an extension onto the name if necessary
          $pi = pathinfo($this->data_file);
          if (empty($pi["extension"])) {
            $this->name = "{$this->name}.$extension";
          }
        }

        $this->_randomize_name_or_slug_on_conflict();

        parent::save();

        // Build our url caches, then save again.  We have to do this after it's already been
        // saved once because we use only information from the database to build the paths.  If we
        // could depend on a save happening later we could defer this 2nd save.
        $this->_build_relative_caches();
        parent::save();

        // Take any actions that we can only do once all our paths are set correctly after saving.
        switch ($this->type) {
        case "album":
          mkdir($this->file_path());
          mkdir(dirname($this->thumb_path()));
          mkdir(dirname($this->resize_path()));
          break;

        case "photo":
        case "movie":
          // The thumb or resize may already exist in the case where a movie and a photo generate
          // a thumbnail of the same name (eg, foo.flv movie and foo.jpg photo will generate
          // foo.jpg thumbnail).  If that happens, randomize and save again.
          if (file_exists($this->resize_path()) ||
              file_exists($this->thumb_path())) {
            $pi = pathinfo($this->name);
            $this->name = $pi["filename"] . "-" . rand() . "." . $pi["extension"];
            parent::save();
          }

          copy($this->data_file, $this->file_path());
          break;
        }

        // This will almost definitely trigger another save, so put it at the end so that we're
        // tail recursive.  Null out the data file variable first, otherwise the next save will
        // trigger an item_updated_data_file event.
        $this->data_file = null;
        module::event("item_created", $this);
      } else {
        // Update an existing item

        // If any significant fields have changed, load up a copy of the original item and
        // keep it around.
        $original = ORM::factory("item", $this->id);
        if (array_intersect($this->changed, array("parent_id", "name", "slug"))) {
          $original->_build_relative_caches();
          $this->relative_path_cache = null;
          $this->relative_url_cache = null;
        }

        $this->_randomize_name_or_slug_on_conflict();

        parent::save();

        // Now update the filesystem and any database caches if there were significant value
        // changes.  If anything past this point fails, then we'll have an inconsistent database
        // so this code should be as robust as we can make it.

        // Update the MPTT pointers, if necessary.  We have to do this before we generate any
        // cached paths!
        if ($original->parent_id != $this->parent_id) {
          parent::move_to($this->parent());
        }

        if ($original->parent_id != $this->parent_id || $original->name != $this->name) {
          // Move all of the items associated data files
          @rename($original->file_path(), $this->file_path());
          if ($this->is_album()) {
            @rename(dirname($original->resize_path()), dirname($this->resize_path()));
            @rename(dirname($original->thumb_path()), dirname($this->thumb_path()));
          } else {
            @rename($original->resize_path(), $this->resize_path());
            @rename($original->thumb_path(), $this->thumb_path());
          }

          if ($original->parent_id != $this->parent_id) {
            // This will result in 2 events since we'll still fire the item_updated event below
            module::event("item_moved", $this, $original->parent());
          }
        }

        // Changing the name, slug or parent ripples downwards
        if ($this->is_album() &&
            ($original->name != $this->name ||
             $original->slug != $this->slug ||
             $original->parent_id != $this->parent_id)) {
          db::build()
            ->update("items")
            ->set("relative_url_cache", null)
            ->set("relative_path_cache", null)
            ->where("left_ptr", ">", $this->left_ptr)
            ->where("right_ptr", "<", $this->right_ptr)
            ->execute();
        }

        // Replace the data file, if requested.
        // @todo: we don't handle the case where you swap in a file of a different mime type
        //        should we prevent that in validation?  or in set_data_file()
        if ($this->data_file && ($this->is_photo() || $this->is_movie())) {
          copy($this->data_file, $this->file_path());

          // Get the width, height and mime type from our data file for photos and movies.
          if ($this->is_photo()) {
            list ($this->width, $this->height) = photo::get_file_metadata($this->file_path());
          } else if ($this->is_movie()) {
            list ($this->width, $this->height) = movie::get_file_metadata($this->file_path());
          }
          $this->thumb_dirty = 1;
          $this->resize_dirty = 1;
        }

        module::event("item_updated", $original, $this);

        if ($this->data_file) {
          // Null out the data file variable here, otherwise this event will trigger another
          // save() which will think that we're doing another file move.
          $this->data_file = null;
          module::event("item_updated_data_file", $this);
        }
      }
    } else if (!empty($this->changed)) {
      // Insignificant changes only.  Don't fire events or do any special checking to try to keep
      // this lightweight.
      parent::save();
    }

    return $this;
  }

  /**
   * Check to see if there's another item that occupies the same name or slug that this item
   * intends to use, and if so choose a new name/slug while preserving the extension.
   * @todo Improve this.  Random numbers are not user friendly
   */
  private function _randomize_name_or_slug_on_conflict() {
    $base_name = pathinfo($this->name, PATHINFO_FILENAME);
    $base_ext = pathinfo($this->name, PATHINFO_EXTENSION);
    $base_slug = $this->slug;
    while (ORM::factory("item")
           ->where("parent_id", "=", $this->parent_id)
           ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
           ->and_open()
           ->where("name", "=", $this->name)
           ->or_where("slug", "=", $this->slug)
           ->close()
           ->find()->id) {
      $rand = rand();
      if ($base_ext) {
        $this->name = "$base_name-$rand.$base_ext";
      } else {
        $this->name = "$base_name-$rand";
      }
      $this->slug = "$base_slug-$rand";
    }
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
  public function get_position($child, $where=array()) {
    if (!strcasecmp($this->sort_order, "DESC")) {
      $comp = ">";
    } else {
      $comp = "<";
    }
    $db = db::build();

    // If the comparison column has NULLs in it, we can't use comparators on it and will have to
    // deal with it the hard way.
    $count = $db->from("items")
      ->where("parent_id", "=", $this->id)
      ->where($this->sort_column, "IS", null)
      ->merge_where($where)
      ->count_records();

    if (empty($count)) {
      // There are no NULLs in the sort column, so we can just use it directly.
      $sort_column = $this->sort_column;

      $position = $db->from("items")
        ->where("parent_id", "=", $this->id)
        ->where($sort_column, $comp, $child->$sort_column)
        ->merge_where($where)
        ->count_records();

      // We stopped short of our target value in the sort (notice that we're using a < comparator
      // above) because it's possible that we have duplicate values in the sort column.  An
      // equality check would just arbitrarily pick one of those multiple possible equivalent
      // columns, which would mean that if you choose a sort order that has duplicates, it'd pick
      // any one of them as the child's "position".
      //
      // Fix this by doing a 2nd query where we iterate over the equivalent columns and add them to
      // our base value.
      foreach ($db
               ->select("id")
               ->from("items")
               ->where("parent_id", "=", $this->id)
               ->where($sort_column, "=", $child->$sort_column)
               ->merge_where($where)
               ->order_by(array("id" => "ASC"))
               ->execute() as $row) {
        $position++;
        if ($row->id == $child->id) {
          break;
        }
      }
    } else {
      // There are NULLs in the sort column, so we can't use MySQL comparators.  Fall back to
      // iterating over every child row to get to the current one.  This can be wildly inefficient
      // for really large albums, but it should be a rare case that the user is sorting an album
      // with null values in the sort column.
      //
      // Reproduce the children() functionality here using Database directly to avoid loading the
      // whole ORM for each row.
      $order_by = array($this->sort_column => $this->sort_order);
      // Use id as a tie breaker
      if ($this->sort_column != "id") {
        $order_by["id"] = "ASC";
      }

      $position = 0;
      foreach ($db->select("id")
               ->from("items")
               ->where("parent_id", "=", $this->id)
               ->merge_where($where)
               ->order_by($order_by)
               ->execute() as $row) {
        $position++;
        if ($row->id == $child->id) {
          break;
        }
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
      $margin_top = (int)(($max - $height) / 20);
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
          $height = (int)($max * $height / $width);
          $width = $max;
        } else {
          $width = (int)($max * $width / $height);
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
    $max_size = module::get_var("gallery", "resize_size", 640);
    $width = $this->width;
    $height = $this->height;
    if ($width > $max_size || $height > $max_size) {
      if ($width > $height) {
        $height = (int)($height * $max_size / $width);
        $width = $max_size;
      } else {
        $width = (int)($width * $max_size / $height);
        $height = $max_size;
      }
    }

    $v->attrs = array_merge($extra_attrs, array("style" => "width:{$width}px;height:{$height}px",
                                                "class" => "g-movie"));
    if (empty($v->attrs["id"])) {
       $v->attrs["id"] = "g-item-id-{$this->id}";
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
   * @param   array    order_by
   * @return array ORM
   */
  function children($limit=null, $offset=null, $where=array(), $order_by=null) {
    if (empty($order_by)) {
      $order_by = array($this->sort_column => $this->sort_order);
      // Use id as a tie breaker
      if ($this->sort_column != "id") {
        $order_by["id"] = "ASC";
      }
    }
    return parent::children($limit, $offset, $where, $order_by);
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
  function descendants($limit=null, $offset=null, $where=array(), $order_by=null) {
    if (empty($order_by)) {
      $order_by = array($this->sort_column => $this->sort_order);
      // Use id as a tie breaker
      if ($this->sort_column != "id") {
        $order_by["id"] = "ASC";
      }
    }
    return parent::descendants($limit, $offset, $where, $order_by);
  }

  /**
   * Specify our rules here so that we have access to the instance of this model.
   */
  public function validate(Validation $array=null) {
    if (!$array) {
      $this->rules = array(
        "album_cover_item_id" => array("callbacks" => array(array($this, "valid_album_cover"))),
        "description"         => array("rules"     => array("length[0,65535]")),
        "mime_type"           => array("callbacks" => array(array($this, "valid_field"))),
        "name"                => array("rules"     => array("length[0,255]", "required"),
                                       "callbacks" => array(array($this, "valid_name"))),
        "parent_id"           => array("callbacks" => array(array($this, "valid_parent"))),
        "rand_key"            => array("rule"      => array("decimal")),
        "slug"                => array("rules"     => array("length[0,255]", "required"),
                                       "callbacks" => array(array($this, "valid_slug"))),
        "sort_column"         => array("callbacks" => array(array($this, "valid_field"))),
        "sort_order"          => array("callbacks" => array(array($this, "valid_field"))),
        "title"               => array("rules"     => array("length[0,255]", "required")),
        "type"                => array("callbacks" => array(array($this, "read_only"),
                                                            array($this, "valid_field"))),
      );

      // Conditional rules
      if ($this->id == 1) {
        // We don't care about the name and slug for the root album.
        $this->rules["name"] = array();
        $this->rules["slug"] = array();
      }

      // Movies and photos must have data files.  Verify the data file on new items, or if it has
      // been replaced.
      if (($this->is_photo() || $this->is_movie()) && $this->data_file) {
        $this->rules["name"]["callbacks"][] = array($this, "valid_data_file");
      }
    }

    parent::validate($array);
  }

  /**
   * Validate that the desired slug does not conflict.
   */
  public function valid_slug(Validation $v, $field) {
    if (preg_match("/[^A-Za-z0-9-_]/", $this->slug)) {
      $v->add_error("slug", "not_url_safe");
    } else if (db::build()
        ->from("items")
        ->where("parent_id", "=", $this->parent_id)
        ->where("id", "<>", $this->id)
        ->where("slug", "=", $this->slug)
        ->count_records()) {
      $v->add_error("slug", "conflict");
    }
  }

  /**
   * Validate the item name.  It can't conflict with other names, can't contain slashes or
   * trailing periods.
   */
  public function valid_name(Validation $v, $field) {
    if (strpos($this->name, "/") !== false) {
      $v->add_error("name", "no_slashes");
      return;
    } else if (rtrim($this->name, ".") !== $this->name) {
      $v->add_error("name", "no_trailing_period");
      return;
    }

    if ($this->is_movie() || $this->is_photo()) {
      if ($this->loaded()) {
        // Existing items can't change their extension
        $original = ORM::factory("item", $this->id);
        $new_ext = pathinfo($this->name, PATHINFO_EXTENSION);
        $old_ext = pathinfo($original->name, PATHINFO_EXTENSION);
        if (strcasecmp($new_ext, $old_ext)) {
          $v->add_error("name", "illegal_data_file_extension");
          return;
        }
      } else {
        // New items must have an extension
        if (!pathinfo($this->name, PATHINFO_EXTENSION)) {
          $v->add_error("name", "illegal_data_file_extension");
          return;
        }
      }
    }

    if (db::build()
        ->from("items")
        ->where("parent_id", "=", $this->parent_id)
        ->where("name", "=", $this->name)
        ->merge_where($this->id ? array(array("id", "<>", $this->id)) : null)
        ->count_records()) {
      $v->add_error("name", "conflict");
      return;
    }
  }

  /**
   * Make sure that the data file is well formed (it exists and isn't empty).
   */
  public function valid_data_file(Validation $v, $field) {
    if (!is_file($this->data_file)) {
      $v->add_error("name", "bad_data_file_path");
    } else if (filesize($this->data_file) == 0) {
      $v->add_error("name", "empty_data_file");
    }

    if ($this->loaded()) {
      if ($this->is_photo()) {
        list ($a, $b, $mime_type) = photo::get_file_metadata($this->data_file);
      } else if ($this->is_movie()) {
        list ($a, $b, $mime_type) = movie::get_file_metadata($this->data_file);
      }
      if ($mime_type != $this->mime_type) {
        $v->add_error("name", "cant_change_mime_type");
      }
    }
  }

  /**
   * Make sure that the parent id refers to an album.
   */
  public function valid_parent(Validation $v, $field) {
    if ($this->id == 1) {
      if ($this->parent_id != 0) {
        $v->add_error("parent_id", "invalid");
      }
    } else {
      $query = db::build()
        ->from("items")
        ->where("id", "=", $this->parent_id)
        ->where("type", "=", "album");

      // If this is an existing item, make sure the new parent is not part of our hierarchy
      if ($this->loaded()) {
        $query->and_open()
          ->where("left_ptr", "<", $this->left_ptr)
          ->or_where("right_ptr", ">", $this->right_ptr)
          ->close();
      }

      if ($query->count_records() != 1) {
        $v->add_error("parent_id", "invalid");
      }
    }
  }

  /**
   * Make sure the album cover item id refers to a valid item, or is null.
   */
  public function valid_album_cover(Validation $v, $field) {
    if ($this->id == 1) {
      return;
    }

    if ($this->album_cover_item_id && db::build()
        ->from("items")
        ->where("id", "=", $this->album_cover_item_id)
        ->count_records() != 1) {
      $v->add_error("album_cover_item_id", "invalid_item");
    }
  }

  /**
   * Make sure that the type is valid.
   */
  public function valid_field(Validation $v, $field) {
    switch($field) {
    case "mime_type":
      if ($this->is_movie()) {
        $legal_values = array("video/flv", "video/x-flv", "video/mp4");
      } if ($this->is_photo()) {
        $legal_values = array("image/jpeg", "image/gif", "image/png");
      }
      break;

    case "sort_column":
      if (!array_key_exists($this->sort_column, $this->object)) {
        $v->add_error($field, "invalid");
      }
      break;

    case "sort_order":
      $legal_values = array("ASC", "DESC", "asc", "desc");
      break;

    case "type":
      $legal_values = array("album", "photo", "movie");
      break;

    default:
      $v->add_error($field, "unvalidated_field");
      break;
    }

    if (isset($legal_values) && !in_array($this->$field, $legal_values)) {
      $v->add_error($field, "invalid");
    }
  }

  /**
   * This field cannot be changed after it's been set.
   */
  public function read_only(Validation $v, $field) {
    if ($this->loaded() && isset($this->changed[$field])) {
      $v->add_error($field, "read_only");
    }
  }

  /**
   * Same as ORM::as_array() but convert id fields into their RESTful form.
   */
  public function as_restful_array() {
    // Convert item ids to rest URLs for consistency
    $data = $this->as_array();
    if ($tmp = $this->parent()) {
      $data["parent"] = rest::url("item", $tmp);
    }
    unset($data["parent_id"]);
    if ($tmp = $this->album_cover()) {
      $data["album_cover"] = rest::url("item", $tmp);
    }
    unset($data["album_cover_item_id"]);

    $data["web_url"] = $this->abs_url();

    if (!$this->is_album()) {
      if (access::can("view_full", $this)) {
        $data["file_url"] = rest::url("data", $this, "full");
        $data["file_size"] = filesize($this->file_path());
      }
      if (access::user_can(identity::guest(), "view_full", $this)) {
        $data["file_url_public"] = $this->file_url(true);
      }
    }

    if ($this->is_photo()) {
      $data["resize_url"] = rest::url("data", $this, "resize");
      $data["resize_size"] = filesize($this->resize_path());
      if (access::user_can(identity::guest(), "view", $this)) {
        $data["resize_url_public"] = $this->resize_url(true);
      }
    }

    if ($this->has_thumb()) {
      $data["thumb_url"] = rest::url("data", $this, "thumb");
      $data["thumb_size"] = filesize($this->thumb_path());
      if (access::user_can(identity::guest(), "view", $this)) {
        $data["thumb_url_public"] = $this->thumb_url(true);
      }
    }

    $data["can_edit"] = access::can("edit", $this);

    // Elide some internal-only data that is going to cause confusion in the client.
    foreach (array("relative_path_cache", "relative_url_cache", "left_ptr", "right_ptr",
                   "thumb_dirty", "resize_dirty", "weight") as $key) {
      unset($data[$key]);
    }
    return $data;
  }
}
