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
class Admin_Spam_Filter_Controller extends Admin_Controller {
  public function index() {
    $view = new Admin_View("admin.html");
    $view->content = $this->get_edit_form();

    print $view;
  }

  public function get_edit_form($driver_name=null, $post=null) {
    $form = new View("spam_filter_admin.html");

    $drivers = spam_filter::get_driver_names();
    $current_driver = empty($driver_name) ? module::get_var("spam_filter", "driver") : $driver_name;
    $current_driver = !empty($current_driver) ? $current_driver : $drivers[0];

    $selected = 0;
    $driver_options = array();
    foreach ($drivers as $idx => $driver) {
      if ($driver == $current_driver) {
        $selected = $idx;
      }
      $driver_options[] = array("name" => $driver, "selected" => $driver == $current_driver);
    }
    $form->drivers = $driver_options;

    $form->filter_data = empty($selected) ? "" :
      SpamFilter::instance($current_driver)->get_admin_fields($post);

    return $form;
  }

  public function edit() {
    $selected = Input::instance()->post("drivers");
    $drivers = spam_filter::get_driver_names();
    $driver_name = $drivers[$selected];

    if (!empty($selected)) {
      $post = new Validation($_POST);
      SpamFilter::instance($driver_name)->get_validation_rules($post);
      if ($post->validate()) {
        module::set_var("spam_filter", "driver", $drivers[$selected]);
        SpamFilter::instance($driver_name)->set_api_data($post);

        log::success("spam_filter", _("Spam Filter configured"));
          message::success(_("Spam Filter configured"));
          print json_encode(
            array("result" => "success",
                  "location" => url::site("admin/spam_filter")));
      } else {
        $form = $this->get_edit_form($driver_name, $post);
        print json_encode(
          array("result" => "error",
                "form" => $form->__toString()));

      }
    } else {
      $form = $this->get_edit_form();
      print json_encode(
        array("result" => "continue",
              "form" => $form->__toString()));
    }
//    $selected = Input::instance()->post("selected");
//    $new_driver_idx = Input::instance()->post("drivers");
//
//    if ($selected != $new_driver_idx) {
//      $drivers = spam_filter::get_driver_names();
//      $form = $this->get_edit_form($drivers[$new_driver_idx]);
//      $form->edit_spam_filter->selected = $new_driver_idx;
//      unset($_POST["drivers"])
//      print json_encode(
//        array("result" => "continue",
//              "form" => $form->__toString()));
//    } else {
//      Kohana::log("debug", "validate form");
//      $form = $this->get_edit_form();
//      if ($form->validate()) {
//        $driver_index = $form->edit_spam_filter->drivers->value;
//        $drivers = spam_filter::get_driver_names();
//        module::set_var("spam_filter", "driver", $drivers[$driver_index]);
//
//        if (SpamFilter::instance()->set_admin_fields($form->edit_spam_filter->api_data)) {
//          $key_verified = module::set_var("spam_filter", "key_verified", true);
//          log::success("spam_filter", _("Spam Filter configured"));
//          message::success(_("Spam Filter configured"));
//          print json_encode(
//            array("result" => "success",
//                  "location" => url::site("admin/spam_filter")));
//        } else {
//          print json_encode(
//          array("result" => "error",
//                "form" => $form->__toString()));
//        }
//      } else {
//        print json_encode(
//          array("result" => "error",
//                "form" => $form->__toString()));
//      }
//    }
  }

  public function callback() {
    $driver_name = Input::instance()->post("driver");

    $selected = $this->_get_selected_index($driver_name);
    if (!empty($selected)) {
      print SpamFilter::instance($driver_name)->get_admin_fields();
    } else {
      print "";
    }
  }

  public function _get_selected_index($driver_name) {
    $drivers = spam_filter::get_driver_names();

    $selected = 0;
    foreach ($drivers as $idx => $driver) {
      if ($driver == $driver_name) {
        $selected = $idx;
      }
    }

    return $selected;
  }
}