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

/**
 * Type of hash to use for passwords. Any algorithm supported by the hash function
 * can be used here. Note that the length of your password is determined by the
 * hash type + the number of salt characters.
 * @see http://php.net/hash
 * @see http://php.net/hash_algos
 */
$config['hash_method'] = 'sha1';

/**
 * Defines the hash offsets to insert the salt at. The password hash length
 * will be increased by the total number of offsets.
 */
$config['salt_pattern'] = '1, 3, 5, 9, 14, 15, 20, 21, 28, 30';

