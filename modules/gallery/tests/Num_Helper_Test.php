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
class Num_Helper_Test extends Gallery_Unit_Test_Case {
  public function convert_to_bytes_test() {
    $this->assert_equal(5 * 1024, num::convert_to_bytes("5K"));
    $this->assert_equal(3 * 1024*1024, num::convert_to_bytes("3M"));
    $this->assert_equal(4 * 1024*1024*1024, num::convert_to_bytes("4G"));
  }

  public function convert_to_human_readable_test() {
    $this->assert_equal("6K", num::convert_to_human_readable(5615));
    $this->assert_equal("1M", num::convert_to_human_readable(1205615));
    $this->assert_equal("3G", num::convert_to_human_readable(3091205615));
  }
}