<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Encrypt configuration is defined in groups which allows you to easily switch
 * between different encryption settings for different uses.
 *
 * [!!] All groups inherit and overwrite the default group.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Group Options:
 *
 *  For best security, your encryption key should be at least 16 characters
 *  long and contain letters, numbers, and symbols.
 *
 * - key    - Encryption key used to do encryption and decryption. The default option
 *           should never be used for a production website.
 *
 *  [!!] Do not use a hash as your key. This significantly lowers encryption entropy.
 *
 * - mode   - MCrypt encryption mode. By default, MCRYPT_MODE_NOFB is used. This mode
 *           offers initialization vector support, is suited to short strings, and
 *           produces the shortest encrypted output.
 *
 * - cipher - MCrypt encryption cipher. By default, the MCRYPT_RIJNDAEL_128 cipher is used.
 *           This is also known as 128-bit AES.
 *
 * 	For more information about mcrypt modes and cipers see the [mcrypt php docs](http://php.net/mcrypt).
 */
$config['default'] = array
(
	'key'    => 'K0H@NA+PHP_7hE-SW!FtFraM3w0R|<',
	'mode'   => MCRYPT_MODE_NOFB,
	'cipher' => MCRYPT_RIJNDAEL_128
);
