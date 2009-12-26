<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Default validation messages
 *
 * @package    Validation
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

$messages = array(
	'required'      => 'The :field field is required',
	'length'        => 'The :field field must be between :param1 and :param2 characters long',
	'depends_on'    => 'The :field field requires the :param1 field',
	'matches'       => 'The :field field must be the same as :param1',
	'email'         => 'The :field field must be a valid email address',
	'decimal'       => 'The :field field must be a decimal with :param1 places',
	'digit'         => 'The :field field must be a digit',
	'in_array'      => 'The :field field must be one of the available options',
	'alpha_numeric' => 'The :field field must consist only of alphabetical or numeric characters',
	'alpha_dash '   => 'The :field field must consist only of alphabetical, numeric, underscore and dash characters',
	'numeric '      => 'The :field field must be a valid number',
	'url'           => 'The :field field must be a valid url',
	'phone'         => 'The :field field must be a valid phone number',
);
