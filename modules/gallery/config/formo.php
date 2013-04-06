<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	// File used
	// FALSE or name of message file (ex: 'label_messages')
	'label_message_file'      => 'mymessages',
	// File used for validation messages
	// FALSE or name of the message file (ex: 'validation_messages')
	'validation_message_file' => 'validation',
	// Whether to translate labels and error messages
	'translate'               => TRUE,
	// Close single html tags (TRUE = <br/>. FALSE = <br>)
	'close_single_html_tags'  => TRUE,
	// Auto-generate IDs on form elements
	'auto_id'                 => TRUE,
	// The directory for the formo templates (ex: 'formo' or 'formo_bootstrap')
	'template_dir'            => 'formo_bootstrap/',
	// Namespace fields (name="parent_alias[field_alias]")
	'namespaces'              => TRUE,
	// Driver used for ORM integration
	'orm_driver'              => 'kohana',
	// Automatically add these rules to 'input' fields for html5 compatability
	'input_rules' => array
	(
		'email'          => array(array('email')),
		'tel'            => array(array('phone')),
		'url'            => array(array('url')),
		'date'           => array(array('date')),
		'datetime'       => array(array('date')),
		'datetime-local' => array(array('date')),
		'color'          => array(array('color')),
		'week'           => array(array('regex', array(':value', '/^\d{4}-[Ww](?:0[1-9]|[1-4][0-9]|5[0-2])$/'))),
		'time'           => array(array('regex', array(':value', '/^(?:([0-1]?[0-9])|([2][0-3])):(?:[0-5]?[0-9])(?::([0-5]?[0-9]))?$/'))),
		'month'          => array(array('regex', array(':value', '/^\d{4}-(?:0[1-9]|1[0-2])$/'))),
		'range'          => array(
			array('digit'),
			array('Formo_Validator::range', array(':field', ':form')),
		),
		'number'        => array(
			array('digit'),
			array('Formo_Validator::range', array(':field', ':form')),
		),
	),
);