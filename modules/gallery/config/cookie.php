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

/**
 * Domain, to restrict the cookie to a specific website domain. For security,
 * you are encouraged to set this option. An empty setting allows the cookie
 * to be read by any website domain.
 */
$config['domain'] = '';

/**
 * Restrict cookies to a specific path, typically the installation directory.
 */
$config['path'] = '/';

/**
 * Lifetime of the cookie. A setting of 0 makes the cookie active until the
 * users browser is closed or the cookie is deleted.
 */
$config['expire'] = 0;

/**
 * Enable this option to only allow the cookie to be read when using the a
 * secure protocol.
 */
$config['secure'] = false;

/**
 * Enable this option to disable the cookie from being accessed when using a
 * secure protocol. This option is only available in PHP 5.2 and above.
 */
$config['httponly'] = true;