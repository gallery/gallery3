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
class Comment_Helper_Test extends Unit_Test_Case {
  public function create_comment_test() {
    $rand = rand();
    $comment = comment::create($rand, $rand, $rand, $rand, $rand);

    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_equal($rand, $comment->datetime);
  }

  public function create_comment_using_current_time_test() {
    $rand = rand();
    $comment = comment::create($rand, $rand, $rand, $rand);

    $this->assert_equal($rand, $comment->author);
    $this->assert_equal($rand, $comment->email);
    $this->assert_equal($rand, $comment->text);
    $this->assert_equal($rand, $comment->item_id);
    $this->assert_true($comment->datetime > time() - 10 && $comment->datetime <= time());
  }

  public function format_elapsed_time_test() {
    /* This test could be improved by using random numbers and specifically testing corner cases. */
    $now = time();

    $yesterday = $now - comment::SECONDS_IN_A_DAY;
    $daysago = $now - 6 * comment::SECONDS_IN_A_DAY;
    $monthsago = $now - 2 * comment::SECONDS_IN_A_MONTH;
    $yearsago = $now - 5 * comment::SECONDS_IN_A_YEAR;

    $this->assert_equal('said today', comment::format_elapsed_time($now));
    $this->assert_equal('said yesterday', comment::format_elapsed_time($yesterday));
    $this->assert_equal('said 6 days ago', comment::format_elapsed_time($daysago));
    $this->assert_equal('said 2 months ago', comment::format_elapsed_time($monthsago));
    $this->assert_equal('said 5 years ago', comment::format_elapsed_time($yearsago));
  }
}
