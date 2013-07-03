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
class Tag_Model_Tag extends ORM {
  // Set the default sorting.  Use "name" fields as tie-breakers since they're unique.
  protected $_sorting = array("count" => "DESC", "name" => "ASC");

  public function __construct($id=null) {
    parent::__construct($id);

    if (!$this->loaded()) {
      // Set reasonable defaults
      $this->count = 0;
    }
  }

  public function rules() {
    return array(
      "count" => array(
        array("digit")),

      "name" => array(
        array("max_length", array(":value", 128)),
        array("not_empty")),

      "slug" => array(
        array("max_length", array(":value", 128)),
        array("not_empty"),
        array(array($this, "valid_slug"), array(":validation"))),
    );
  }

  /**
   * Validate the tag slug.  It can return the following error messages:
   * - not_url_safe: has illegal characters
   * - conflict: has conflicting slug
   */
  public function valid_slug(Validation $v) {
    if (preg_match("/[^A-Za-z0-9-_]/", $this->slug)) {
      $v->error("slug", "not_url_safe");
    }

    if (DB::select()
        ->from("tags")
        ->where("id", "<>", $this->id)
        ->where("slug", "=", $this->slug)
        ->execute()
        ->count()) {
      $v->error("slug", "conflict");
    }
  }

  /**
   * Overload ORM::save() to trigger an item_related_update event for all items that are related
   * to this tag and to combine duplicate tags.
   */
  public function save(Validation $validation=null) {
    // Check to see if another tag exists with the same name.  Since our DB uses case-insensitive
    // collation, this search is also case-insensitive (and accent-insensitive).
    // @todo: do we need to add an option so some users can have "accent" and "àçcéñt" be distinct?
    $duplicate_tag = ORM::factory("Tag")
      ->where("name", "=", $this->name)
      ->where("id", "!=", $this->id)
      ->find();
    if ($duplicate_tag->loaded()) {
      // If so, tag its items with this tag so as to merge it.
      foreach ($duplicate_tag->items->find_all() as $item) {
        if (!$this->has("items", $item)) {
          // Add the item to the tag without adding it to changed_through.
          $this->add("items", $item, false);
        }
      }

      // If we don't yet have a slug, copy it from the duplicate tag.
      if (empty($this->slug)) {
        $this->slug = $duplicate_tag->slug;
      }

      // Finally, remove the duplicate tag.
      $duplicate_tag->delete();
    }

    // See if we don't yet have a slug, or the name has changed but the slug has not,
    // generate/regenerate the slug from the name.
    if (empty($this->slug) || ($this->changed("name") && !$this->changed("slug"))) {
      // @todo: move these functions out of the Item helper into something more generic.
      $this->slug = Item::convert_filename_to_slug($this->name);

      // If the name is all invalid characters, then the slug may be empty here.  We set a
      // generic name ("tag"), then fix potential conflicts afterward.
      if (empty($this->slug)) {
        $this->slug = "tag";
      }
    }

    // Check for slug conflicts, and fix ours if needed.  Note that, unlike with items, we
    // don't need to look for name conflicts since we've already merged duplicate names.
    // Since our DB uses case-insensitive collation, this search is also case-insensitive.
    $suffix_num = 1;
    $suffix = "";
    while (DB::select("id")
       ->from("tags")
       ->where("id", $this->id ? "<>" : "IS NOT", $this->id)
       ->where("slug", "=", "{$this->slug}{$suffix}")
       ->execute($this->_db)
       ->count()) {
      $suffix = "-" . (($suffix_num <= 99) ? sprintf("%02d", $suffix_num++) : Random::int());
    }
    if ($suffix) {
      $this->slug = "{$this->slug}{$suffix}";
    }

    // Revise the count
    $this->count = $this->items->count_all();

    // If the tag name has changed, all related items are considered changed, too.
    if ($this->changed("name")) {
      $changed_items = $this->items->find_all();
    } else {
      $changed_items = $this->changed_through("items");
    }

    parent::save($validation);

    foreach ($changed_items as $item) {
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Overload ORM::delete() to trigger an item_related_update event for all items that are
   * related to this tag.
   */
  public function delete() {
    $items = $this->items->find_all();
    parent::delete();
    foreach ($items as $item) {
      Module::event("item_related_update", $item);
    }

    return $this;
  }

  /**
   * Return the server-relative url to this item, eg:
   *   /gallery3/index.php/tag/Bob?page=3
   *
   * @param string $query the query string (eg "page=3")
   */
  public function url($query=null) {
    $url = URL::site("tag/{$this->slug}");
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }

  /**
   * Return the full url to this item, eg:
   *   http://example.com/gallery3/index.php/tag/Bob?page=3
   *
   * @param string $query the query string (eg "page=3")
   */
  public function abs_url($query=null) {
    $url = URL::abs_site("tag/{$this->slug}");
    if ($query) {
      $url .= "?$query";
    }
    return $url;
  }
}
