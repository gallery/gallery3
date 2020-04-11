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

/**
 * to use this module:
 * 1) configure your below mail settings
 * 2) cd to your main gallery3 folder
 * 3) be sure 'composer' for PHP is installed
 * 4) run 'composer install'
 * 5) enable the phpmailer module in the gallery -> admin -> module area
 */

$config['options'] = array(
  #'use_smtp' => true,
  #'use_smtp_auth' => true,
  #'hostname' => 'yourhostname',
  #'username' => 'yourusername',
  #'password' => 'yourpassword',
  #'port' => '25',
  #'secure' => 'tls', // or 'smtps' to enable
);
