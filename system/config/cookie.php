<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cookie config settings. These are the default settings used by the [cookie]
 * helper. You can override these settings by passing parameters to the cookie
 * helper functions.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Domain, to restrict the cookie to a specific website domain. For security,
 * you are encouraged to set this option. An empty setting allows the cookie
 * to be read by any website domain.
 * @default ''
 */
$config['domain'] = '';

/**
 * Restrict cookies to a specific path, typically the installation directory.
 * @default '/'
 */
$config['path'] = '/';

/**
 * Lifetime of the cookie. A setting of 0 makes the cookie active until the
 * users browser is closed or the cookie is deleted.
 * @default = 0
 */
$config['expire'] = 0;

/**
 * Enable this option to only allow the cookie to be read when using the a
 * secure protocol.
 * @default FALSE
 */
$config['secure'] = FALSE;

/**
 * Enable this option to make the cookie accessible only through the
 * HTTP protocol (e.g. no javascript access). This is not supported by all browsers.
 * @default FALSE
 */
$config['httponly'] = FALSE;

/**
 * Cookie salt for signed cookies.
 * Make sure you change this to a unique value.
 *
 * [!!] Changing this value will invalidate all existing cookies!
 * @default 'K0hAN4 15 Th3 B357'
 */
$config['salt'] = 'K0hAN4 15 Th3 B357';