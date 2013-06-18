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
class RestAPI_Controller_Admin_Rest extends Controller_Admin {
  public function action_index() {
    // Build the form.
    $form = Formo::form()
      ->attr("id", "g-rest-admin-form")
      ->add("settings", "group")
      ->add("submit", "input|submit", t("Save"));
    $form->settings
      ->set("label", t("Settings"))
      ->add("allow_guest_access", "checkbox", Module::get_var("rest", "allow_guest_access", false))
      ->add("allow_jsonp_output", "checkbox", Module::get_var("rest", "allow_jsonp_output", false))
      ->add("cors_embedding",     "select",   Module::get_var("rest", "cors_embedding", "none"))
      ->add("approved_domains",   "input",    Module::get_var("rest", "approved_domains", ""));
    $form->settings->allow_guest_access
      ->set("label", t("Allow guest access"));
    $form->settings->allow_jsonp_output
      ->set("label", t("Allow embedding using JSONP (requires guest access)"));
    $form->settings->cors_embedding
      ->set("label", t("Allow embedding using CORS on other domains"))
      ->set("opts", array(
          "all"  => t("yes, from any domain"),
          "list" => t("yes, from list of approved domains"),
          "none" => t("no, from same domain only")
        ));
    $form->settings->approved_domains
      ->set("label", t("List of approved domains for CORS embedding (comma-separated list, where \"example.com\" matches subdomains like \"my.example.com\")"));

    // Validate the form and update the settings as needed.
    if ($form->load()->validate()) {
      $approved_domains = strtolower(trim($form->settings->approved_domains->val(), ","));
      $approved_domains = preg_replace("/,+/", ",", $approved_domains);
      $approved_domains = preg_replace('/\s/', "", $approved_domains);
      $form->settings->approved_domains->val($approved_domains);  // Displays filtered val in form.

      Module::set_var("rest", "allow_guest_access", $form->settings->allow_guest_access->val());
      Module::set_var("rest", "allow_jsonp_output", $form->settings->allow_jsonp_output->val());
      Module::set_var("rest", "cors_embedding",     $form->settings->cors_embedding->val());
      Module::set_var("rest", "approved_domains",   $form->settings->approved_domains->val());

      Message::success(t("REST settings updated successfully"));
    }

    // Build and return the view.
    $view = new View_Admin("required/admin.html");
    $view->page_title = t("REST API settings");
    $view->content = new View("admin/rest.html");
    $view->content->form = $form;

    $this->response->body($view);
  }
}
