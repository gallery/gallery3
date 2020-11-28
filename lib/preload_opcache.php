<?php

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
	return $files;
}

$preload_dirs = [
	'application',
	'system',
	'modules',
	'themes',
];

$br = (php_sapi_name() == "cli") ? "\n" : "<br />";
$br = "\n";

foreach ($preload_dirs as $dir) {
	$files = [];
	$full_dir = "/var/www/$dir";
	getfiles($full_dir, $files);

	echo "opcache preload ".count($files)."files from $full_dir\n";

	foreach ($files as $file) {
		echo $file.$br;
		opcache_compile_file($file);
	}
}
