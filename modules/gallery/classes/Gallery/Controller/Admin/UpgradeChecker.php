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
class Gallery_Controller_Admin_UpgradeChecker extends Controller_Admin {
  function check_now() {
    Access::verify_csrf();
    UpgradeChecker::fetch_version_info();
    $message = UpgradeChecker::get_upgrade_message();
    if ($message) {
      $message .= t(
        " <a href=\"%hide-url\"><i>(remind me later)</i></a>",
        array("hide-url" => URL::site("admin/upgrade_checker/remind_me_later?csrf=__CSRF__")));
      SiteStatus::info($message, "upgrade_checker");
    } else {
      SiteStatus::clear("upgrade_checker");
    }
    URL::redirect("admin/dashboard");
  }

  function remind_me_later() {
    Access::verify_csrf();
    SiteStatus::clear("upgrade_checker");
    if ($referer = Input::instance()->server("HTTP_REFERER")) {
      URL::redirect($referer);
    } else {
      URL::redirect(Item::root()->abs_url());
    }
  }

  function set_auto($val) {
    Access::verify_csrf();
    Module::set_var("gallery", "upgrade_checker_auto_enabled", (bool)$val);

    if ((bool)$val) {
      Message::success(t("Automatic upgrade checking is enabled."));
    } else {
      Message::success(t("Automatic upgrade checking is disabled."));
    }
    URL::redirect("admin/dashboard");
  }
}
