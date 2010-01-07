<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Image library config
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * Image driver
 *
 * @default: 'GD'
 */
$config['driver'] = 'GD';

/**
 * Driver parameters:
 * ImageMagick - set the "directory" parameter to your ImageMagick installation directory
 */
$config['params'] = array();