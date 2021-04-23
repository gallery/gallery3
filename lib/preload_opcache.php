<?php
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

function getfiles( $path , &$files = array() ) {
	if ( !is_dir( $path ) ) return null;

	$handle = opendir( $path );

	while ( false !== ( $file = readdir( $handle ) ) ) {
		if ( $file != '.' && $file != '..' ) {
			$path2 = $path . '/' . $file;
			if ( is_dir( $path2 ) ) {
				getfiles( $path2 , $files );
			} else {
				if ( preg_match( "/\.(php|php5)$/i" , $file ) ) {
					$files[] = $path2;
				}
			}
		}
	}

	closedir($handle);

	return $files;
}

$preload_dirs = [
	'vendor/lsolesen/pel/src',
	'vendor/phpmailer/phpmailer/src',
	'application',
	'system',
	'modules/gallery',
	'modules',
	'themes',
];

$br = (php_sapi_name() == "cli") ? "\n" : "<br />";
$br = "\n";

foreach ($preload_dirs as $dir) {
	$files = [];
	$full_dir = "/var/www/$dir";

	getfiles($full_dir, $files);

	sort($files);

	echo "opcache preload ".count($files)." files from $full_dir\n";

	foreach ($files as $file) {
		if ($dir == 'modules') {
			# don't load the second time through the list
			if (preg_match('#^/var/www/modules/gallery/#', $file)) continue;
		}

		if (preg_match('#^/var/www/modules/gallery/tests#', $file)) continue;
		if (preg_match('#^/var/www/modules/gallery_unit_test#', $file)) continue;

		echo $file.$br;
		opcache_compile_file($file);
	}

	echo "\n";
}
