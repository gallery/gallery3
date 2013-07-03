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
class Search_Controller_Admin_Search extends Controller_Admin {
  public function action_index() {
    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-search-admin-form")
      ->add("settings", "group")
      ->add("submit", "input|submit", t("Save"));
    $form->settings
      ->set("label", t("Settings"))
      ->add("wildcard_mode",      "select", Module::get_var("search", "wildcard_mode", "append_stem"))
      ->add("short_search_fix", "checkbox", Module::get_var("search", "short_search_fix", false))
      ->add("short_search_prefix", "input", Module::get_var("search", "short_search_prefix", "1Z"));
    $form->settings->wildcard_mode
      ->set("label", t("Wildcard mode"))
      ->set("opts", array(
          "append_stem" => t("append wildcards to word stem (default)"),
          "append"      => t("append wildcards"),
          "none"        => t("do not append wildcards")
        ));
    $form->settings->short_search_fix
      ->set("label", t("Enable 'short search fix' mode"));
    $form->settings->short_search_prefix
      ->set("label", t("Prefix used for 'short search fix' mode (default: 1Z)"))
      ->add_rule("not_empty", array(":value"), t("You must enter a prefix"));

    // Validate the form and update the settings as needed.
    if ($form->load()->validate()) {
      // If the short search fix settings have changed, we need to mark the search records as dirty.
      $old_mode   = Module::get_var("search", "short_search_fix", false);
      $old_prefix = Module::get_var("search", "short_search_prefix", "1Z");
      if (($old_mode != $form->settings->short_search_fix->val()) ||
          (($old_prefix != $form->settings->short_search_prefix->val()) && $old_mode)) {
        Search::mark_dirty();
      }

      Module::set_var("search", "wildcard_mode",       $form->settings->wildcard_mode->val());
      Module::set_var("search", "short_search_fix",    $form->settings->short_search_fix->val());
      Module::set_var("search", "short_search_prefix", $form->settings->short_search_prefix->val());

      Message::success(t("Search settings updated successfully"));
    }

    // Build and return the view.
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("Search settings");
    $view->content = new View("admin/search.html");
    $view->content->form = $form;

    $db = Database::instance();
    $result = $db
      ->query(Database::SELECT, "SHOW VARIABLES LIKE" . $db->quote("ft_min_word_len"))
      ->current();

    $view->content->ft_min_word_len = Arr::get($result, "Value");

    $this->response->body($view);
  }
}
