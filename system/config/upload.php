<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Upload config
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

/**
 * This path is relative to your index file. Absolute paths are also supported.
 */
$config['directory'] = DOCROOT.'upload';

/**
 * Enable or disable directory creation.
 */
$config['create_directories'] = FALSE;

/**
 * Remove spaces from uploaded filenames.
 */
$config['remove_spaces'] = TRUE;