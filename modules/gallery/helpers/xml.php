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
class xml_Core {
  static function to_xml($array, $element_names) {
    $xml = "<$element_names[0]>\n";
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $xml .= xml::to_xml($value, array_slice($element_names, 1));
      } else if (is_object($value)) {
        $xml .= xml::to_xml($value->as_array(), array_slice($element_names, 1));
      } else {
        $xml .= "<$key>$value</$key>\n";
      }
    }
    $xml .= "</$element_names[0]>\n";
    return $xml;
  }
}
