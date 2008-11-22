<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
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
class Atom_Entry_Core extends Atom_Base {
  public function id($id) {
    $this->element->appendChild($this->dom->createElement("id", $id));
    return $this;
  }

  public function updated($updated) {
    $this->element->appendChild($this->dom->createElement("updated", $updated));
    return $this;
  }

  public function title($title) {
    $this->element->appendChild($this->dom->createElement("title", $title));
    return $this;
  }

  public function content($text, $type="html") {
    $content = $this->dom->createElement("content", $text);
    $content->setAttribute("type", $type);
    $this->element->appendChild($content);
    return $this;
  }

  public function author() {
    return $this->add_child("Atom_Author", "author");
  }

  public function link() {
    return $this->add_child("Atom_Link", "link");
  }
}
