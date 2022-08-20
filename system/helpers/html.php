<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * HTML helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class html_Core {

	// Enable or disable automatic setting of target="_blank"
	public static $windowed_urls = FALSE;

	/**
	 * Convert special characters to HTML entities
	 *
	 * @param   string   string to convert
	 * @param   boolean  encode existing entities
	 * @return  string
	 */
	public static function chars($str, $double_encode = TRUE)
	{
		if (is_null($str)) return '';

		// Return HTML entities using the Kohana charset
		return htmlspecialchars($str, ENT_QUOTES, Kohana::CHARSET, $double_encode);
	}

	/**
	 * Create HTML link anchors.
	 *
	 * @param   string  URL or URI string
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  non-default protocol, eg: https
	 * @param   boolean option to escape the title that is output
	 * @return  string
	 */
	public static function anchor($uri, $title = NULL, $attributes = NULL, $protocol = NULL, $escape_title = FALSE)
	{
		if ($uri === '')
		{
			$site_url = url::base(FALSE);
		}
		elseif (strpos($uri, '#') === 0)
		{
			// This is an id target link, not a URL
			$site_url = $uri;
		}
		elseif (strpos($uri, '://') === FALSE)
		{
			$site_url = url::site($uri, $protocol);
		}
		else
		{
			if (html::$windowed_urls === TRUE AND empty($attributes['target']))
			{
				$attributes['target'] = '_blank';
			}

			$site_url = $uri;
		}

		return
		// Parsed URL
		'<a href="'.htmlspecialchars($site_url, ENT_QUOTES, Kohana::CHARSET, FALSE).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the parsed URL
		.($escape_title ? htmlspecialchars((($title === NULL) ? $site_url : $title), ENT_QUOTES, Kohana::CHARSET, FALSE) : (($title === NULL) ? $site_url : $title)).'</a>';
	}

	/**
	 * Creates an HTML anchor to a file.
	 *
	 * @param   string  name of file to link to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @param   string  non-default protocol, eg: ftp
	 * @return  string
	 */
	public static function file_anchor($file, $title = NULL, $attributes = NULL, $protocol = NULL)
	{
		return
		// Base URL + URI = full URL
		'<a href="'.htmlspecialchars(url::base(FALSE, $protocol).$file, ENT_QUOTES, Kohana::CHARSET, FALSE).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the filename part of the URI
		.(($title === NULL) ? end(explode('/', $file)) : $title) .'</a>';
	}

	/**
	 * Generates an obfuscated version of an email address.
	 *
	 * @param   string  email address
	 * @return  string
	 */
	public static function email($email)
	{
		$safe = '';
		foreach (str_split($email) as $letter)
		{
			switch (($letter === '@') ? rand(1, 2) : rand(1, 3))
			{
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		return $safe;
	}

	/**
	 * Creates an email anchor.
	 *
	 * @param   string  email address to send to
	 * @param   string  link text
	 * @param   array   HTML anchor attributes
	 * @return  string
	 */
	public static function mailto($email, $title = NULL, $attributes = NULL)
	{
		if (empty($email))
			return $title;

		// Remove the subject or other parameters that do not need to be encoded
		if (strpos($email, '?') !== FALSE)
		{
			// Extract the parameters from the email address
			list ($email, $params) = explode('?', $email, 2);

			// Make the params into a query string, replacing spaces
			$params = '?'.str_replace(' ', '%20', $params);
		}
		else
		{
			// No parameters
			$params = '';
		}

		// Obfuscate email address
		$safe = html::email($email);

		// Title defaults to the encoded email address
		empty($title) and $title = $safe;

		// Parse attributes
		empty($attributes) or $attributes = html::attributes($attributes);

		// Encoded start of the href="" is a static encoded version of 'mailto:'
		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$safe.$params.'"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * Generate a "breadcrumb" list of anchors representing the URI.
	 *
	 * @param   array   segments to use as breadcrumbs, defaults to using Router::$segments
	 * @return  string
	 */
	public static function breadcrumb($segments = NULL)
	{
		empty($segments) and $segments = Router::$segments;

		$array = array();
		while ($segment = array_pop($segments))
		{
			$array[] = html::anchor
			(
				// Complete URI for the URL
				implode('/', $segments).'/'.$segment,
				// Title for the current segment
				ucwords(inflector::humanize($segment))
			);
		}

		// Retrun the array of all the segments
		return array_reverse($array);
	}

	/**
	 * Creates a meta tag.
	 *
	 * @param   string|array   tag name, or an array of tags
	 * @param   string         tag "content" value
	 * @return  string
	 */
	public static function meta($tag, $value = NULL)
	{
		if (is_array($tag))
		{
			$tags = array();
			foreach ($tag as $t => $v)
			{
				// Build each tag and add it to the array
				$tags[] = html::meta($t, $v);
			}

			// Return all of the tags as a string
			return implode("\n", $tags);
		}

		// Set the meta attribute value
		$attr = in_array(strtolower($tag), Kohana::config('http.meta_equiv')) ? 'http-equiv' : 'name';

		return '<meta '.$attr.'="'.$tag.'" content="'.$value.'" />';
	}

	/**
	 * Creates a stylesheet link.
	 *
	 * @param   string|array  filename, or array of filenames to match to array of medias
	 * @param   string|array  media type of stylesheet, or array to match filenames
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function stylesheet($style, $media = FALSE, $index = FALSE)
	{
		return html::link($style, 'stylesheet', 'text/css', $media, $index);
	}

	/**
	 * Creates a link tag.
	 *
	 * @param   string|array  filename
	 * @param   string|array  relationship
	 * @param   string|array  mimetype
	 * @param   string|array  specifies on what device the document will be displayed
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function link($href, $rel, $type, $media = FALSE, $index = FALSE)
	{
		$compiled = '';

		if (is_array($href))
		{
			foreach ($href as $_href)
			{
				$_rel   = is_array($rel) ? array_shift($rel) : $rel;
				$_type  = is_array($type) ? array_shift($type) : $type;
				$_media = is_array($media) ? array_shift($media) : $media;

				$compiled .= html::link($_href, $_rel, $_type, $_media, $index);
			}
		}
		else
		{
			if (strpos($href, '://') === FALSE)
			{
				// Make the URL absolute
				$href = url::base($index).$href;
			}

			$attr = array
			(
				'rel' => $rel,
				'type' => $type,
				'href' => $href,
			);

			if ( ! empty($media))
			{
				// Add the media type to the attributes
				$attr['media'] = $media;
			}

			$compiled = '<link'.html::attributes($attr).' />';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a script link.
	 *
	 * @param   string|array  filename
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function script($script, $index = FALSE)
	{
		$compiled = '';

		if (is_array($script))
		{
			foreach ($script as $name)
			{
				$compiled .= html::script($name, $index);
			}
		}
		else
		{
			if (strpos($script, '://') === FALSE)
			{
				// Add the suffix only when it's not already present
				$script = url::base((bool) $index).$script;
			}

			$compiled = '<script type="text/javascript" src="'.$script.'"></script>';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a image link.
	 *
	 * @param   string        image source, or an array of attributes
	 * @param   string|array  image alt attribute, or an array of attributes
	 * @param   boolean       include the index_page in the link
	 * @return  string
	 */
	public static function image($src = NULL, $alt = NULL, $index = FALSE)
	{
		// Create attribute list
		$attributes = is_array($src) ? $src : array('src' => $src);

		if (is_array($alt))
		{
			$attributes += $alt;
		}
		elseif ( ! empty($alt))
		{
			// Add alt to attributes
			$attributes['alt'] = $alt;
		}

		if (strpos($attributes['src'], '://') === FALSE)
		{
			// Make the src attribute into an absolute URL
			$attributes['src'] = url::base($index).$attributes['src'];
		}

		return '<img'.html::attributes($attributes).' />';
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * @param   string|array  array of attributes
	 * @return  string
	 */
	public static function attributes($attrs)
	{
		if (empty($attrs))
			return '';

		if (is_string($attrs))
			return ' '.$attrs;

		$compiled = '';
		foreach ($attrs as $key => $val)
		{
			if (is_null($val)) $val = '';
			$compiled .= ' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES, Kohana::CHARSET).'"';
		}

		return $compiled;
	}

} // End html
