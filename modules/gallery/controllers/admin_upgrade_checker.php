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
class Admin_Upgrade_Checker_Controller extends Admin_Controller {
  function check_now() {
    access::verify_csrf();
    upgrade_checker::fetch_version_info();
    $message = upgrade_checker::get_upgrade_message();
    if ($message) {
      $message .= t(
        " <a href=\"%hide-url\"><i>(remind me later)</i></a>",
        array("hide-url" => url::site("admin/upgrade_checker/remind_me_later?csrf=__CSRF__")));
      site_status::info($message, "upgrade_checker");
    } else {
      site_status::clear("upgrade_checker");
    }
    url::redirect("admin/dashboard");
  }

  function remind_me_later() {
    access::verify_csrf();
    site_status::clear("upgrade_checker");
    if ($referer = Input::instance()->server("HTTP_REFERER")) {
      url::redirect($referer);
    } else {
      url::redirect(item::root()->abs_url());
    }
  }

  function set_auto($val) {
    access::verify_csrf();
    module::set_var("gallery", "upgrade_checker_auto_enabled", (bool)$val);

    if ((bool)$val) {
      message::success(t("Automatic upgrade checking is enabled."));
    } else {
      message::success(t("Automatic upgrade checking is disabled."));
    }
    url::redirect("admin/dashboard");
  }
}
