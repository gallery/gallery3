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
class Gallery_Controller_Admin_Movies extends Controller_Admin {
  public function action_index() {
    // Build the form.
    $form = Formo::form()
      ->add("settings", "group")
      ->add("submit", "input|submit", t("Save"));
    $form->settings
      ->add("allow_uploads", "select", Module::get_var("gallery", "movie_allow_uploads", "autodetect"))
      ->add("rebuild_thumbs", "checkbox", false);  // always reset to false

    $form
      ->attr("id", "g-movies-admin-form");
    $form->settings
      ->set("label", t("Settings"));
    $form->settings->allow_uploads
      ->set("label", t("Allow movie uploads into Gallery (does not affect existing movies)"))
      ->set("opts", array(
          "autodetect" => t("only if FFmpeg is detected (default)"),
          "always"     => t("always"),
          "never"      => t("never")
        ));
    $form->settings->rebuild_thumbs
      ->set("label", t("Rebuild all movie thumbnails (once FFmpeg is installed, use this to update existing movie thumbnails)"));

    // Validate the form and update the settings as needed.
    if ($form->load()->validate()) {
      Module::set_var("gallery", "movie_allow_uploads", $form->settings->allow_uploads->val());
      if ($form->settings->rebuild_thumbs->val()) {
        Graphics::mark_dirty(true, false, "movie");
        $form->settings->rebuild_thumbs->val(false);  // always reset to false
      }
      Message::success(t("Movies settings updated successfully"));
    }

    // Build and return the view.
    list ($ffmpeg_version, $ffmpeg_date) = Movie::get_ffmpeg_version();
    $ffmpeg_version = $ffmpeg_date ? "{$ffmpeg_version} ({$ffmpeg_date})" : $ffmpeg_version;
    $ffmpeg_path = Movie::find_ffmpeg();
    $ffmpeg_dir = substr($ffmpeg_path, 0, strrpos($ffmpeg_path, "/"));

    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Movies settings");
    $view->content = new View("admin/movies.html");
    $view->content->form = $form;
    $view->content->ffmpeg_dir = $ffmpeg_dir;
    $view->content->ffmpeg_version = $ffmpeg_version;

    $this->response->body($view);
  }
}
