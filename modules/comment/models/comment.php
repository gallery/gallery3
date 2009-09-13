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
class Comment_Model extends ORM {
  function item() {
    return ORM::factory("item", $this->item_id);
  }

  function author() {
    return user::lookup($this->author_id);
  }

  function author_name() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_name;
    } else {
      return $author->display_name();
    }
  }

  function author_email() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_email;
    } else {
      return $author->email;
    }
  }

  function author_url() {
    $author = $this->author();
    if ($author->guest) {
      return $this->guest_url;
    } else {
      return $author->url;
    }
  }

  /**
   * @see ORM::save()
   */
  public function save() {
    if (!empty($this->changed)) {
      $this->updated = time();
      if (!$this->loaded && empty($this->created)) {
        $this->created = $this->updated;
        $created = true;
      }
    }
    $visible_change = $this->original()->state == "published" || $this->state == "published";
    parent::save();

    if (isset($created)) {
      module::event("comment_created", $this);
    } else {
      module::event("comment_updated", $this->original(), $this);
    }

    // We only notify on the related items if we're making a visible change.
    if ($visible_change) {
      module::event("item_related_update", $this->item());
    }

    return $this;
  }

  /**
   * Add a set of restrictions to any following queries to restrict access only to items
   * viewable by the active user.
   * @chainable
   */
  public function viewable() {
    $this->join("items", "items.id", "comments.item_id");
    return item::viewable($this);
  }
}
