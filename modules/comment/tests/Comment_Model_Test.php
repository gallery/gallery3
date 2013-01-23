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
class Comment_Model_Test extends Gallery_Unit_Test_Case {
  public function teardown() {
    identity::set_active_user(identity::admin_user());
  }

  public function guest_name_and_email_is_required_test() {
    try {
      $comment = ORM::factory("comment");
      $comment->item_id = item::root()->id;
      $comment->author_id = identity::guest()->id;
      $comment->text = "text";
      $comment->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("guest_name" => "required",
                                "guest_email" => "required"),
                          $e->validation->errors());
      return;
    }
  }

  public function guest_email_must_be_well_formed_test() {
    try {
      $comment = ORM::factory("comment");
      $comment->item_id = item::root()->id;
      $comment->author_id = identity::guest()->id;
      $comment->guest_name = "guest";
      $comment->guest_email = "bogus";
      $comment->text = "text";
      $comment->save();
    } catch (ORM_Validation_Exception $e) {
      $this->assert_equal(array("guest_email" => "invalid"),
                          $e->validation->errors());
      return;
    }
  }

  public function cant_view_comments_for_unviewable_items_test() {
    $album = test::random_album();

    $comment = ORM::factory("comment");
    $comment->item_id = $album->id;
    $comment->author_id = identity::admin_user()->id;
    $comment->text = "text";
    $comment->save();

    identity::set_active_user(identity::guest());

    // We can see the comment when permissions are granted on the album
    access::allow(identity::everybody(), "view", $album);
    $this->assert_true(
      ORM::factory("comment")->viewable()->where("comments.id", "=", $comment->id)->count_all());

    // We can't see the comment when permissions are denied on the album
    access::deny(identity::everybody(), "view", $album);
    $this->assert_false(
      ORM::factory("comment")->viewable()->where("comments.id", "=", $comment->id)->count_all());
  }
}
