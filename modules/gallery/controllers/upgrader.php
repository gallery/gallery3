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
class Upgrader_Controller extends Controller {
  public function index() {
    // Todo: give the admin a chance to log in here
    if (!user::active()->admin) {
      access::forbidden();
    }

    $view = new View("upgrader.html");
    $view->available = module::available();
    $view->done = Input::instance()->get("done");
    print $view;
  }

  public function upgrade() {
    // Todo: give the admin a chance to log in here
    if (php_sapi_name() == "cli") {
      // @todo this may screw up some module installers, but we don't have a better answer at
      // this time.
      $_SERVER["HTTP_HOST"] = "example.com";
    } else if (!user::active()->admin) {
      access::forbidden();
    }

    // Upgrade gallery and user first
    module::install("gallery");
    module::install("user");

    // Then upgrade the rest
    foreach (module::available() as $id => $module) {
      if ($id == "gallery") {
        continue;
      }

      if ($module->active && $module->code_version != $module->version) {
        module::install($id);
      }
    }

    if (php_sapi_name() == "cli") {
      print "Upgrade complete\n";
    } else {
      url::redirect("upgrader?done=1");
    }
  }
}
