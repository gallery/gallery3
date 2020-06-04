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
class Passwordreset_Controller extends Controller {
  function index($username='') {
    if (PHP_SAPI != 'cli') {
      access::forbidden();
    }

    if (empty($username)) {
        print "No username entered\n";
	exit;
    }

    $user = identity::lookup_user_by_name($username);

    if (empty($user)) {
        print "Unable to find user ($username)\n";
	exit;
    }

    $password = substr(md5(time() . mt_rand()), 0, 10);

    $user->password = $password;
    $user->save();

    print "We reset the password for:
  username: {$user->name}
  password: $password

";
  }
}
