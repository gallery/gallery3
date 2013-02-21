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
class Upgrader_Controller extends Controller {
  public function index() {
    $session = Session::instance();

    // Make sure we have an upgrade token
    if (!($upgrade_token = $session->get("upgrade_token", null))) {
      $session->set("upgrade_token", $upgrade_token = random::hash());
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

    $failed = Input::instance()->get("failed");
    $view = new View("upgrader.html");
    $view->can_upgrade = identity::active_user()->admin || $session->get("can_upgrade");
    $view->upgrade_token = $upgrade_token;
    $view->available = module::available();
    $view->failed = $failed ? explode(",", $failed) : array();
    $view->done = $available_upgrades == 0;
    $view->obsolete_modules_message = module::get_obsolete_modules_message();
    print $view;
  }

  public function upgrade() {
    if (php_sapi_name() == "cli") {
      // @todo this may screw up some module installers, but we don't have a better answer at
      // this time.
      $_SERVER["HTTP_HOST"] = "example.com";
    } else {
      if (!identity::active_user()->admin && !Session::instance()->get("can_upgrade", false)) {
        access::forbidden();
      }

      try {
        access::verify_csrf();
      } catch (Exception $e) {
        url::redirect("upgrader");
      }
    }

    $available = module::available();
    // Upgrade gallery first
    $gallery = $available["gallery"];
    if ($gallery->code_version != $gallery->version) {
      module::upgrade("gallery");
      module::activate("gallery");
    }

    // Then upgrade the rest
    $failed = array();
    foreach (module::available() as $id => $module) {
      if ($id == "gallery") {
        continue;
      }

      if ($module->active && $module->code_version != $module->version) {
        try {
          module::upgrade($id);
        } catch (Exception $e) {
          // @todo assume it's MODULE_FAILED_TO_UPGRADE for now
          $failed[] = $id;
        }
      }
    }

    // If the upgrade failed, this will get recreated
    site_status::clear("upgrade_now");

    // Clear any upgrade check strings, we are probably up to date.
    site_status::clear("upgrade_checker");

    if (php_sapi_name() == "cli") {
      if ($failed) {
        print "Upgrade completed ** WITH FAILURES **\n";
        print "The following modules were not successfully upgraded:\n";
        print "  " . implode($failed, "\n  ") . "\n";
        print "Try getting newer versions or deactivating those modules\n";
      } else {
        print "Upgrade complete\n";
      }
    } else {
      if ($failed) {
        url::redirect("upgrader?failed=" . join(",", $failed));
      } else {
        url::redirect("upgrader");
      }
    }
  }
}
