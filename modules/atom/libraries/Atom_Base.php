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

class Atom_Base_Core {
  protected $dom;
  protected $element;
  protected $children = array();
  protected $element_name;

  function __construct($element_name, $dom=null) {
    if ($dom) {
      $this->dom = $dom;
      $this->element = $dom->createElement($element_name);
    } else {
      $this->dom = new DOMDocument('1.0', 'utf-8');
      $this->element = $this->dom->createElementNS("http://www.w3.org/2005/Atom", $element_name);
    }
    $this->dom->appendChild($this->element);
    $this->element_name = $element_name;
    return $this;
  }

  public function get_element() {
    $this->add_children_to_base_element();
    return $this->element;
  }

  public function as_xml() {
    $this->add_children_to_base_element();
    $this->dom->formatOutput = true;
    return $this->dom->saveXML();
  }

  public function as_json() {
    $this->add_children_to_base_element();
    /* Both Google and Yahoo generate their JSON from XML. We could do that, too. */
    return null;
  }

  public function load_xml($xml) {
    /* Load XML into our DOM. We can also validate against the RELAX NG schema from the Atom RFC. */
  }

  protected function add_child($element_type, $element_name) {
    // @todo check if element_type is of Atom_Base; this can also be done with no magic
    $element = new $element_type($element_name, $this->dom);
    $this->children[$element_name][] = $element;
    return end($this->children[$element_name]);
  }

  protected function add_children_to_base_element() {
    foreach ($this->children as $element_type => $elements) {
      $base_element = $this->element;
      foreach ($elements as $id => $element) {
        $base_element->appendChild($element->get_element());
      }
    }
  }
}