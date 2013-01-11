<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
  public $data_file = null;

  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->created = time();
      $this->rand_key = random::percent();
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
    if (!$this->loaded()) {
      // Concurrent deletes may result in this item already being gone.  Ignore it.
      return;
    }

    if ($this->id == 1) {
      $v = new Validation(array("id"));
      $v->add_error("id", "cant_delete_root_album");
      ORM_Validation_Exception::handle_validation($this->table_name, $v);
    }

    $old = clone $this;
    module::event("item_before_delete", $this);

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
   *   /gallery3/index.php/Bobs%20Wedding?page=2
   *   /gallery3/index.php/Bobs%20Wedding/Eating-Cake.jpg
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
   *   http://example.com/gallery3/index.php/Bobs%20Wedding?page=2
   *   http://example.com/gallery3/index.php/Bobs%20Wedding/Eating-Cake.jpg
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
   * album: /var/albums/Bobs Wedding
   * photo: /var/albums/Bobs Wedding/Eating-Cake.jpg
   * movie: /var/albums/Bobs Wedding/Making-Speech.mp4
   */
  public function file_path() {
    return VARPATH . "albums/" . urldecode($this->relative_path());
  }

  /**
   * $full_uri = true
   *   album: http://example.com/gallery3/var/albums/Bobs%20Wedding?m=123456789
   *   photo: http://example.com/gallery3/var/albums/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: http://example.com/gallery3/var/albums/Bobs%20Wedding/Making-Speech.mp4?m=123456789
   * $full_uri = false
   *   album: /gallery3/var/albums/Bobs%20Wedding?m=123456789
   *   photo: /gallery3/var/albums/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: /gallery3/var/albums/Bobs%20Wedding/Making-Speech.mp4?m=123456789
   */
  public function file_url($full_uri=false) {
    $relative_path = "var/albums/" . $this->relative_path();
    $cache_buster = $this->_cache_buster($this->file_path());
    return ($full_uri ? url::abs_file($relative_path) : url::file($relative_path))
      . $cache_buster;
  }

  /**
   * album: /var/thumbs/Bobs Wedding/.album.jpg
   * photo: /var/thumbs/Bobs Wedding/Eating-Cake.jpg
   * movie: /var/thumbs/Bobs Wedding/Making-Speech.jpg
   */
  public function thumb_path() {
    $base = VARPATH . "thumbs/" . urldecode($this->relative_path());
    if ($this->is_album()) {
      return $base . "/.album." . $this->thumb_extension;
    } else {
      // Replace the full-size image extension with the thumb extension
      return legal_file::change_extension($base, $this->thumb_extension);
    }
  }

  /**
   * Return true if there is a thumbnail for this item.
   */
  public function has_thumb() {
    return $this->thumb_width && $this->thumb_height;
  }

  /**
   * $full_uri = true
   *   album: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/.album.jpg?m=123456789
   *   photo: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/Making-Speech.mp4?m=123456789
   * $full_uri = false
   *   album: /gallery3/var/thumbs/Bobs%20Wedding/.album.jpg?m=123456789
   *   photo: /gallery3/var/thumbs/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: /gallery3/var/thumbs/Bobs%20Wedding/Making-Speech.jpg?m=123456789
   */
  public function thumb_url($full_uri=false) {
    $cache_buster = $this->_cache_buster($this->thumb_path());
    $relative_path = "var/thumbs/" . $this->relative_path();
    $base = ($full_uri ? url::abs_file($relative_path) : url::file($relative_path));
    if ($this->is_album()) {
      return $base . "/.album." . $this->thumb_extension . $cache_buster;
    } else {
      // Replace the full-size image extension with the thumb extension
      return legal_file::change_extension($base, $this->thumb_extension) . $cache_buster;
    }
  }

  /**
   * album: /var/resizes/Bobs Wedding/.    (Not a file!  Trailing dot makes it safe for dirname().)
   * photo: /var/resizes/Bobs Wedding/Eating-Cake.jpg?m=123456789
   * movie: (does not exist)
   */
  public function resize_path() {
    $base = VARPATH . "resizes/" . urldecode($this->relative_path());
    if ($this->is_album()) {
      return $base . "/.";
    } else if ($this->is_photo()) {
      // Replace the full-size image extension with the resize extension
      return legal_file::change_extension($base, $this->resize_extension);
    }
    // Movies don't have a resize
    return "";
  }

  /**
   * $full_uri = true
   *   album: http://example.com/gallery3/var/resizes/Bobs%20Wedding    (Not a file!)
   *   photo: http://example.com/gallery3/var/resizes/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: (does not exist)
   * $full_uri = false
   *   album: /gallery3/var/resizes/Bobs%20Wedding    (Not a file!)
   *   photo: /gallery3/var/resizes/Bobs%20Wedding/Eating-Cake.jpg?m=123456789
   *   movie: (does not exist)
   */
  public function resize_url($full_uri=false) {
    $cache_buster = $this->_cache_buster($this->resize_path());
    $relative_path = "var/resizes/" . $this->relative_path();
    $base = ($full_uri ? url::abs_file($relative_path) : url::file($relative_path));
    if ($this->is_album()) {
      return $base . $cache_buster;
    } else if ($this->is_photo()) {
      // Replace the full-size image extension with the resize extension
      return legal_file::change_extension($base, $this->resize_extension) . $cache_buster;
    }
    // Movies don't have a resize
    return "";
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
   * Return the extension to this item's file.  This can be uppercase or lowercase.
   * @return string
   */
  public function file_extension() {
    if ($this->is_album()) {
      return "";
    } else {
      return pathinfo($this->name, PATHINFO_EXTENSION);
    }
  }

  /**
   * Return the base filename to this item's file (i.e. filename without its extension).
   * @return string
   */
  public function file_base() {
    if ($this->is_album()) {
      return $this->name;
    } else {
      return pathinfo($this->name, PATHINFO_FILENAME);
    }
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
        // @todo the create subroutine assumes we have data_file defined, and crashes if we don't.
        module::event("item_before_create", $this);

        // Set a weight if it's missing.  We don't do this in the constructor because it's not a
        // simple assignment.
        if (empty($this->weight)) {
          $this->weight = item::get_max_weight();
        }

        // Make an url friendly slug from the name, if necessary
        if (empty($this->slug)) {
          $this->slug = item::convert_filename_to_slug($this->file_base());

          // If the filename is all invalid characters, then the slug may be empty here.  Pick a
          // random value.
          if (empty($this->slug)) {
            $this->slug = (string)rand(1000, 9999);
          }
        }

        // Get metadata from the data file and check/fix the item name extension
        $this->_get_and_check_metadata();
        
        // Set the resize and thumb extensions if not already set
        $this->_fill_resize_and_thumb_extensions();
        
        $this->_randomize_name_or_slug_on_conflict();

        parent::save();

        // Build our url caches, then save again.  We have to do this after it's already been
        // saved once because we use only information from the database to build the paths.  If we
        // could depend on a save happening later we could defer this 2nd save.
        $this->_build_relative_caches();
        parent::save();

        // Take any actions that we can only do once all our paths are set correctly after saving.
        if ($this->is_album()) {
          mkdir($this->file_path());
          mkdir(dirname($this->thumb_path()));
          mkdir(dirname($this->resize_path()));
        } else {
          copy($this->data_file, $this->file_path());
        }

        // This will almost definitely trigger another save, so put it at the end so that we're
        // tail recursive.  Null out the data file variable first, otherwise the next save will
        // trigger an item_updated_data_file event.
        $this->data_file = null;
        module::event("item_created", $this);
      } else {
        // Update an existing item
        module::event("item_before_update", $item);

        // If any significant fields have changed, load up a copy of the original item and
        // keep it around.
        $original = ORM::factory("item", $this->id);

        if (isset($this->data_file)) {
          // Get metadata from the data file and check/fix the item name extension
          $this->_get_and_check_metadata();
          // Remove old resize and thumb extensions
          $this->resize_extension = null;
          $this->thumb_extension = null;
        } else if (!$this->is_album() &&
                   legal_file::get_types_by_extension($this->file_extension()) != $this->mime_type) {
          // We don't have a data file, but we did change the name to something with an incorrect
          // extension (likely because the user took it off) - Revert to what we used to have.
          $this->name = legal_file::change_extension($this->name, $original->file_extension());
        }

        if (array_intersect($this->changed, array("parent_id", "name", "slug"))) {
          $original->_build_relative_caches();
          $this->relative_path_cache = null;
          $this->relative_url_cache = null;
        }

        if (array_intersect($this->changed, array("album_cover_item_id"))) {
          $this->thumb_extension = null;
          $this->thumb_dirty = 1;
          $this->thumb_height = 0;
          $this->thumb_width = 0;
        }

        // Set the resize and thumb extensions if not already set
        $this->_fill_resize_and_thumb_extensions();

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

        // Build path caches
        $this->_build_relative_caches();

        // If our resize or thumb extensions changed, delete the old ones and mark them as dirty
        // *before* they could be potentially moved elsewhere with incorrect extensions
        if ($original->resize_extension != $this->resize_extension) {
          @unlink($original->resize_path());
          $this->resize_dirty = 1;
        }
        if ($original->thumb_extension != $this->thumb_extension) {
          @unlink($original->thumb_path());
          $this->thumb_dirty = 1;
        }

        if ($original->parent_id != $this->parent_id || $original->name != $this->name) {
          // If there is a data file, then we want to preserve both the old data and the new data.
          // (Third-party event handlers would like access to both). The old data file will be
          // accessible via the $original item, and the new one via $this item. But in that case,
          // we don't want to rename the original as below, because the old data would end up being
          // clobbered by the new data file. Also, the rename isn't necessary, because the new item
          // data is coming from the data file anyway. So we only perform the rename if there isn't
          // a data file. Another way to solve this would be to copy the original file rather than
          // conditionally rename it, but a copy would cost far more than the rename.
          if (!isset($this->data_file)) {
            @rename($original->file_path(), $this->file_path());
          }
          // Move all of the items associated data files
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
        if ($this->data_file && ($this->is_photo() || $this->is_movie())) {
          copy($this->data_file, $this->file_path());
          $this->thumb_dirty = 1;
          $this->resize_dirty = 1;
        }

        module::event("item_updated", $original, $this);

        if ($this->data_file) {
          // Null out the data file variable here, otherwise this event will trigger another
          // save() which will think that we're doing another file move.
          $this->data_file = null;
          if ($original->file_path() != $this->file_path()) {
            @unlink($original->file_path());
          }
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
   * Check to see if there's another item that occupies the same name (without extension) or slug
   * that this item intends to use, and if so choose a new name/slug while preserving the 
   * extension.  Since this checks the name without its extension, it covers possible collisions
   * with thumbs and resizes as well (e.g. between the thumbs of movie foo.flv and photo foo.jpg)
   */
  private function _randomize_name_or_slug_on_conflict() {
    $base_slug = $this->slug;
    $suffix_num = 0;
    if ($this->is_album()) {
      $base_name = $this->name;
      while (ORM::factory("item")
             ->where("parent_id", "=", $this->parent_id)
             ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
             ->and_open()
             ->where("name", "=", $this->name)
             ->or_where("slug", "=", $this->slug)
             ->close()
             ->find()->id) {
        $suffix_num++;
        $suffix = str_pad($suffix_num, 4, "0", STR_PAD_LEFT);
        $this->name = "$base_name-$suffix";
        $this->slug = "$base_slug-$suffix";
        $this->relative_path_cache = null;
        $this->relative_url_cache = null;
      }
    } else {
      $base_name = $this->file_base();
      $base_ext = $this->file_extension();
      while (ORM::factory("item")
             ->where("parent_id", "=", $this->parent_id)
             ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
             ->and_open()
             ->where("name", "LIKE", addcslashes($this->file_base(), "_%") . ".%")
             ->or_where("slug", "=", $this->slug)
             ->close()
             ->find()->id) {
        $suffix_num++;
        $suffix = str_pad($suffix_num, 4, "0", STR_PAD_LEFT);
        $this->name = "$base_name-$suffix.$base_ext";
        $this->slug = "$base_slug-$suffix";
        $this->relative_path_cache = null;
        $this->relative_url_cache = null;
      }
    }
  }

  /**
   * Get the metadata from the data file, then ensure that its extension matches its metadata.
   * Many helpers, (e.g. ImageMagick), assume the MIME type from the extension.  So when we adopt
   * the new data file, it's important to check that we have the right extension.  This ensures
   * that the item's extension is always appropriate for its data. We don't try to preserve the name
   * of the data file, though, because the name is typically a temporary randomly-generated name.
   */
  private function _get_and_check_metadata() {
    if ($this->is_photo() || $this->is_movie()) {
      // Get the width, height, mime type, and extension from our data file for photos and movies.
      if ($this->is_photo()) {
        list ($this->width, $this->height, $this->mime_type, $extension) =
          photo::get_file_metadata($this->data_file);
      } else if ($this->is_movie()) {
        list ($this->width, $this->height, $this->mime_type, $extension) =
          movie::get_file_metadata($this->data_file);
      }

      // See if the metadata matches the item's file extension, and change if incorrect or missing.
      // The checking is actually performed on the mimes to avoid renaming already-valid things
      // with extension synonyms (e.g. don't rename foo.jpeg to foo.jpg).
      if (legal_file::get_types_by_extension($this->file_extension()) != $this->mime_type) {
        // Extension is incorrect or missing - change the item name.
        if (legal_file::get_types_by_extension(
            $data_extension = pathinfo($this->data_file, PATHINFO_EXTENSION)) != $this->mime_type) {
          // Data file extension matches mime - use it (avoids renaming foo.jpeg to foo.jpg)
          $this->name = legal_file::change_extension($this->name, $data_extension);
        } else {
          // Data file extension doesn't match mime - use what metadata gave us
          $this->name = legal_file::change_extension($this->name, $extension);
        }
      }
    }
  }

  /**
   * Fill resize_extension and thumb_extension if not already set.  If not set for a photo or
   * movie, use the convert variable if defined or the full-size extension if not.  If not set for
   * an album thumb, use the extension of the cover item's thumb if defined or default jpg if not.
   */
  private function _fill_resize_and_thumb_extensions() {
    if (empty($this->resize_extension) && $this->is_photo()) {
      $convert_extension = module::get_var("gallery", "convert_photo_resize");
      $this->resize_extension = $convert_extension ? $convert_extension : $this->file_extension();
    }
    if (empty($this->thumb_extension)) {
      if ($this->is_album()) {
        $album_cover = $this->album_cover();
        $this->thumb_extension = $album_cover ? $album_cover->thumb_extension : "jpg";
      } else {
        $convert_extension = module::get_var("gallery", "convert_{$this->type}_thumb");
        $this->thumb_extension = $convert_extension ? $convert_extension : $this->file_extension();
      }
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
   *
   * This method stands as a backward compatibility for gallery 3.0, and will
   * be deprecated in version 3.1.
   */
  public function get_position($child, $where=array()) {
    return item::get_position($child, $where);
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
   * aspect ratio.  Don't upscale.
   * @param int $max Maximum size of the largest dimension
   * @return array
   */
  public function scale_dimensions($max) {
    $width = $this->thumb_width;
    $height = $this->thumb_height;

    if ($width <= $max && $height <= $max) {
        return array($height, $width);
    }

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
   * Return a view for movies.  By default this is a Flowplayer v3 <script> tag, but 
   * movie_img events can override this and provide their own player/view.  If no player/view
   * is found and the movie is unsupported by Flowplayer v3, this returns a simple download link.
   * @param array $extra_attrs
   * @return string
   */
  public function movie_img($extra_attrs) {
    $max_size = module::get_var("gallery", "resize_size", 640);
    $width = $this->width;
    $height = $this->height;
    if ($width == 0 || $height == 0) {
      // Not set correctly, likely because ffmpeg isn't available.  Making the window 0x0 causes the
      // video to be effectively unviewable.  So, let's guess: set width to max_size and guess a 
      // height (using 4:3 aspect ratio).  Once the video metadata is loaded, js in 
      // movieplayer.html.php will correct these values.
      $width = $max_size;
      $height = ceil($width * 3/4);
    } 
    $attrs = array_merge(array("id" => "g-item-id-{$this->id}"), $extra_attrs,
                         array("class" => "g-movie"));

    // Run movie_img events, which can either:
    //  - generate a view, which is used in place of the standard Flowplayer v3 player
    //    (use view variable)
    //  - alter the arguments sent to the standard player
    //    (use fp_params and fp_config variables)
    $movie_img = new stdClass();
    $movie_img->max_size = $max_size;
    $movie_img->width = $width;
    $movie_img->height = $height;
    $movie_img->attrs = $attrs;
    $movie_img->url = $this->file_url(true);
    $movie_img->fp_params = array(); // additional Flowplayer params values (will be json encoded)
    $movie_img->fp_config = array(); // additional Flowplayer config values (will be json encoded)
    $movie_img->view = array();
    module::event("movie_img", $movie_img, $this);

    if (count($movie_img->view) > 0) {
      // View generated - use it
      $view = implode("\n", $movie_img->view);
    } else {
      // View NOT generated - see if filetype supported by Flowplayer v3
      // Note that the extension list below is hard-coded and doesn't use the legal_file helper
      // since anything else will not work in Flowplayer v3.
      if (in_array(strtolower($this->file_extension()),
                   array("flv", "mp4", "m4v", "mov", "f4v"))) {
        // Filetype supported by Flowplayer v3 - use it (default)
        $view = new View("movieplayer.html");
        $view->max_size = $movie_img->max_size;
        $view->width = $movie_img->width;
        $view->height = $movie_img->height;
        $view->attrs = $movie_img->attrs;
        $view->url = $movie_img->url;
        $view->fp_params = $movie_img->fp_params;
        $view->fp_config = $movie_img->fp_config;
      } else {
        // Filetype NOT supported by Flowplayer v3 - display download link
        $attrs = array_merge($attrs, array("style" => "width: {$max_size}px;",
                                           "download" => $this->name, // forces download (HTML5 only)
                                           "class" => "g-movie g-movie-download-link"));
        $view = html::anchor($this->file_url(true), t("Click here to download item."), $attrs);
      }
    }
    return $view;
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
        "resize_extension"    => array("callbacks" => array(array($this, "valid_field"))),
        "slug"                => array("rules"     => array("length[0,255]", "required"),
                                       "callbacks" => array(array($this, "valid_slug"))),
        "sort_column"         => array("callbacks" => array(array($this, "valid_field"))),
        "sort_order"          => array("callbacks" => array(array($this, "valid_field"))),
        "thumb_extension"     => array("callbacks" => array(array($this, "valid_field"))),
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
      if (!$this->is_photo()) {
        // We only care about resizes of photos.
        $this->rules["resize_extension"] = array();
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
    }

    if (rtrim($this->name, ".") !== $this->name) {
      $v->add_error("name", "no_trailing_period");
      return;
    }

    // Do not accept files with double extensions, they can cause problems on some
    // versions of Apache.
    if (!$this->is_album() && substr_count($this->name, ".") > 1) {
      $v->add_error("name", "illegal_data_file_extension");
    }

    if ($this->is_movie() || $this->is_photo()) {
      if (!$this->loaded() && !$this->file_extension()) {
        // New items must have an extension
        $v->add_error("name", "illegal_data_file_extension");
        return;
      }

      if ($this->is_photo()) {
        if (!legal_file::get_photo_extensions($this->file_extension())) {
          $v->add_error("name", "illegal_data_file_extension");
        }
      }

      if ($this->is_movie()) {
        if (!legal_file::get_movie_extensions($this->file_extension())) {
          $v->add_error("name", "illegal_data_file_extension");
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

    if ($this->parent_id == 1 && Kohana::auto_load("{$this->slug}_Controller")) {
      $v->add_error("slug", "reserved");
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
        ->where("type", "<>", "album")
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
        $legal_values = legal_file::get_movie_types();
      } else if ($this->is_photo()) {
        $legal_values = legal_file::get_photo_types();
      }
      break;

    case "resize_extension":
    case "thumb_extension":
      // If we're just validating without saving, these may not be defined.
      if (isset($this->$field)) { 
        $legal_values = legal_file::get_photo_extensions();
        $case_insensitive = true;
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

    if (empty($case_insensitive)) {
      if (isset($legal_values) && !in_array($this->$field, $legal_values)) {
        $v->add_error($field, "invalid");
      }
    } else {
      if (isset($legal_values) && !in_array(strtolower($this->$field), $legal_values)) {
        $v->add_error($field, "invalid");
      }
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
   *
   * @param array if specified, only return the named fields
   */
  public function as_restful_array($fields=array()) {
    if ($fields) {
      $data = array();
      foreach ($fields as $field) {
        if (isset($this->object[$field])) {
          $data[$field] = $this->__get($field);
        }
      }
      $fields = array_flip($fields);
    } else {
      $data = $this->as_array();
    }

    // Convert item ids to rest URLs for consistency
    if (empty($fields) || isset($fields["parent"])) {
      if ($tmp = $this->parent()) {
        $data["parent"] = rest::url("item", $tmp);
      }
      unset($data["parent_id"]);
    }

    if (empty($fields) || isset($fields["album_cover"])) {
      if ($tmp = $this->album_cover()) {
        $data["album_cover"] = rest::url("item", $tmp);
      }
      unset($data["album_cover_item_id"]);
    }

    if (empty($fields) || isset($fields["web_url"])) {
      $data["web_url"] = $this->abs_url();
    }

    if (!$this->is_album()) {
      if (access::can("view_full", $this)) {
        if (empty($fields) || isset($fields["file_url"])) {
          $data["file_url"] = rest::url("data", $this, "full");
        }
        if (empty($fields) || isset($fields["file_size"])) {
          $data["file_size"] = filesize($this->file_path());
        }
        if (access::user_can(identity::guest(), "view_full", $this)) {
          if (empty($fields) || isset($fields["file_url_public"])) {
            $data["file_url_public"] = $this->file_url(true);
          }
        }
      }
    }

    if ($this->is_photo()) {
      if (empty($fields) || isset($fields["resize_url"])) {
        $data["resize_url"] = rest::url("data", $this, "resize");
      }
      if (empty($fields) || isset($fields["resize_size"])) {
        $data["resize_size"] = filesize($this->resize_path());
      }
      if (access::user_can(identity::guest(), "view", $this)) {
        if (empty($fields) || isset($fields["resize_url_public"])) {
          $data["resize_url_public"] = $this->resize_url(true);
        }
      }
    }

    if ($this->has_thumb()) {
      if (empty($fields) || isset($fields["thumb_url"])) {
        $data["thumb_url"] = rest::url("data", $this, "thumb");
      }
      if (empty($fields) || isset($fields["thumb_size"])) {
        $data["thumb_size"] = filesize($this->thumb_path());
      }
      if (access::user_can(identity::guest(), "view", $this)) {
        if (empty($fields) || isset($fields["thumb_url_public"])) {
          $data["thumb_url_public"] = $this->thumb_url(true);
        }
      }
    }

    if (empty($fields) || isset($fields["can_edit"])) {
      $data["can_edit"] = access::can("edit", $this);
    }

    // Elide some internal-only data that is going to cause confusion in the client.
    foreach (array("relative_path_cache", "relative_url_cache", "left_ptr", "right_ptr",
                   "thumb_dirty", "resize_dirty", "weight") as $key) {
      unset($data[$key]);
    }
    return $data;
  }

  /**
   * Increments the view counter of this item
   * We can't use math in ORM or the query builder, so do this by hand.  It's important
   * that we do this with math, otherwise concurrent accesses will damage accuracy.
   */
  public function increment_view_count() {
    db::query("UPDATE {items} SET `view_count` = `view_count` + 1 WHERE `id` = $this->id")
      ->execute();
  }

  private function _cache_buster($path) {
    return "?m=" . (string)(file_exists($path) ? filemtime($path) : 0);
  }
}
