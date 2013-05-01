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
class Gallery_Controller_Movies extends Controller_Items {
  public function action_show() {
    $movie = $this->request->param("item");
    if (!is_object($movie)) {
      // action_show() must be a public action because we route to it in the bootstrap,
      // so make sure that we're actually receiving an object
      throw HTTP_Exception::factory(404);
    }
    Access::required("view", $movie);

    $template = new View_Theme("required/page.html", "item", "movie");
    $template->set_global(array("item" => $movie,
                                "children" => array(),
                                "children_count" => 0));
    $template->set_global(Item::get_display_context($movie));
    $template->content = new View("required/movie.html");

    $movie->increment_view_count();

    $this->response->body($template);
  }

  public function action_update() {
    $movie_id = $this->request->arg(0, "digit");
    Access::verify_csrf();
    $movie = ORM::factory("Item", $movie_id);
    Access::required("view", $movie);
    Access::required("edit", $movie);

    $form = $this->get_edit_form($movie);
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
      Module::event("item_edit_form_completed", $movie, $form);

      GalleryLog::success("content", "Updated movie", "<a href=\"{$movie->url()}\">view</a>");
      Message::success(
        t("Saved movie %movie_title", array("movie_title" => HTML::purify($movie->title))));

      if ($form->from_id->value == $movie->id) {
        // Use the new URL; it might have changed.
        $this->response->json(array("result" => "success", "location" => $movie->url()));
      } else {
        // Stay on the same page
        $this->response->json(array("result" => "success"));
      }
    } else {
      $this->response->json(array("result" => "error", "html" => (string) $form));
    }
  }

  public function action_form_edit() {
    $movie_id = $this->request->arg(0, "digit");
    $movie = ORM::factory("Item", $movie_id);
    Access::required("view", $movie);
    Access::required("edit", $movie);

    $this->response->body($this->get_edit_form($movie));
  }

  public function get_edit_form($movie) {
    $form = new Forge("movies/update/$movie->id", "", "post", array("id" => "g-edit-movie-form"));
    $form->hidden("from_id")->value($movie->id);
    $group = $form->group("edit_item")->label(t("Edit Movie"));
    $group->input("title")->label(t("Title"))->value($movie->title)
      ->error_messages("not_empty", t("You must provide a title"))
      ->error_messages("max_length", t("Your title is too long"));
    $group->textarea("description")->label(t("Description"))->value($movie->description);
    $group->input("name")->label(t("Filename"))->value($movie->name)
      ->error_messages("name_conflict", t("There is already a movie, photo or album with this name"))
      ->error_messages("no_slashes", t("The movie name can't contain a \"/\""))
      ->error_messages("no_backslashes", t("The movie name can't contain a \"\\\""))
      ->error_messages("no_trailing_period", t("The movie name can't end in \".\""))
      ->error_messages("data_file_extension", t("You cannot change the movie file extension"))
      ->error_messages("not_empty", t("You must provide a movie file name"))
      ->error_messages("max_length", t("Your movie file name is too long"));
    $group->input("slug")->label(t("Internet Address"))->value($movie->slug)
      ->error_messages(
        "conflict", t("There is already a movie, photo or album with this internet address"))
      ->error_messages(
        "not_url_safe",
        t("The internet address should contain only letters, numbers, hyphens and underscores"))
      ->error_messages("not_empty", t("You must provide an internet address"))
      ->error_messages("max_length", t("Your internet address is too long"));

    Module::event("item_edit_form", $movie, $form);

    $group = $form->group("buttons")->label("");
    $group->submit("")->value(t("Modify"));
    return $form;
  }
}
