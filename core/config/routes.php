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

// FIXME Temporary workaround to show the welcome page at /welcome.
// The problem is that we're routing all requests to /{controllername} to Rest_Controller,
// even requests to controllers that do not implement Rest_Controller.
$config['^welcome$'] = 'welcome';

// REST configuration
// Any resource requests (eg: album/1 or comment/3) get dispatched to the REST
// dispatcher, and the abstract REST_Controller is not directly routable.
$config['^rest'] = null;
$config['^rest/.*'] = null;
$config['^(\w+)/(\d+)$'] = '$1/dispatch/$2';
$config['^(\w+)$'] = '$1/dispatch/0';
$config['^form/(edit|add)/(\w+)/(.*)$'] = '$2/form_$1/$3';

// For now our default page is the scaffolding.
$config['_default'] = 'welcome';
