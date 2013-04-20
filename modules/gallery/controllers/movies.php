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
class Movies_Controller extends Items_Controller {
  public function show($movie) {
    if (!is_object($movie)) {
      // show() must be public because we route to it in url::parse_url(), so make
      // sure that we're actually receiving an object
      throw new Kohana_404_Exception();
    }

    access::required("view", $movie);

    $template = new Theme_View("page.html", "item", "movie");
    $template->set_global(array("item" => $movie,
                                "children" => array(),
                                "children_count" => 0));
    $template->set_global(item::get_display_context($movie));
    $template->content = new View("movie.html");

    $movie->increment_view_count();

    print $template;
  }

  public function update($movie_id) {
    access::verify_csrf();
    $movie = ORM::factory("item", $movie_id);
    access::required("view", $movie);
    access::required("edit", $movie);

    $form = movie::get_edit_form($movie);
    try {
      $valid = $form->validate();
      $movie->title = $form->edit_item->title->value;
      $movie->description = $form->edit_item->description->value;
      $movie->slug = $form->edit_item->slug->value;
      $movie->name = $form->edit_item->inputs["name"]->value;
      $movie->validate();
    } catch (ORM_Validation_Exception $e) {
      // Translate ORM validation errors into form error messages
      foreach ($e->validation->errors() as $key => $error) {
        $form->edit_item->inputs[$key]->add_error($error, 1);
      }
      $valid = false;
    }

    if ($valid) {
      $movie->save();
      module::event("item_edit_form_completed", $movie, $form);

      log::success("content", "Updated movie", "<a href=\"{$movie->url()}\">view</a>");
      message::success(
        t("Saved movie %movie_title", array("movie_title" => html::purify($movie->title))));

      if ($form->from_id->value == $movie->id) {
        // Use the new url; it might have changed.
        json::reply(array("result" => "success", "location" => $movie->url()));
      } else {
        // Stay on the same page
        json::reply(array("result" => "success"));
      }
    } else {
      json::reply(array("result" => "error", "html" => (string) $form));
    }
  }

  public function form_edit($movie_id) {
    $movie = ORM::factory("item", $movie_id);
    access::required("view", $movie);
    access::required("edit", $movie);

    print movie::get_edit_form($movie);
  }
}
