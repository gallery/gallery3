<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
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
$config['secure'] = FALSE;

/**
 * Enable this option to make the cookie accessible only through the
 * HTTP protocol (e.g. no javascript access). This is not supported by all browsers.
 */
$config['httponly'] = FALSE;

/**
 * Cookie salt for signed cookies.
 * Make sure you change this to a unique value.
 */
$config['salt'] = 'K0hAN4 15 Th3 B357';