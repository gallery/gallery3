<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Database
 *
 * SQL data types. If there are missing values, please report them:
 *
 * @link http://dev.kohanaphp.com/projects/kohana2
 */
$config = array
(
	// SQL-92
	'bit'                   => array('type' => 'string', 'exact' => TRUE),
	'bit varying'           => array('type' => 'string'),
	'character'             => array('type' => 'string', 'exact' => TRUE),
	'character varying'     => array('type' => 'string'),
	'date'                  => array('type' => 'string'),
	'decimal'               => array('type' => 'float', 'exact' => TRUE),
	'double precision'      => array('type' => 'float'),
	'float'                 => array('type' => 'float'),
	'integer'               => array('type' => 'int', 'min' => -2147483648, 'max' => 2147483647),
	'interval'              => array('type' => 'string'),
	'national character'    => array('type' => 'string', 'exact' => TRUE),
	'national character varying' => array('type' => 'string'),
	'numeric'               => array('type' => 'float', 'exact' => TRUE),
	'real'                  => array('type' => 'float'),
	'smallint'              => array('type' => 'int', 'min' => -32768, 'max' => 32767),
	'time'                  => array('type' => 'string'),
	'time with time zone'   => array('type' => 'string'),
	'timestamp'             => array('type' => 'string'),
	'timestamp with time zone' => array('type' => 'string'),

	// SQL:1999
	//'array','ref','row'
	'binary large object'   => array('type' => 'string', 'binary' => TRUE),
	'boolean'               => array('type' => 'boolean'),
	'character large object' => array('type' => 'string'),
	'national character large object' => array('type' => 'string'),

	// SQL:2003
	'bigint'    => array('type' => 'int', 'min' => -9223372036854775808, 'max' => 9223372036854775807),

	// SQL:2008
	'binary'            => array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
	'binary varying'    => array('type' => 'string', 'binary' => TRUE),

	// MySQL
	'bigint unsigned'   => array('type' => 'int', 'min' => 0, 'max' => 18446744073709551615),
	'decimal unsigned'  => array('type' => 'float', 'exact' => TRUE, 'min' => 0.0),
	'double unsigned'   => array('type' => 'float', 'min' => 0.0),
	'float unsigned'    => array('type' => 'float', 'min' => 0.0),
	'integer unsigned'  => array('type' => 'int', 'min' => 0, 'max' => 4294967295),
	'mediumint'         => array('type' => 'int', 'min' => -8388608, 'max' => 8388607),
	'mediumint unsigned' => array('type' => 'int', 'min' => 0, 'max' => 16777215),
	'real unsigned'     => array('type' => 'float', 'min' => 0.0),
	'smallint unsigned' => array('type' => 'int', 'min' => 0, 'max' => 65535),
	'text'              => array('type' => 'string'),
	'tinyint'           => array('type' => 'int', 'min' => -128, 'max' => 127),
	'tinyint unsigned'  => array('type' => 'int', 'min' => 0, 'max' => 255),
	'year'              => array('type' => 'string'),
);

// SQL-92
$config['char'] = $config['character'];
$config['char varying'] = $config['character varying'];
$config['dec'] = $config['decimal'];
$config['int'] = $config['integer'];
$config['nchar'] = $config['national char'] = $config['national character'];
$config['nchar varying'] = $config['national char varying'] = $config['national character varying'];
$config['varchar'] = $config['character varying'];

// SQL:1999
$config['blob'] = $config['binary large object'];
$config['clob'] = $config['char large object'] = $config['character large object'];
$config['nclob'] = $config['nchar large object'] = $config['national character large object'];
$config['time without time zone'] = $config['time'];
$config['timestamp without time zone'] = $config['timestamp'];

// SQL:2008
$config['varbinary'] = $config['binary varying'];

// MySQL
$config['bool'] = $config['boolean'];
$config['datetime'] = $config['timestamp'];
$config['double'] = $config['double precision'];
$config['double precision unsigned'] = $config['double unsigned'];
$config['enum'] = $config['set'] = $config['character varying'];
$config['fixed'] = $config['decimal'];
$config['fixed unsigned'] = $config['decimal unsigned'];
$config['int unsigned'] = $config['integer unsigned'];
$config['longblob'] = $config['mediumblob'] = $config['tinyblob'] = $config['binary large object'];
$config['longtext'] = $config['mediumtext'] = $config['tinytext'] = $config['text'];
$config['numeric unsigned'] = $config['decimal unsigned'];
$config['nvarchar'] = $config['national varchar'] = $config['national character varying'];
