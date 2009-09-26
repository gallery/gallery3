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
class Movies_Controller extends Items_Controller {

  /**
   *  @see REST_Controller::_show($resource)
   */
  public function _show($movie) {
    access::required("view", $movie);

    $position = $movie->parent()->get_position($movie);
    if ($position > 1) {
      list ($previous_item, $ignore, $next_item) =
        $movie->parent()->children(3, $position - 2);
    } else {
      $previous_item = null;
      list ($next_item) = $movie->parent()->viewable()->children(1, $position);
    }

    $template = new Theme_View("page.html", "movie");
    $template->set_global("item", $movie);
    $template->set_global("children", array());
    $template->set_global("children_count", 0);
    $template->set_global("parents", $movie->parents());
    $template->set_global("next_item", $next_item);
    $template->set_global("previous_item", $previous_item);
    $template->set_global("sibling_count", $movie->parent()->viewable()->children_count());
    $template->set_global("position", $position);

    $template->content = new View("movie.html");

    $movie->view_count++;
    $movie->save();

    print $template;
  }

  /**
   * @see REST_Controller::_update($resource)
   */
  public function _update($movie) {
    access::verify_csrf();
    access::required("view", $movie);
    access::required("edit", $movie);

    $form = movie::get_edit_form($movie);
    if ($valid = $form->validate()) {
      if ($form->edit_item->filename->value != $movie->name ||
          $form->edit_item->slug->value != $movie->slug) {
        // Make sure that there's not a name or slug conflict
        if ($row = Database::instance()
            ->select(array("name", "slug"))
            ->from("items")
            ->where("parent_id", $movie->parent_id)
            ->where("id <>", $movie->id)
            ->open_paren()
            ->where("name", $form->edit_item->filename->value)
            ->orwhere("slug", $form->edit_item->slug->value)
            ->close_paren()
            ->get()
            ->current()) {
          if ($row->name == $form->edit_item->filename->value) {
            $form->edit_item->filename->add_error("name_conflict", 1);
          }
          if ($row->slug == $form->edit_item->slug->value) {
            $form->edit_item->slug->add_error("slug_conflict", 1);
          }
          $valid = false;
        }
      }
    }

    if ($valid) {
      $movie->title = $form->edit_item->title->value;
      $movie->description = $form->edit_item->description->value;
      $movie->slug = $form->edit_item->slug->value;
      $movie->rename($form->edit_item->filename->value);
      $movie->save();
      module::event("item_edit_form_completed", $movie, $form);

      log::success("content", "Updated movie", "<a href=\"{$movie->url()}\">view</a>");
      message::success(
        t("Saved movie %movie_title", array("movie_title" => $movie->title)));

      print json_encode(
        array("result" => "success"));
    } else {
      print json_encode(
        array("result" => "error",
              "form" => $form->__toString()));
    }
  }

  /**
   *  @see REST_Controller::_form_edit($resource)
   */
  public function _form_edit($movie) {
    access::required("view", $movie);
    access::required("edit", $movie);
    print movie::get_edit_form($movie);
  }
}
