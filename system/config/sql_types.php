<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Database
 *
 * SQL data types. If there are missing values, please report them:
 *
 * @link  http://trac.kohanaphp.com/newticket
 */
$config = array
(
	'tinyint'			=> array('type' => 'int', 'max' => 127),
	'smallint'			=> array('type' => 'int', 'max' => 32767),
	'mediumint'			=> array('type' => 'int', 'max' => 8388607),
	'int'				=> array('type' => 'int', 'max' => 2147483647),
	'integer'			=> array('type' => 'int', 'max' => 2147483647),
	'bigint'			=> array('type' => 'int', 'max' => 9223372036854775807),
	'float'				=> array('type' => 'float'),
	'float unsigned'	=> array('type' => 'float', 'min' => 0),
	'boolean'			=> array('type' => 'boolean'),
	'time'				=> array('type' => 'string', 'format' => '00:00:00'),
	'time with time zone' => array('type' => 'string'),
	'date'				=> array('type' => 'string', 'format' => '0000-00-00'),
	'year'				=> array('type' => 'string', 'format' => '0000'),
	'datetime'			=> array('type' => 'string', 'format' => '0000-00-00 00:00:00'),
	'timestamp with time zone' => array('type' => 'string'),
	'char'				=> array('type' => 'string', 'exact' => TRUE),
	'binary'			=> array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
	'varchar'			=> array('type' => 'string'),
	'varbinary'			=> array('type' => 'string', 'binary' => TRUE),
	'blob'				=> array('type' => 'string', 'binary' => TRUE),
	'text'				=> array('type' => 'string')
);

// DOUBLE
$config['double'] = $config['double precision'] = $config['decimal'] = $config['real'] = $config['numeric'] = $config['float'];
$config['double unsigned'] = $config['float unsigned'];

// BIT
$config['bit'] = $config['boolean'];

// TIMESTAMP
$config['timestamp'] = $config['timestamp without time zone'] = $config['datetime'];

// ENUM
$config['enum'] = $config['set'] = $config['varchar'];

// TEXT
$config['tinytext'] = $config['mediumtext'] = $config['longtext'] = $config['text'];

// BLOB
$config['tsvector'] = $config['tinyblob'] = $config['mediumblob'] = $config['longblob'] = $config['clob'] = $config['bytea'] = $config['blob'];

// CHARACTER
$config['character'] = $config['char'];
$config['character varying'] = $config['varchar'];

// TIME
$config['time without time zone'] = $config['time'];
