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

/**
 * This is the API for handling comments.
 *
 * Note: by design, this class does not do any permission checking.
 */
class comment_Core {
  /**
   * Create a new comment.
   * @param Item_MOdel $item         the parent item
   * @param User_Model $author       the author User_Model
   * @param string     $text         comment body
   * @param string     $guest_name   guest's name (if the author is a guest user, default empty)
   * @param string     $guest_email  guest's email (if the author is a guest user, default empty)
   * @param string     $guest_url    guest's url (if the author is a guest user, default empty)
   * @return Comment_Model
   */
  static function create($item, $author, $text, $guest_name=null,
                         $guest_email=ull, $guest_url=null) {
    $comment = ORM::factory("comment");
    $comment->author_id = $author->id;
    $comment->guest_email = $guest_email;
    $comment->guest_name = $guest_name;
    $comment->guest_url = $guest_url;
    $comment->item_id = $item->id;
    $comment->text = $text;
    $comment->state = "published";

    // These values are useful for spam fighting, so save them with the comment.
    $input = Input::instance();
    $comment->server_http_accept = substr($input->server("HTTP_ACCEPT"), 0, 128);
    $comment->server_http_accept_charset = substr($input->server("HTTP_ACCEPT_CHARSET"), 0, 64);
    $comment->server_http_accept_encoding = substr($input->server("HTTP_ACCEPT_ENCODING"), 0, 64);
    $comment->server_http_accept_language = substr($input->server("HTTP_ACCEPT_LANGUAGE"), 0, 64);
    $comment->server_http_connection = substr($input->server("HTTP_CONNECTION"), 0, 64);
    $comment->server_http_host = substr($input->server("HTTP_HOST"), 0, 64);
    $comment->server_http_referer = substr($input->server("HTTP_REFERER"), 0, 255);
    $comment->server_http_user_agent = substr($input->server("HTTP_USER_AGENT"), 0, 128);
    $comment->server_query_string = substr($input->server("QUERY_STRING"), 0, 64);
    $comment->server_remote_addr = substr($input->server("REMOTE_ADDR"), 0, 32);
    $comment->server_remote_host = substr($input->server("REMOTE_HOST"), 0, 64);
    $comment->server_remote_port = substr($input->server("REMOTE_PORT"), 0, 16);

    $comment->save();
    module::event("comment_created", $comment);
    return $comment;
  }

  static function get_add_form($item) {
    $form = new Forge("comments", "", "post");
    $group = $form->group("add_comment")->label(t("Add comment"));
    $group->input("name")   ->label(t("Name"))            ->id("gAuthor");
    $group->input("email")  ->label(t("Email (hidden)"))  ->id("gEmail");
    $group->input("url")    ->label(t("Website (hidden)"))->id("gUrl");
    $group->textarea("text")->label(t("Comment"))         ->id("gText");
    $group->hidden("item_id")->value($item->id);
    $group->submit(t("Add"));

    // Forge will try to reload any pre-seeded values upon validation if it's a post request, so
    // force validation before seeding values.
    // @todo make that an option in Forge
    if (request::method() == "post") {
      $form->validate();
    }

    $active = user::active();
    if (!$active->guest) {
      $group->inputs["name"]->value($active->full_name)->disabled("disabled");
      $group->email->value($active->email)->disabled("disabled");
      $group->url->value($active->url)->disabled("disabled");
    }

    return $form;
  }

  static function get_edit_form($comment) {
    $form = new Forge("comments/{$comment->id}?_method=put", "", "post");
    $group = $form->group("edit_comment")->label(t("Edit comment"));
    $group->input("name")   ->label(t("Author"))          ->id("gAuthor");
    $group->input("email")  ->label(t("Email (hidden)"))  ->id("gEmail");
    $group->input("url")    ->label(t("Website (hidden)"))->id("gUrl");
    $group->textarea("text")->label(t("Comment"))         ->id("gText");
    $group->submit(t("Edit"));

    $group->text = $comment->text;
    $author = $comment->author();
    if ($author->guest) {
      $group->inputs["name"]->value = $comment->guest_name;
      $group->email = $comment->guest_email;
      $group->url = $comment->guest_url;
    } else {
      $group->inputs["name"]->value($author->full_name)->disabled("disabled");
      $group->email->value($author->email)->disabled("disabled");
      $group->url->value($author->url)->disabled("disabled");
    }
    return $form;
  }
}

