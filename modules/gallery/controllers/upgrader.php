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
    $session = Session::instance();

    // Make sure we have an upgrade token
    if (!($upgrade_token = $session->get("upgrade_token", null))) {
      $session->set("upgrade_token", $upgrade_token = md5(rand()));
    }

    // If the upgrade token exists, then bless this session
    if (file_exists(TMPPATH . $upgrade_token)) {
      $session->set("can_upgrade", true);
      @unlink(TMPPATH . $upgrade_token);
    }

    $available_upgrades = 0;
    foreach (module::available() as $module) {
      if ($module->version && $module->version != $module->code_version) {
        $available_upgrades++;
      }
    }

    $view = new View("upgrader.html");
    $view->can_upgrade = user::active()->admin || $session->get("can_upgrade");
    $view->upgrade_token = $upgrade_token;
    $view->available = module::available();
    $view->done = ($available_upgrades == 0);
    print $view;
  }

  public function upgrade() {
    if (php_sapi_name() == "cli") {
      // @todo this may screw up some module installers, but we don't have a better answer at
      // this time.
      $_SERVER["HTTP_HOST"] = "example.com";
    } else if (!user::active()->admin && !Session::instance()->get("can_upgrade", false)) {
      access::forbidden();
    }

    // Upgrade gallery and user first
    module::upgrade("gallery");
    module::upgrade("user");

    // Then upgrade the rest
    foreach (module::available() as $id => $module) {
      if ($id == "gallery") {
        continue;
      }

      if ($module->active && $module->code_version != $module->version) {
        module::upgrade($id);
      }
    }

    if (php_sapi_name() == "cli") {
      print "Upgrade complete\n";
    } else {
      url::redirect("upgrader");
    }
  }
}
