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
class Gallery_Model_Item extends ORM_MPTT {
  public $data_file = null;
  protected $data_file_error = null;

  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->created = time();
      $this->rand_key = Random::percent();
      $this->thumb_dirty = 1;
      $this->resize_dirty = 1;
      $this->sort_column = "created";
      $this->sort_order = "ASC";
      $this->owner_id = Identity::active_user()->id;
    }

    $this->_set_default_sorting();
  }

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    return Item::viewable($this);
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

  /**
   * Is this the root item?
   * @return true if it's the root item
   */
  public function is_root() {
    return $this->id == Item::root()->id;
  }

  public function delete() {
    if (!$this->loaded()) {
      // Concurrent deletes may result in this item already being gone.  Ignore it.
      return;
    }

    if ($this->is_root()) {
      $v = new Validation(array("id"));
      $v->error("id", "cant_delete_root_album");
      throw new ORM_Validation_Exception($this->object_name(), $v);
    }

    $old = clone $this;
    Module::event("item_before_delete", $this);

    $parent = $this->parent;
    if ($parent->album_cover_item_id == $this->id) {
      Item::remove_album_cover($parent);
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
        throw new Gallery_Exception("Deleting too much: $delete_resize_path, $delete_thumb_path, $path");
      }
      @System::unlink_dir($path);
      @System::unlink_dir($delete_resize_path);
      @System::unlink_dir($delete_thumb_path);
    } else {
      @unlink($path);
      @unlink($resize_path);
      @unlink($thumb_path);
    }

    Module::event("item_deleted", $old);
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
   *   album: /gallery3/index.php/Bobs%20Wedding?page=2
   *   photo: /gallery3/index.php/Bobs%20Wedding/Eating-Cake
   *   movie: /gallery3/index.php/Bobs%20Wedding/First-Dance
   *
   * @param string $query the query string (eg "page=2")
   */
  public function url($query=null) {
    $url = URL::site($this->relative_url());
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * Return the full url to this item, eg:
   *   album: http://example.com/gallery3/index.php/Bobs%20Wedding?page=2
   *   photo: http://example.com/gallery3/index.php/Bobs%20Wedding/Eating-Cake
   *   movie: http://example.com/gallery3/index.php/Bobs%20Wedding/First-Dance
   *
   * @param string $query the query string (eg "page=2")
   */
  public function abs_url($query=null) {
    $url = URL::abs_site($this->relative_url());
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * Return the full path to this item's file, eg:
   *   album: /usr/home/www/gallery3/var/albums/Bobs Wedding
   *   photo: /usr/home/www/gallery3/var/albums/Bobs Wedding/Eating-Cake.jpg
   *   movie: /usr/home/www/gallery3/var/albums/Bobs Wedding/First-Dance.mp4
   */
  public function file_path() {
    return VARPATH . "albums/" . urldecode($this->relative_path());
  }

  /**
   * Return the relative url to this item's file, with cache buster, eg:
   *   album: var/albums/Bobs%20Wedding?m=1234567890
   *   photo: var/albums/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: var/albums/Bobs%20Wedding/First-Dance.mp4?m=1234567890
   * If $full_uri==true, return the full url to this item's file, with cache buster, eg:
   *   album: http://example.com/gallery3/var/albums/Bobs%20Wedding?m=1234567890
   *   photo: http://example.com/gallery3/var/albums/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: http://example.com/gallery3/var/albums/Bobs%20Wedding/First-Dance.mp4?m=1234567890
   */
  public function file_url($full_uri=false) {
    $relative_path = "var/albums/" . $this->relative_path();
    $cache_buster = $this->_cache_buster($this->file_path());
    return ($full_uri ? URL::abs_file($relative_path) : URL::file($relative_path))
      . $cache_buster;
  }

  /**
   * Return the full path to this item's thumb, eg:
   *   album: /usr/home/www/gallery3/var/thumbs/Bobs Wedding/.album.jpg
   *   photo: /usr/home/www/gallery3/var/thumbs/Bobs Wedding/Eating-Cake.jpg
   *   movie: /usr/home/www/gallery3/var/thumbs/Bobs Wedding/First-Dance.jpg
   */
  public function thumb_path() {
    $base = VARPATH . "thumbs/" . urldecode($this->relative_path());
    if ($this->is_photo()) {
      return $base;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg";
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      return LegalFile::change_extension($base, "jpg");
    }
  }

  /**
   * Return true if there is a thumbnail for this item.
   */
  public function has_thumb() {
    return $this->thumb_width && $this->thumb_height;
  }

  /**
   * Return the relative url to this item's thumb, with cache buster, eg:
   *   album: var/thumbs/Bobs%20Wedding/.album.jpg?m=1234567890
   *   photo: var/thumbs/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: var/thumbs/Bobs%20Wedding/First-Dance.mp4?m=1234567890
   * If $full_uri==true, return the full url to this item's file, with cache buster, eg:
   *   album: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/.album.jpg?m=1234567890
   *   photo: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: http://example.com/gallery3/var/thumbs/Bobs%20Wedding/First-Dance.mp4?m=1234567890
   */
  public function thumb_url($full_uri=false) {
    $cache_buster = $this->_cache_buster($this->thumb_path());
    $relative_path = "var/thumbs/" . $this->relative_path();
    $base = ($full_uri ? URL::abs_file($relative_path) : URL::file($relative_path));
    if ($this->is_photo()) {
      return $base . $cache_buster;
    } else if ($this->is_album()) {
      return $base . "/.album.jpg" . $cache_buster;
    } else if ($this->is_movie()) {
      // Replace the extension with jpg
      $base = LegalFile::change_extension($base, "jpg");
      return $base . $cache_buster;
    }
  }

  /**
   * Return the full path to this item's resize, eg:
   *   album: /usr/home/www/gallery3/var/resizes/Bobs Wedding/.album.jpg      (*)
   *   photo: /usr/home/www/gallery3/var/resizes/Bobs Wedding/Eating-Cake.jpg
   *   movie: /usr/home/www/gallery3/var/resizes/Bobs Wedding/First-Dance.mp4 (*)
   * (*) Since only photos have resizes, album and movie paths are fictitious.
   */
  public function resize_path() {
    return VARPATH . "resizes/" . urldecode($this->relative_path()) .
      ($this->is_album() ? "/.album.jpg" : "");
  }

  /**
   * Return the relative url to this item's resize, with cache buster, eg:
   *   album: var/resizes/Bobs%20Wedding/.album.jpg?m=1234567890      (*)
   *   photo: var/resizes/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: var/resizes/Bobs%20Wedding/First-Dance.mp4?m=1234567890 (*)
   * If $full_uri==true, return the full url to this item's file, with cache buster, eg:
   *   album: http://example.com/gallery3/var/resizes/Bobs%20Wedding/.album.jpg?m=1234567890      (*)
   *   photo: http://example.com/gallery3/var/resizes/Bobs%20Wedding/Eating-Cake.jpg?m=1234567890
   *   movie: http://example.com/gallery3/var/resizes/Bobs%20Wedding/First-Dance.mp4?m=1234567890 (*)
   * (*) Since only photos have resizes, album and movie urls are fictitious.
   */
  public function resize_url($full_uri=false) {
    $relative_path = "var/resizes/" . $this->relative_path();
    $cache_buster = $this->_cache_buster($this->resize_path());
    return ($full_uri ? URL::abs_file($relative_path) : URL::file($relative_path)) .
      ($this->is_album() ? "/.album.jpg" : "") . $cache_buster;
  }

  /**
   * Rebuild the relative_path_cache and relative_url_cache.
   */
  protected function _build_relative_caches() {
    $names = array();
    $slugs = array();
    foreach (DB::select("name", "slug")
             ->from("items")
             ->where("left_ptr", "<=", $this->left_ptr)
             ->where("right_ptr", ">=", $this->right_ptr)
             ->where("id", "<>", 1)
             ->order_by("left_ptr", "ASC")
             ->as_object()
             ->execute($this->_db) as $row) {
      // Don't encode the names segment
      $names[] = rawurlencode($row->name);
      $slugs[] = $row->slug;
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
   * @see ORM::get()
   */
  public function get($column) {
    if ($column == "owner") {
      // This relationship depends on an outside module, which may not be present so handle
      // failures gracefully.
      // @TODO: revisit this - it's silly to have a design which allows us to not have an identity
      // provider.
      try {
        return Identity::lookup_user($this->owner_id);
      } catch (Exception $e) {
        return null;
      }
    }

    return parent::get($column);
  }

  /**
   * Set (or reset) the item's default sorting order.  This is used in __construct() and save().
   *
   * @see ORM::_load_result(), which uses $_sorting if no other order_by calls have been applied.
   * @see ORM::sorting(), which sets/gets $_sorting
   * @see ORM_MPTT::get(), which uses this to set the sorting order of children and descendants.
   */
  protected function _set_default_sorting() {
    $sorting[$this->sort_column] = $this->sort_order;
    // Use id as a tie breaker
    if ($this->sort_column != "id") {
      $sorting["id"] = "ASC";
    }
    $this->sorting($sorting);
  }

  /**
   * Handle any business logic necessary to save (i.e. create or update) an item.
   * @see ORM::save()
   */
  public function save(Validation $validation=null) {
    $significant_changes = array_diff($this->changed(), array(
      "view_count", "relative_url_cache", "relative_path_cache", "resize_width", "resize_height",
      "resize_dirty", "thumb_width", "thumb_height", "thumb_dirty"));

    if ($significant_changes || isset($this->data_file)) {
      $this->updated = time();
      parent::save();
      // Now that the sort_order and sort_column are validated, reset the default sorting order.
      $this->_set_default_sorting();
      return $this;
    } else {
      // Insignificant changes only.  Don't fire events or do any special checking to try to keep
      // this lightweight.  This skips our local update() and create() functions.
      return $this->loaded() ? parent::update($validation) : parent::create($validation);
    }
  }

  /**
   * Handle any business logic necessary to create an item.
   * @see ORM::create()
   *
   * @return ORM Model_Item
   */
  public function create(Validation $validation=null) {
    Module::event("item_before_create", $this);

    // Set a weight if it's missing.  We don't do this in the constructor because it's not a
    // simple assignment.
    if (empty($this->weight)) {
      $this->weight = Item::get_max_weight();
    }

    if ($this->is_album()) {
      // Sanitize the album name.
      $this->name = LegalFile::sanitize_dirname($this->name);
    } else {
      // Process the data file info.  This also sanitizes the item name.
      if (isset($this->data_file)) {
        $this->_process_data_file_info();
      } else {
        // New photos and movies must have a data file.
        $this->data_file_error = true;
      }
    }

    // Make an url friendly slug from the name, if necessary
    if (empty($this->slug)) {
      $this->slug = Item::convert_filename_to_slug(pathinfo($this->name, PATHINFO_FILENAME));

      // If the filename is all invalid characters, then the slug may be empty here.  We set a
      // generic name ("photo", "movie", or "album") based on its type, then rely on
      // check_and_fix_conflicts to ensure it doesn't conflict with another name.
      if (empty($this->slug)) {
        $this->slug = $this->type;
      }
    }

    // Give the item a title, if necessary
    if (empty($this->title)) {
      $this->title = Item::convert_filename_to_title($this->name);

      // If the filename got converted away to nothing (e.g. "_.jpg"),
      // revert to using the un-converted filename.
      if (empty($this->title)) {
        $this->title = $this->name;
      }
    }

    $this->_check_and_fix_conflicts();

    parent::create($validation);

    // Build our url caches, then save again.  We have to do this after it's already been
    // saved once because we use only information from the database to build the paths.  If we
    // could depend on a save happening later we could defer this 2nd save.
    $this->_build_relative_caches();
    parent::update($validation);

    // Take any actions that we can only do once all our paths are set correctly after saving.
    switch ($this->type) {
    case "album":
      mkdir($this->file_path());
      mkdir(dirname($this->thumb_path()));
      mkdir(dirname($this->resize_path()));
      break;

    case "photo":
    case "movie":
      copy($this->data_file, $this->file_path());
    break;
    }

    // This will almost definitely trigger another save, so put it at the end so that we're
    // tail recursive.  Null out the data file variable first, otherwise the next save will
    // trigger an item_updated_data_file event.
    $this->data_file = null;
    Module::event("item_created", $this);

    return $this;
  }

  /**
   * Handle any business logic necessary to modify an item.
   * @see ORM::update()
   *
   * @return ORM Model_Item
   */
  public function update(Validation $validation=null) {
    Module::event("item_before_update", $this);

    // If any significant fields have changed, load up a copy of the original item and
    // keep it around.
    $original = ORM::factory("Item", $this->id);

    // If we have a new data file, process its info.  This will get its metadata and
    // preserve the extension of the data file. Many helpers, (e.g. ImageMagick), assume
    // the MIME type from the extension. So when we adopt the new data file, it's important
    // to adopt the new extension. That ensures that the item's extension is always
    // appropriate for its data. We don't try to preserve the name of the data file, though,
    // because the name is typically a temporary randomly-generated name.
    if (isset($this->data_file)) {
      $this->_process_data_file_info();
    } else if (!$this->is_album() && $this->changed("name")) {
      // There's no new data file, but the name changed.  If it's a photo or movie,
      // make sure the new name still agrees with the file type.
      $this->name = LegalFile::sanitize_filename(
        $this->name,
        pathinfo($original->name, PATHINFO_EXTENSION), $this->type);
    }

    // If an album's name changed, sanitize it.
    if ($this->is_album() && $this->changed("name")) {
      $this->name = LegalFile::sanitize_dirname($this->name);
    }

    // If an album's cover has changed (or been removed), delete any existing album cover,
    // reset the thumb metadata, and mark the thumb as dirty.
    if ($this->is_album() && $this->changed("album_cover_item_id")) {
      @unlink($original->thumb_path());
      $this->thumb_dirty = 1;
      $this->thumb_height = 0;
      $this->thumb_width = 0;
    }

    if (array_intersect($this->changed(), array("parent_id", "name", "slug"))) {
      $original->_build_relative_caches();
      $this->relative_path_cache = null;
      $this->relative_url_cache = null;
    }

    $this->_check_and_fix_conflicts();

    parent::update($validation);

    // Now update the filesystem and any database caches if there were significant value
    // changes.  If anything past this point fails, then we'll have an inconsistent database
    // so this code should be as robust as we can make it.

    // Update the MPTT pointers, if necessary.  We have to do this before we generate any
    // cached paths!
    if ($original->parent_id != $this->parent_id) {
      unset($this->parent);
      parent::move_to($this->parent);
    }

    if ($original->parent_id != $this->parent_id || $original->name != $this->name) {
      $this->_build_relative_caches();
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
    }

    // Changing the name, slug or parent ripples downwards
    if ($this->is_album() &&
        ($original->name != $this->name ||
         $original->slug != $this->slug ||
         $original->parent_id != $this->parent_id)) {
      DB::update("items")
        ->set(array("relative_url_cache" => null,
                    "relative_path_cache" => null))
        ->where("left_ptr", ">", $this->left_ptr)
        ->where("right_ptr", "<", $this->right_ptr)
        ->execute($this->_db);
    }

    // Replace the data file, if requested.
    if ($this->data_file && ($this->is_photo() || $this->is_movie())) {
      copy($this->data_file, $this->file_path());
      $this->thumb_dirty = 1;
      $this->resize_dirty = 1;
    }

    if ($original->parent_id != $this->parent_id) {
      // This will result in 2 events since we'll still fire the item_updated event below
      Module::event("item_moved", $this, $original->parent);
    }

    Module::event("item_updated", $original, $this);

    if ($this->data_file) {
      // Null out the data file variable here, otherwise this event will trigger another
      // save() which will think that we're doing another file move.
      $this->data_file = null;
      if ($original->file_path() != $this->file_path()) {
        @unlink($original->file_path());
      }
      Module::event("item_updated_data_file", $this);
    }

    return $this;
  }


  /**
   * Check to see if there's another item that occupies the same name or slug that this item
   * intends to use, and if so choose a new name/slug while preserving the extension.  Since this
   * checks the name without its extension, it covers possible collisions with thumbs and resizes
   * as well (e.g. between the thumbs of movie "foo.flv" and photo "foo.jpg").
   */
  protected function _check_and_fix_conflicts() {
    $suffix_num = 1;
    $suffix = "";
    if ($this->is_album()) {
      while (DB::select("id")
             ->from("items")
             ->where("parent_id", "=", $this->parent_id)
             ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
             ->and_where_open()
             ->where("name", "=", "{$this->name}{$suffix}")
             ->or_where("slug", "=", "{$this->slug}{$suffix}")
             ->and_where_close()
             ->execute($this->_db)
             ->count()) {
        $suffix = "-" . (($suffix_num <= 99) ? sprintf("%02d", $suffix_num++) : Random::int());
      }
      if ($suffix) {
        $this->name = "{$this->name}{$suffix}";
        $this->slug = "{$this->slug}{$suffix}";
        $this->relative_path_cache = null;
        $this->relative_url_cache = null;
      }
    } else {
      // Split the filename into its base and extension.  This uses a regexp similar to
      // LegalFile::change_extension (which isn't always the same as pathinfo).
      if (preg_match("/^(.*)(\.[^\.\/]*?)$/", $this->name, $matches)) {
        $base_name = $matches[1];
        $extension = $matches[2]; // includes a leading dot
      } else {
        $base_name = $this->name;
        $extension = "";
      }
      $base_name_escaped = Database::escape_for_like($base_name);
      // Note: below query uses LIKE with wildcard % at end, which is still sargable (i.e. quick)
      while (DB::select("id")
             ->from("items")
             ->where("parent_id", "=", $this->parent_id)
             ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
             ->and_where_open()
             ->where("name", "LIKE", "{$base_name_escaped}{$suffix}.%")
             ->or_where("slug", "=", "{$this->slug}{$suffix}")
             ->and_where_close()
             ->execute($this->_db)
             ->count()) {
        $suffix = "-" . (($suffix_num <= 99) ? sprintf("%02d", $suffix_num++) : Random::int());
      }
      if ($suffix) {
        $this->name = "{$base_name}{$suffix}{$extension}";
        $this->slug = "{$this->slug}{$suffix}";
        $this->relative_path_cache = null;
        $this->relative_url_cache = null;
      }
    }
  }

  /**
   * Process the data file info.  Get its metadata and extension.
   * If valid, use it to sanitize the item name and update the
   * width, height, and mime type.
   */
  protected function _process_data_file_info() {
    try {
      if ($this->is_photo()) {
        list ($this->width, $this->height, $this->mime_type, $extension) =
          Photo::get_file_metadata($this->data_file);
      } else if ($this->is_movie()) {
        list ($this->width, $this->height, $this->mime_type, $extension) =
          Movie::get_file_metadata($this->data_file);
      } else {
        // Albums don't have data files.
        $this->data_file = null;
        return;
      }

      // Sanitize the name based on the idenified extension, but only set $this->name if different
      // to ensure it isn't unnecessarily marked as "changed"
      $name = LegalFile::sanitize_filename($this->name, $extension, $this->type);
      if ($this->name != $name) {
        $this->name = $name;
      }

      // Data file valid - make sure the flag is reset to false.
      $this->data_file_error = false;
    } catch (Exception $e) {
      // Data file invalid - set the flag so it's reported during item validation.
      $this->data_file_error = true;
    }
  }

  /**
   * Return the Model_Item representing the cover for this album.
   * @return Model_Item or null if there's no cover
   */
  public function album_cover() {
    if (!$this->is_album()) {
      return null;
    }

    if (empty($this->album_cover_item_id)) {
      return null;
    }

    try {
      return ORM::factory("Item", $this->album_cover_item_id);
    } catch (Exception $e) {
      // It's possible (unlikely) that the item was deleted, if so keep going.
      return null;
    }
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
    // HTML::image forces an absolute url which we don't want
    return "<img" . HTML::attributes($attrs) . "/>";
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
    // HTML::image forces an absolute url which we don't want
    return "<img" . HTML::attributes($attrs) . "/>";
  }

  /**
   * Return a view for movies.  By default, this uses MediaElementPlayer on an HTML5-compliant
   * <video> object, but movie_img events can override this and provide their own player/view.
   * If none are found and the player can't play the movie, this returns a simple download link.
   * @param array $extra_attrs
   * @return string
   */
  public function movie_img($extra_attrs) {
    $player_width = Module::get_var("gallery", "resize_size", 640);
    $width = $this->width;
    $height = $this->height;
    if ($width == 0 || $height == 0) {
      // Not set correctly, likely because FFmpeg isn't available.  Making the window 0x0 causes the
      // player to be unviewable during loading.  So, let's guess: set width to player_width and
      // guess a height (using 4:3 aspect ratio).  Once the video metadata is loaded, the player
      // will correct these values.
      $width = $player_width;
      $height = ceil($width * 3/4);
    }
    $div_attrs = array_merge(array("id" => "g-item-id-{$this->id}"), $extra_attrs,
                             array("class" => "g-movie", "style" => "width: {$player_width}px;"));

    // Run movie_img events, which can either:
    //  - generate a view, which is used in place of the standard MediaElementPlayer
    //    (use view variable)
    //  - change the file sent to the player
    //    (use width, height, url, and filename variables)
    //  - alter the arguments sent to the player
    //    (use video_attrs and player_options variables)
    $movie_img = new stdClass();
    $movie_img->width = $width;
    $movie_img->height = $height;
    $movie_img->url = $this->file_url(true);
    $movie_img->filename = $this->name;
    $movie_img->div_attrs = $div_attrs;   // attrs for the outer .g-movie <div>
    $movie_img->video_attrs = array();    // add'l <video> attrs
    $movie_img->player_options = array(); // add'l MediaElementPlayer options (will be json encoded)
    $movie_img->view = array();
    Module::event("movie_img", $movie_img, $this);

    if (count($movie_img->view) > 0) {
      // View generated - use it
      $view = implode("\n", $movie_img->view);
    } else {
      // View not generated - see if the filetype is supported by MediaElementPlayer.
      // Note that the extension list below doesn't use the legal_file helper but rather
      // is hard-coded based on player specifications.
      $extension = strtolower(pathinfo($movie_img->filename, PATHINFO_EXTENSION));
      if (in_array($extension, array("webm", "ogv", "mp4", "flv", "m4v", "mov", "f4v", "wmv"))) {
        // Filetype supported by MediaElementPlayer - use it (default)
        $view = new View("gallery/movieplayer.html");
        $view->width = $movie_img->width;
        $view->height = $movie_img->height;
        $view->div_attrs = $movie_img->div_attrs;
        $view->video_attrs = array_merge(array("controls" => "controls", "autoplay" => "autoplay",
                                         "style" => "max-width: 100%"), $movie_img->video_attrs);
        $view->source_attrs = array("type" => LegalFile::get_movie_types_by_extension($extension),
                                    "src" => $movie_img->url);
        $view->player_options = $movie_img->player_options;
      } else {
        // Filetype not supported by MediaElementPlayer - display download link
        $div_attrs["class"] .= " g-movie-download-link"; // add class
        $div_attrs["download"] = $movie_img->filename;   // force download (HTML5 only)
        $view = HTML::anchor($movie_img->url, t("Click here to download item."), $div_attrs);
      }
    }
    return $view;
  }

  public function rules() {
    $rules = array(
      "album_cover_item_id" => array(
        array(array($this, "valid_album_cover"), array(":validation"))),

      "description" => array(
        array("max_length", array(":value", 65535))),

      "mime_type" => array(
        array(array($this, "valid_field"), array(":validation", ":field"))),

      "name" => array(
        array("max_length", array(":value", 255)),
        array("not_empty"),
        array(array($this, "valid_name"), array(":validation"))),

      "parent_id" => array(
        array(array($this, "valid_parent"), array(":validation"))),

      "rand_key" => array(
        array("regex", array(":value", "/^0[\.,][0-9]+$/D"))),

      "slug" => array(
        array("max_length", array(":value", 255)),
        array("not_empty"),
        array(array($this, "valid_slug"), array(":validation"))),

      "sort_column" => array(
        array(array($this, "valid_field"), array(":validation", ":field"))),

      "sort_order" => array(
        array(array($this, "valid_field"), array(":validation", ":field"))),

      "title" => array(
        array("max_length", array(":value", 255)),
        array("not_empty")),

      "type" => array(
        array(array($this, "read_only"), array(":validation", ":field")),
        array(array($this, "valid_field"), array(":validation", ":field")))
    );

    // Conditional rules
    if ($this->is_root()) {
      // We don't care about the name and slug for the root album.
      $rules["name"] = array();
      $rules["slug"] = array();
    }

    // Movies and photos must have data files.  Verify the data file on new items, or if it has
    // been replaced.
    if (($this->is_photo() || $this->is_movie()) && $this->data_file) {
      $rules["name"][] = array(array($this, "valid_data_file"), array(":validation"));
    }

    return $rules;
  }

  /**
   * Validate the item slug.  It can return the following error messages:
   * - not_url_safe: has illegal characters
   * - conflict: has conflicting slug
   * - reserved (items in root only): has same slug as a controller
   */
  public function valid_slug(Validation $v) {
    if (preg_match("/[^A-Za-z0-9-_]/", $this->slug)) {
      $v->error("slug", "not_url_safe");
    }

    if (DB::select()
        ->from("items")
        ->where("parent_id", "=", $this->parent_id)
        ->where("id", "<>", $this->id)
        ->where("slug", "=", $this->slug)
        ->execute()
        ->count()) {
      $v->error("slug", "conflict");
    }

    if ($this->parent_id == Item::root()->id && Kohana::auto_load("Controller_{$this->slug}")) {
      // @todo: revise this to look for routes instead of just controller names.  It seems that
      // it should use Request::process() (*not* execute()) and Route::name() like:
      //   $processed = Request::factory($this->slug)->process();
      //   if ($processed) {
      //     $route = $processed[1];
      //     if ($route->name() != "item") {
      //       $v->error("slug, "reserved")
      //     }
      //   }
      // ... but I wonder if this will allow an album named "items" (which has no index),
      // then raise issue when it gets a photo called "show", i.e. "items/show".  One solution
      // is to put an empty action_index() in the Controller class, but that seems like a hack...
      $v->error("slug", "reserved");
      return;
    }
  }

  /**
   * Validate the item name.  It can return the following error messages:
   * - no_slashes: contains slashes
   * - no_backslashes: contains backslashes
   * - no_trailing_period: has a trailing period
   * - illegal_data_file_extension (non-albums only): has double, no, or illegal extension
   * - conflict: has conflicting name
   */
  public function valid_name(Validation $v) {
    if (strpos($this->name, "/") !== false) {
      $v->error("name", "no_slashes");
      return;
    }

    if (strpos($this->name, "\\") !== false) {
      $v->error("name", "no_backslashes");
      return;
    }

    if (rtrim($this->name, ".") !== $this->name) {
      $v->error("name", "no_trailing_period");
      return;
    }

    if ($this->is_movie() || $this->is_photo()) {
      if (substr_count($this->name, ".") > 1) {
        // Do not accept files with double extensions, as they can
        // cause problems on some versions of Apache.
        $v->error("name", "illegal_data_file_extension");
      }

      $ext = pathinfo($this->name, PATHINFO_EXTENSION);

      if (!$this->loaded() && !$ext) {
        // New items must have an extension
        $v->error("name", "illegal_data_file_extension");
        return;
      }

      if ($this->is_photo() && !LegalFile::get_photo_extensions($ext) ||
          $this->is_movie() && !LegalFile::get_movie_extensions($ext)) {
        $v->error("name", "illegal_data_file_extension");
      }
    }

    if ($this->is_album()) {
      $query = DB::select()
        ->from("items")
        ->where("parent_id", "=", $this->parent_id)
        ->where("name", "=", $this->name);
      if ($this->id) {
        $query->where("id", "<>", $this->id);
      }
      if ($query->execute()->count()) {
        $v->error("name", "conflict");
      }
    } else {
      if (preg_match("/^(.*)(\.[^\.\/]*?)$/", $this->name, $matches)) {
        $base_name = $matches[1];
      } else {
        $base_name = $this->name;
      }
      $base_name_escaped = Database::escape_for_like($base_name);
      $query = DB::select()
        ->from("items")
        ->where("parent_id", "=", $this->parent_id)
        ->where("name", "LIKE", "{$base_name_escaped}.%");
      if ($this->id) {
        $query->where("id", "<>", $this->id);
      }
      if ($query->execute()->count()) {
        $v->error("name", "conflict");
      }
    }
  }

  /**
   * Make sure that the data file is well formed (it exists and isn't empty).
   */
  public function valid_data_file(Validation $v) {
    if (!is_file($this->data_file)) {
      $v->error("name", "bad_data_file_path");
    } else if (filesize($this->data_file) == 0) {
      $v->error("name", "empty_data_file");
    } else if ($this->data_file_error) {
      $v->error("name", "invalid_data_file");
    }
  }

  /**
   * Make sure that the parent id refers to an album.
   */
  public function valid_parent(Validation $v) {
    if ($this->is_root()) {
      if ($this->parent_id != 0) {
        $v->error("parent_id", "invalid");
      }
    } else {
      $parent = $this->parent;
      if (!$parent->loaded() || !$parent->is_album()) {
        $v->error("parent_id", "invalid");
      }

      // If this is an existing item, make sure the new parent is not part of our hierarchy
      if ($this->loaded()) {
        if ($this->descendants->where("id", "=", $parent->id)->find()->loaded()) {
          $v->error("parent_id", "invalid");
        }
      }
    }
  }

  /**
   * Make sure the album cover item id refers to a valid item, or is null.
   */
  public function valid_album_cover(Validation $v) {
    if ($this->is_root()) {
      return;
    }

    if ($this->album_cover_item_id &&
        ($this->is_photo() || $this->is_movie() ||
         DB::select()
         ->from("items")
         ->where("id", "=", $this->album_cover_item_id)
         ->where("type", "<>", "album")
         ->execute()
         ->count() != 1)) {
      $v->error("album_cover_item_id", "invalid_item");
    }
  }

  /**
   * Make sure that the type is valid.
   */
  public function valid_field(Validation $v, $field) {
    switch($field) {
    case "mime_type":
      if ($this->is_movie()) {
        $legal_values = LegalFile::get_movie_types();
      } else if ($this->is_photo()) {
        $legal_values = LegalFile::get_photo_types();
      }
      break;

    case "sort_column":
      if (!array_key_exists($this->sort_column, $this->table_columns())) {
        $v->error($field, "invalid");
      }
      break;

    case "sort_order":
      $legal_values = array("ASC", "DESC", "asc", "desc");
      break;

    case "type":
      $legal_values = array("album", "photo", "movie");
      break;

    default:
      $v->error($field, "unvalidated_field");
      break;
    }

    if (isset($legal_values) && !in_array($this->$field, $legal_values)) {
      $v->error($field, "invalid");
    }
  }

  /**
   * This field cannot be changed after it's been set.
   */
  public function read_only(Validation $v, $field) {
    if ($this->loaded() && $this->changed($field)) {
      $v->error($field, "read_only");
    }
  }

  /**
   * Increments the view counter of this item
   * We can't use math in ORM or the query builder, so do this by hand.  It's important
   * that we do this with math, otherwise concurrent accesses will damage accuracy.
   */
  public function increment_view_count() {
    DB::query(Database::UPDATE, "UPDATE {items} SET `view_count` = `view_count` + 1 WHERE `id` = $this->id")
      ->execute($this->_db);
  }

  protected function _cache_buster($path) {
    return "?m=" . (string)(file_exists($path) ? filemtime($path) : 0);
  }
}
