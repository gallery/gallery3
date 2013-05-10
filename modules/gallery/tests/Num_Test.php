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
class Num_Test extends Unittest_TestCase {
  public function test_convert_to_bytes() {
    $this->assertEquals(5 * 1024, Num::convert_to_bytes("5K"));
    $this->assertEquals(3 * 1024*1024, Num::convert_to_bytes("3M"));
    $this->assertEquals(4 * 1024*1024*1024, Num::convert_to_bytes("4G"));
  }

  public function test_convert_to_human_readable() {
    $this->assertEquals("6K", Num::convert_to_human_readable(5615));
    $this->assertEquals("1M", Num::convert_to_human_readable(1205615));
    $this->assertEquals("3G", Num::convert_to_human_readable(3091205615));
  }
}