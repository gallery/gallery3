<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Core_Driver_File extends Formo_Driver {

	public static function added( array $array)
	{
		$field = $array['field'];

		// Add necessary multipart/form-data attribute
		$field->parent(TRUE)->attr('enctype', 'multipart/form-data');
	}

	public static function get_tag()
	{
		return 'input';
	}

	public static function get_val( array $array)
	{
		$val = $array['val'];

		if (is_array($val))
		{
			if ( ! Arr::get($val, 'custom') AND ! Upload::not_empty($val))
			{
				$val = NULL;
			}
		}

		return $val
			? $val
			: NULL;
	}

	public static function get_attr( array $array)
	{
		$field = $array['field'];

		return array
		(
			'type' => 'file',
			'value' => null,
			'name' => $field->name(),
		);
	}

	public static function new_val( array $array)
	{
		$new_val = $array['new_val'];

		if (is_array($new_val))
		{
			
		}
		else
		{
			if (file_exists($new_val))
			{
				/*
					"name" => string(22) "php-docblock-1.2.0.zip"
					"type" => string(15) "application/zip"
					"tmp_name" => string(26) "/private/var/tmp/phpP9kI0O"
					"error" => integer 0
					"size" => integer 2580673
				*/

				$filename = $new_val;
				$parts = explode('/', $filename);

				// Determine mime type
				$finfo = new finfo(FILEINFO_MIME);
				$finfo_str = $finfo->file($filename);
				$finfo_parts = explode(';', $finfo_str);
				$mime = Arr::get($finfo_parts, 0);

				$new_val = array
				(
					'name' => Arr::get($parts, count($parts) - 1),
					'type' => $mime,
					'tmp_name' => $filename,
					'error' => 0,
					'size' => filesize($new_val),
					'custom' => TRUE,
				);
			}
		}

		return $new_val;
	}

}