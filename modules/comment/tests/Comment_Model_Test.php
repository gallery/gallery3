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
class Comment_Model_Test extends Unit_Test_Case {

  public function cant_view_comments_for_unviewable_items_test() {
    $root = ORM::factory("item", 1);
    $album = album::create($root, rand(), rand(), rand());
    $comment = comment::create($album, identity::guest(), "text", "name", "email", "url");
    identity::set_active_user(identity::guest());

    // We can see the comment when permissions are granted on the album
    access::allow(identity::everybody(), "view", $album);
    $this->assert_equal(
      1,
      ORM::factory("comment")->viewable()->where("comments.id", $comment->id)->count_all());

    // We can't see the comment when permissions are denied on the album
    access::deny(identity::everybody(), "view", $album);
    $this->assert_equal(
      0,
      ORM::factory("comment")->viewable()->where("comments.id", $comment->id)->count_all());
  }
}
