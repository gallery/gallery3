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
class Exif_Test extends Unit_Test_Case {
  public function exif_extract_test() {
    $rand = rand();
    $root = ORM::factory("item", 1);
    $photo = photo::create(
      $root, DOCROOT . "modules/exif/tests/data/image.jpg", "$rand.jpg", $rand, $rand);

    $expected = array(
      array("caption" => "Camera Maker", "value" => "Pentax Corporation"),
      array("caption" => "Camera Model", "value" => "PENTAX K10D"),
      array("caption" => "Aperture", "value" => "f/2.8"),
      array("caption" => "Color Space", "value" => "Uncalibrated"),
      array("caption" => "Exposure Value", "value" => "4294.67 EV"),
      array("caption" => "Exposure Program", "value" => "Program"),
      array("caption" => "Flash", "value" => "No Flash"),
      array("caption" => "Focal Length", "value" => "50 mm"),
      array("caption" => "ISO", "value" => "6553700"),
      array("caption" => "Metering Mode", "value" => "Multi-Segment"),
      array("caption" => "Shutter Speed", "value" => "1/60 sec"),
      array("caption" => "Date/Time", "value" => "2008:03:17 17:41:25"),
      array("caption" => "Copyright", "value" => "(C) 2008 -  T. Almdal"),
      array("caption" => "Orientation", "value" => "1: Normal (0 deg)"),
      array("caption" => "Resolution Unit", "value" => "Inch"),
      array("caption" => "X Resolution", "value" => "240 dots per ResolutionUnit"),
      array("caption" => "Y Resolution", "value" => "240 dots per ResolutionUnit"),
      array("caption" => "Brightness Value", "value" => "0"),
      array("caption" => "Scene Type", "value" => "0"),
      array("caption" => "Subject Distance", "value" => "0"),
    );
    $this->assert_equal($expected, exif::get($photo));
  }
}