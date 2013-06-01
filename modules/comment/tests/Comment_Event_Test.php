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
class Comment_Event_Test extends Unittest_TestCase {
  public function test_deleting_an_item_deletes_its_comments_too() {
    $album = Test::random_album();

    $comment = ORM::factory("Comment");
    $comment->item_id = $album->id;
    $comment->author_id = Identity::guest()->id;
    $comment->guest_name = "test";
    $comment->guest_email = "test@test.com";
    $comment->text = "text";
    $comment->server_name = "server_name";
    $comment->save();

    $album->delete();

    $this->assertFalse(ORM::factory("Comment", $comment->id)->loaded());
  }
}
