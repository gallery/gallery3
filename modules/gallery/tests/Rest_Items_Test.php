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
class Rest_Items_Test extends Unittest_TestCase {
  public function test_get_response() {
    $user = Identity::admin_user();
    Identity::set_active_user($user);

    $root = Item::root();
    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $tag = Tag::add($album, Test::random_name());
    $album->reload();

    $rest = Rest::factory("Items", $album->id);

    $expected = array(
      "url" => URL::abs_site("rest/items/{$album->id}"),
      "entity" => array(
        "id"               => $album->id,
        "captured"         => null,
        "created"          => $album->created,
        "description"      => null,
        "level"            => $album->level,
        "mime_type"        => null,
        "name"             => $album->name,
        "rand_key"         => $album->rand_key,
        "slug"             => $album->slug,
        "sort_column"      => "created",
        "sort_order"       => "ASC",
        "thumb_height"     => $album->thumb_height,
        "thumb_width"      => $album->thumb_width,
        "title"            => $album->title,
        "type"             => "album",
        "updated"          => $album->updated,
        "view_count"       => $album->view_count,
        "parent"           => URL::abs_site("rest/items/{$root->id}"),
        "album_cover"      => URL::abs_site("rest/items/{$photo->id}"),
        "owner"            => URL::abs_site("rest/users/{$user->id}"),
        "thumb_url"        => URL::abs_site("rest/data/{$album->id}?size=thumb&m=") . filemtime($album->thumb_path()),
        "thumb_size"       => filesize($album->thumb_path()),
        "thumb_url_public" => $album->thumb_url(true),
        "can_edit"         => true,
        "can_add"          => true,
        "web_url"          => $album->abs_url()),
      "members" => array(
        0 => URL::abs_site("rest/items/{$photo->id}")),
      "members_info" => array(
        "count" => 1,
        "num" => 100,
        "start" => 0),
      "relationships" => array(
        "comments" => array(
          "url" => URL::abs_site("rest/item_comments/{$album->id}"),
          "members" => array(),
          "members_info" => array(
            "count" => 0,
            "num" => 100,
            "start" => 0)),
        "tags" => array(
          "url" => URL::abs_site("rest/item_tags/{$album->id}"),
          "entity" => array(
            "names" => $tag->name),
          "members" => array(
            0 => URL::abs_site("rest/tags/{$tag->id}")),
          "members_info" => array(
            "count" => 1,
            "num" => 100,
            "start" => 0))));

    $this->assertEquals($expected, $rest->get_response());
  }

  public function test_get_entity_with_edit_bit() {
    $rest = Rest::factory("Items", Item::root()->id);

    Identity::set_active_user(Identity::admin_user());
    $this->assertTrue(Arr::get($rest->get_entity(), "can_edit"));

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "edit", Item::root());
    Identity::set_active_user(Identity::guest());
    $this->assertFalse(Arr::get($rest->get_entity(), "can_edit"));
  }

  public function test_get_entity_with_add_bit() {
    $rest = Rest::factory("Items", Item::root()->id);

    Identity::set_active_user(Identity::admin_user());
    $this->assertTrue(Arr::get($rest->get_entity(), "can_edit"));

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "add", Item::root());
    Identity::set_active_user(Identity::guest());
    $this->assertFalse(Arr::get($rest->get_entity(), "can_add"));
  }

  public function test_get_entity_with_blocked_user_profile() {
    Module::set_var("gallery", "show_user_profiles_to", "registered_users");
    $rest = Rest::factory("Items", Item::root()->id);

    Identity::set_active_user(Identity::admin_user());
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "owner"));

    Identity::set_active_user(Identity::guest());
    $this->assertEmpty(Arr::get($rest->get_entity(), "owner"));
  }

  public function test_get_entity_without_view_full_access() {
    Identity::set_active_user(Identity::guest());

    $photo = Test::random_photo();
    $rest = Rest::factory("Items", $photo->id);

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "view_full", Item::root());
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "file_url"));

    Access::deny(Identity::everybody(), "view_full", Item::root());
    $this->assertSame(null, Arr::get($rest->get_entity(), "file_url"));
  }

  public function test_get_entity_without_public_view_access() {
    Identity::set_active_user(Identity::admin_user());

    $photo = Test::random_photo();
    $rest = Rest::factory("Items", $photo->id);

    Access::allow(Identity::everybody(), "view", Item::root());
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url"));
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url_public"));

    Access::deny(Identity::everybody(), "view", Item::root());
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url"));
    $this->assertEmpty(Arr::get($rest->get_entity(), "thumb_url_public"));
  }

  public function test_get_entity_with_missing_data_files() {
    Identity::set_active_user(Identity::admin_user());

    $photo = Test::random_photo();
    unlink($photo->file_path());
    unlink($photo->resize_path());
    unlink($photo->thumb_path());

    $entity = Rest::factory("Items", $photo->id)->get_entity();

    $this->assertSame("&m=0", substr(Arr::get($entity, "file_url"), -4));
    $this->assertSame("&m=0", substr(Arr::get($entity, "resize_url"), -4));
    $this->assertSame("&m=0", substr(Arr::get($entity, "thumb_url"), -4));
    $this->assertSame(0, Arr::get($entity, "file_size"));
    $this->assertSame(0, Arr::get($entity, "resize_size"));
    $this->assertSame(0, Arr::get($entity, "thumb_size"));
  }

  public function test_get_entity_different_sizes_for_different_types() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();
    $photo = Test::random_photo($album);
    $movie = Test::random_movie($album);

    $rest = Rest::factory("Items", $album->id);
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url"));
    $this->assertEmpty(Arr::get($rest->get_entity(), "resize_url"));
    $this->assertEmpty(Arr::get($rest->get_entity(), "file_url"));

    $rest = Rest::factory("Items", $movie->id);
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url"));
    $this->assertEmpty(Arr::get($rest->get_entity(), "resize_url"));
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "file_url"));

    $rest = Rest::factory("Items", $photo->id);
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "thumb_url"));
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "resize_url"));
    $this->assertNotEmpty(Arr::get($rest->get_entity(), "file_url"));
  }

  public function test_get_members() {
    Identity::set_active_user(Identity::admin_user());

    $album1 = Test::random_album();
    $photo2 = Test::random_photo($album1);
    $album2 = Test::random_album($album1);
    $photo3 = Test::random_photo($album2);
    $album1->reload();
    $album2->reload();

    $rest_album1 = Rest::factory("Items", $album1->id);
    $rest_photo2 = Rest::factory("Items", $photo2->id);
    $rest_album2 = Rest::factory("Items", $album2->id);
    $rest_photo3 = Rest::factory("Items", $photo3->id);

    // Get with no query params
    $members = Rest::factory("Items", $album1->id)->get_members();
    $this->assertSame(0,     array_search($rest_photo2, $members));
    $this->assertSame(1,     array_search($rest_album2, $members));
    $this->assertSame(false, array_search($rest_photo3, $members));

    // Get with "type=album" query param - only album2
    $members = Rest::factory("Items", $album1->id, array("type" => array("album")))->get_members();
    $this->assertSame(false, array_search($rest_photo2, $members));
    $this->assertSame(0,     array_search($rest_album2, $members));
    $this->assertSame(false, array_search($rest_photo3, $members));

    // Get with "name" query param - only photo2 (use its name)
    $members = Rest::factory("Items", $album1->id, array("name" => $photo2->name))->get_members();
    $this->assertSame(0,     array_search($rest_photo2, $members));
    $this->assertSame(false, array_search($rest_album2, $members));
    $this->assertSame(false, array_search($rest_photo3, $members));

    // Get with "scope=all" query param - all three items
    $members = Rest::factory("Items", $album1->id, array("scope" => "all"))->get_members();
    $this->assertSame(0, array_search($rest_photo2, $members));
    $this->assertSame(1, array_search($rest_album2, $members));
    $this->assertSame(2, array_search($rest_photo3, $members));
  }

  public function test_get_members_with_urls_param() {
    Identity::set_active_user(Identity::admin_user());

    $item1 = Test::random_album();
    $item2 = Test::random_photo();

    $rest1 = Rest::factory("Items", $item1->id);
    $rest2 = Rest::factory("Items", $item2->id);

    // The order is intentionally reversed to show that sort doesn't matter.

    // Get with no other query params.
    $members = Rest::factory("Items", null,
        array("urls" => json_encode(array($rest2->url(), $rest1->url())))
      )->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, array_search($rest1, $members));

    // Get with no other query params, using a comma-separated list.
    $members = Rest::factory("Items", null,
        array("urls" => implode(",", array($rest2->url(), $rest1->url())))
      )->get_members();
    $this->assertSame(0, array_search($rest2, $members));
    $this->assertSame(1, array_search($rest1, $members));

    // Get with "type=album" query param - only item1
    $members = Rest::factory("Items", null,
        array("urls" => json_encode(array($rest2->url(), $rest1->url())), "type" => array("album"))
      )->get_members();
    $this->assertSame(false, array_search($rest2, $members));
    $this->assertSame(0,     array_search($rest1, $members));

    // Get with "type=album" query param - only item2
    $members = Rest::factory("Items", null,
        array("urls" => json_encode(array($rest2->url(), $rest1->url())), "name" => $item2->name)
      )->get_members();
    $this->assertSame(0,     array_search($rest2, $members));
    $this->assertSame(false, array_search($rest1, $members));
  }

  public function test_get_members_with_ancestors_for_param() {
    Identity::set_active_user(Identity::admin_user());

    $item1 = Test::random_album();
    $item2 = Test::random_album($item1);
    $item3 = Test::random_photo($item2);

    $rest0 = Rest::factory("Items", Item::root()->id);
    $rest1 = Rest::factory("Items", $item1->id);
    $rest2 = Rest::factory("Items", $item2->id);
    $rest3 = Rest::factory("Items", $item3->id);

    // Get with no other query params (type and name aren't accepted here anyway)
    $members = Rest::factory("Items", null, array("ancestors_for" => $rest3->url()))->get_members();
    $this->assertSame(0, array_search($rest0, $members));
    $this->assertSame(1, array_search($rest1, $members));
    $this->assertSame(2, array_search($rest2, $members));
  }

  public function test_get_members_expand_members_defaults() {
    Identity::set_active_user(Identity::admin_user());
    $item = Test::random_album();

    // Normally it's false.
    $rest1 = Rest::factory("Items", $item->id);
    $this->assertFalse($rest1->default_params["expand_members"]);

    // The "urls" param makes it true.
    $rest2 = Rest::factory("Items", null, array("urls" => json_encode(array($rest1->url()))));
    $rest2->get_response();  // since "urls" parsed in get_response()
    $this->assertTrue($rest2->default_params["expand_members"]);

    // The "ancestors_for" param makes it true, too.
    $rest3 = Rest::factory("Items", null, array("ancestors_for" => $rest1->url()));
    $rest3->get_response();  // since "ancestors_for" parsed in get_response()
    $this->assertTrue($rest3->default_params["expand_members"]);
  }

  public function test_get_members_with_id_set_by_default_or_random_param() {
    Identity::set_active_user(Identity::admin_user());

    // Make it (very nearly) impossible for the root item to be randomly selected.
    $root = Item::root();
    $root->rand_key = "0.9999999999"; // highest legal value
    $root->save();

    // Normally an unset id defaults to root if Rest::get_response() called.
    $rest = Rest::factory("Items", null);
    $rest->get_response();
    $this->assertSame(Item::root()->id, $rest->id);

    // However, random sets the id randomly.
    $rest = Rest::factory("Items", null, array("random" => true));
    $rest->get_response();
    $this->assertNotSame(Item::root()->id, $rest->id);
  }

  public function test_get_members_with_weights() {
    Identity::set_active_user(Identity::admin_user());

    // Make child1 created first, but weighted last.
    $album = Test::random_album();
    $child1 = Test::random_album_unsaved($album);
    $child1->weight = 321;
    $child1->save();
    $child2 = Test::random_photo_unsaved($album);
    $child2->weight = 123;
    $child2->save();

    $rest0 = Rest::factory("Items", $album->id);
    $rest1 = Rest::factory("Items", $child1->id);
    $rest2 = Rest::factory("Items", $child2->id);

    // Get with ordering by created (default)
    $members = $rest0->get_members();
    $this->assertSame(0, array_search($rest1, $members));
    $this->assertSame(1, array_search($rest2, $members));
    $this->assertEquals(array(0, 1), array_keys($members));  // child1 (0) is first

    // Change ordering
    $album->sort_column = "weight";
    $album->save();

    // Get with ordering by weights
    $members = $rest0->get_members();
    $this->assertSame(321, array_search($rest1, $members));
    $this->assertSame(123, array_search($rest2, $members));
    $this->assertEquals(array(123, 321), array_keys($members));  // child2 (123) is first
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_with_invalid_item() {
    Identity::set_active_user(Identity::admin_user());
    Access::allow(Identity::everybody(), "view", Item::root());  // ensure it's not an access error
    $item = Test::random_album();

    $id = $item->id;
    $item->delete();

    Rest::factory("Items", $id)->get_entity();
  }

  /**
   * @expectedException HTTP_Exception_404
   */
  public function test_get_entity_without_view_access() {
    Identity::set_active_user(Identity::guest());
    Access::deny(Identity::everybody(), "view", Item::root());
    $item = Test::random_album();

    Rest::factory("Items", $item->id)->get_entity();
  }

  public function test_put_entity_album() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();        // parent should be root
    $cover = Test::random_photo($album);  // should become album cover
    $new_cover  = Test::random_photo($album);

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name   = "new{$cover->name}";
    $params["entity"]->weight = 1234567890;  // this will be ignored - not on whitelist
    $params["entity"]->album_cover = Rest::factory("Items", $new_cover->id)->url();

    $rest = Rest::factory("Items", $album->id, $params);
    $rest->put_entity();
    $album->reload();

    $this->assertEquals("new{$cover->name}", $album->name);
    $this->assertNotEquals(1234567890, $album->weight);
    $this->assertEquals($new_cover->id, $album->album_cover_item_id);
  }

  public function test_put_entity_photo() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $photo = Test::random_photo($album);             // black dot
    $new_photo = Test::random_unique_photo($album);  // color dot - we'll make this the data file.
    $new_parent = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name   = "new{$new_photo->name}";
    $params["entity"]->weight = 1234567890;  // this will be ignored - not on whitelist
    $params["entity"]->parent = Rest::factory("Items", $new_parent->id)->url();
    $params["file"] = array(
        "name"     => "ignored_name",
        "tmp_name" => $new_photo->file_path(),
        "size"     => filesize($new_photo->file_path()),
        "type"     => "image/jpeg",
        "error"    => UPLOAD_ERR_OK
      );

    $rest = Rest::factory("Items", $photo->id, $params);
    $rest->put_entity();
    $photo->reload();

    $this->assertEquals("new{$new_photo->name}", $photo->name);
    $this->assertNotEquals(1234567890, $photo->weight);
    $this->assertEquals($new_parent->id, $photo->parent->id);
    $this->assertEquals(file_get_contents($new_photo->file_path()),
                        file_get_contents($photo->file_path()));
  }

  /**
   * @expectedException ORM_Validation_Exception
   */
  public function test_put_entity_album_illegal_value_fails() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = "valid{$album->name}";
    $params["entity"]->slug = "invalid {$album->slug} with spaces";

    Rest::factory("Items", $album->id, $params)->put_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_entity_album_illegal_album_cover_fails() {
    // The Model_Item logic checks if the cover id is valid - we're just making sure we
    // don't let non-item ids get put in there.  So, we use the *same* already-valid cover id,
    // but call it a tag.
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->album_cover = Rest::factory("Tags", $album->album_cover_item_id)->url();

    Rest::factory("Items", $album->id, $params)->put_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_entity_album_illegal_parent_fails() {
    // The Model_Item logic checks if the parent id is valid - we're just making sure we
    // don't let non-item ids get put in there.  So, we use the *same* already-valid parent id,
    // but call it a tag.
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->parent = Rest::factory("Tags", $album->parent->id)->url();

    Rest::factory("Items", $album->id, $params)->put_entity();
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_put_entity_without_edit_access() {
    Identity::set_active_user(Identity::admin_user());
    $album = Test::random_album();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->name = "valid{$album->name}";

    Identity::set_active_user(Identity::guest());
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());

    Rest::factory("Items", $album->id, $params)->put_entity();
  }

  public function test_post_entity_album() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "album";
    $params["entity"]->weight = 1234567890;  // this will be ignored - not on whitelist
    $params["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params["entity"]->name = $name;

    $rest = Rest::factory("Items", null, $params);
    $rest->post_entity();
    $album = ORM::factory("Item", $rest->id);

    $this->assertTrue($album->loaded());
    $this->assertTrue($album->is_album());
    $this->assertEquals($name, $album->name);
    $this->assertNotEquals(1234567890, $album->weight);
    $this->assertEquals($parent->id, $album->parent->id);
  }

  public function test_post_entity_photo() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $other_photo = Test::random_unique_photo($album);  // color dot - we'll make this the data file.

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "photo";
    $params["entity"]->weight = 1234567890;  // this will be ignored - not on whitelist
    $params["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params["file"] = array(
        "name"     => "new{$other_photo->name}",
        "tmp_name" => $other_photo->file_path(),
        "size"     => filesize($other_photo->file_path()),
        "type"     => "image/jpeg",
        "error"    => UPLOAD_ERR_OK
      );

    $rest = Rest::factory("Items", null, $params);
    $rest->post_entity();
    $photo = ORM::factory("Item", $rest->id);

    $this->assertTrue($photo->loaded());
    $this->assertTrue($photo->is_photo());
    $this->assertEquals("new{$other_photo->name}", $photo->name);
    $this->assertNotEquals(1234567890, $photo->weight);
    $this->assertEquals($parent->id, $photo->parent->id);
    $this->assertEquals(file_get_contents($other_photo->file_path()),
                        file_get_contents($photo->file_path()));
  }

  /**
   * @expectedException ORM_Validation_Exception
   */
  public function test_post_entity_album_illegal_value_fails() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "album";
    $params["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params["entity"]->name = $name;
    $params["entity"]->slug = "slugs cannot have spaces";

    Rest::factory("Items", null, $params)->post_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_post_entity_album_missing_type_fails() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "invalid_type";
    $params["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params["entity"]->name = $name;

    Rest::factory("Items", null, $params)->post_entity();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_post_entity_album_illegal_parent_fails() {
    // The Model_Item logic checks if the parent id is valid - we're just making sure we
    // don't let non-item ids get put in there.  So, we use the valid parent id, but call it a tag.
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "album";
    $params["entity"]->parent = Rest::factory("Tags", $parent->id)->url();
    $params["entity"]->name = $name;

    Rest::factory("Items", null, $params)->post_entity();
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_post_entity_without_add_access() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params = array();
    $params["entity"] = new stdClass();
    $params["entity"]->type = "album";
    $params["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params["entity"]->name = $name;

    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::deny(Identity::everybody(), "add", Item::root());
    Identity::set_active_user(Identity::guest());

    Rest::factory("Items", null, $params)->post_entity();
  }

  public function test_post_entity_album_parent_as_id_or_field() {
    Identity::set_active_user(Identity::admin_user());

    $parent = Test::random_album();
    $name = Test::random_name();

    $params1 = array();
    $params1["entity"] = new stdClass();
    $params1["entity"]->type = "album";
    $params1["entity"]->parent = Rest::factory("Items", $parent->id)->url();
    $params1["entity"]->name = "{$name}1";

    $params2 = array();
    $params2["entity"] = new stdClass();
    $params2["entity"]->type = "album";
    $params2["entity"]->name = "{$name}2";

    $rest1 = Rest::factory("Items", null,        $params1);
    $rest2 = Rest::factory("Items", $parent->id, $params2);
    $rest1->post_entity();
    $rest2->post_entity();
    $album1 = ORM::factory("Item", $rest1->id);
    $album2 = ORM::factory("Item", $rest2->id);

    $this->assertEquals($parent->id, $album1->parent->id);
    $this->assertEquals($parent->id, $album2->parent->id);
  }

  public function test_delete_album() {
    Identity::set_active_user(Identity::guest());
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::allow(Identity::everybody(), "edit", Item::root());
    Access::deny(Identity::everybody(), "add", Item::root());
    $album = Test::random_album();

    Rest::factory("Items", $album->id)->delete();
    $this->assertFalse($album->reload()->loaded());
  }

  /**
   * @expectedException HTTP_Exception_403
   */
  public function test_delete_album_fails_without_permission() {
    Identity::set_active_user(Identity::guest());
    Access::allow(Identity::everybody(), "view", Item::root());
    Access::deny(Identity::everybody(), "edit", Item::root());
    Access::allow(Identity::everybody(), "add", Item::root());
    $album = Test::random_album();

    Rest::factory("Items", $album->id)->delete();
  }

  public function test_put_members() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album_unsaved();
    $album->sort_column = "weight";
    $album->save();

    $child1 = Test::random_album($album);
    $child2 = Test::random_photo($album);

    // Before reordering, child1 is before child2 since created first.
    $this->assertEquals($child1->weight + 1, $child2->weight);

    $params = array();
    $params["members"] = array($child1->weight => Rest::factory("Items", $child2->id));

    $rest = Rest::factory("Items", $album->id, $params);
    $rest->put_members();

    // After reordering, child2 is before child1.
    $this->assertEquals($child1->reload()->weight - 1, $child2->reload()->weight);
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_non_child() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album_unsaved();
    $album->sort_column = "weight";
    $album->save();

    $child = Test::random_album($album);
    $grandchild = Test::random_photo($child);

    $params = array();
    $params["members"] = array(12345 => Rest::factory("Items", $grandchild->id));

    Rest::factory("Items", $album->id, $params)->put_members();
  }

  /**
   * @expectedException HTTP_Exception_400
   */
  public function test_put_members_with_non_weighted_album() {
    Identity::set_active_user(Identity::admin_user());

    $album = Test::random_album();  // will be sorted by "created"
    $child = Test::random_album($album);

    $params = array();
    $params["members"] = array($child->weight => Rest::factory("Items", $child->id));

    Rest::factory("Items", $album->id, $params)->put_members();
  }
}
