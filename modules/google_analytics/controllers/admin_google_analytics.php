<?php defined("SYSPATH") or die("No direct script access.");/**
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
class Admin_Google_Analytics_Controller extends Admin_Controller
{
  public function index()
  {
    print $this->_get_view();
  }

  public function handler()
  {
    access::verify_csrf();

    $form = $this->_get_form();
    
    if ($form->validate())
    {
      module::set_var("google_analytics", "code", $form->google_analytics_code->inputs["analytics_code"]->value);	  
      module::set_var("google_analytics", "owneradmin_hidden", $form->google_analytics_code->inputs["analytics_owneradmin_hidden"]->value);	  
      url::redirect("admin/google_analytics");
    }

    print $this->_get_view($form);
  }

  private function _get_view($form=null)
  {
    $v = new Admin_View("admin.html");
    $v->content = new View("admin_google_analytics.html");
    $v->content->form = empty($form) ? $this->_get_form() : $form;
    return $v;
  }
  
  private function _get_form()
  {
    $form = new Forge("admin/google_analytics/handler", "", "post",
                      array("id" => "gAdminForm"));
    $group = $form->group("google_analytics_code");
    $group->input("analytics_code")->label(t('Enter the <a href="http://www.google.com/support/googleanalytics/bin/answer.py?answer=113500" target="_blank">Web-Property-ID</a> given by Google.'))->rules("required")->value(module::get_var("google_analytics", "code"));
    $group->checkbox("analytics_owneradmin_hidden")->label(t("Omit code for owner and admin"))
    	->checked(module::get_var("google_analytics", "owneradmin_hidden", false) == 1);
    $group->submit("submit")->value(t("Save"));

    return $form;
  }
}