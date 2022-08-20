<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Manipulate images using standard methods such as resize, crop, rotate, etc.
 * This class must be re-initialized for every image you wish to manipulate.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Image_Core {

	// Master Dimension
	const NONE = 1;
	const AUTO = 2;
	const HEIGHT = 3;
	const WIDTH = 4;

	// Flip Directions
	const HORIZONTAL = 5;
	const VERTICAL = 6;

	// Orientations
	const PORTRAIT = 7;
	const LANDSCAPE = 8;
	const SQUARE    = 9;

	// Allowed image types
	public static $allowed_types = array
	(
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG => 'png',
		IMAGETYPE_WEBP => 'webp',
		IMAGETYPE_TIFF_II => 'tiff',
		IMAGETYPE_TIFF_MM => 'tiff',
	);

	// Driver instance
	protected $driver;

	// Driver actions
	protected $actions = array();

	// Reference to the current image filename
	protected $image = '';

	/**
	 * Creates a new Image instance and returns it.
	 *
	 * @param   string   filename of image
	 * @param   array    non-default configurations
	 * @return  object
	 */
	public static function factory($image, $config = NULL)
	{
		return new Image($image, $config);
	}

	/**
	 * Creates a new image editor instance.
	 *
	 * @throws  Kohana_Exception
	 * @param   string   filename of image
	 * @param   array    non-default configurations
	 * @return  void
	 */
	public function __construct($image, $config = NULL)
	{
		static $check;

		// Make the check exactly once
		($check === NULL) and $check = function_exists('getimagesize');

		if ($check === FALSE)
			throw new Kohana_Exception('The Image library requires the getimagesize() PHP function, which is not available in your installation.');

		// Check to make sure the image exists
		if ( ! is_file($image))
			throw new Kohana_Exception('The specified image, :image:, was not found. Please verify that images exist by using file_exists() before manipulating them.', array(':image:' => $image));

		// Disable error reporting, to prevent PHP warnings
		$ER = error_reporting(0);

		// Fetch the image size and mime type
		$image_info = getimagesize($image);

		// Turn on error reporting again
		error_reporting($ER);

		// Make sure that the image is readable and valid
		if ( ! is_array($image_info) OR count($image_info) < 3)
			throw new Kohana_Exception('The file specified, :file:, is not readable or is not an image', array(':file:' => $image));

		// Check to make sure the image type is allowed
		if ( ! isset(Image::$allowed_types[$image_info[2]]))
			throw new Kohana_Exception('The specified image, :type:, is not an allowed image type.', array(':type:' => $image));

		// Image has been validated, load it
		$this->image = array
		(
			'file' => str_replace('\\', '/', realpath($image)),
			'width' => $image_info[0],
			'height' => $image_info[1],
			'type' => $image_info[2],
			'ext' => Image::$allowed_types[$image_info[2]],
			'mime' => $image_info['mime']
		);

		$this->determine_orientation();

		// Load configuration
		$this->config = (array) $config + Kohana::config('image');

		// Set driver class name
		$driver = 'Image_'.ucfirst($this->config['driver']).'_Driver';

		// Load the driver
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('The :driver: driver for the :library: library could not be found',
									   array(':driver:' => $this->config['driver'], ':library:' => get_class($this)));

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		// Validate the driver
		if ( ! ($this->driver instanceof Image_Driver))
			throw new Kohana_Exception('The :driver: driver for the :library: library must implement the :interface: interface',
									   array(':driver:' => $this->config['driver'], ':library:' => get_class($this), ':interface:' => 'Image_Driver'));
	}

	/**
	 * Works out the correct orientation for the image
	 *
	 * @return  void
	 */
	protected function determine_orientation()
	{
		switch (TRUE)
		{
			case $this->image['height'] > $this->image['width']:
				$orientation = Image::PORTRAIT;
			break;

			case $this->image['height'] < $this->image['width']:
				$orientation = Image::LANDSCAPE;
			break;

			default:
				$orientation = Image::SQUARE;
		}

		$this->image['orientation'] = $orientation;
	}

	/**
	 * Handles retrieval of pre-save image properties
	 *
	 * @param   string  property name
	 * @return  mixed
	 */
	public function __get($property)
	{
		if (isset($this->image[$property]))
		{
			return $this->image[$property];
		}
		else
		{
			throw new Kohana_Exception('The :property: property does not exist in the :class: class.',
									   array(':property:' => $property, ':class:' => get_class($this)));
		}
	}

	/**
	 * Resize an image to a specific width and height. By default, Kohana will
	 * maintain the aspect ratio using the width as the master dimension. If you
	 * wish to use height as master dim, set $image->master_dim = Image::HEIGHT
	 * This method is chainable.
	 *
	 * @throws  Kohana_Exception
	 * @param   integer  width
	 * @param   integer  height
	 * @param   integer  one of: Image::NONE, Image::AUTO, Image::WIDTH, Image::HEIGHT
	 * @return  object
	 */
	public function resize($width, $height, $master = NULL)
	{
		if ( ! $this->valid_size('width', $width))
			throw new Kohana_Exception('The width you specified, :width:, is not valid.', array(':width:' => $width));

		if ( ! $this->valid_size('height', $height))
			throw new Kohana_Exception('The height you specified, :height:, is not valid.', array(':height:' => $height));

		if (empty($width) AND empty($height))
			throw new Kohana_Exception('The dimensions specified for :function: are not valid.', array(':function:' => __FUNCTION__));

		if ($master === NULL)
		{
			// Maintain the aspect ratio by default
			$master = Image::AUTO;
		}
		elseif ( ! $this->valid_size('master', $master))
			throw new Kohana_Exception('The master dimension specified is not valid.');

		$this->actions['resize'] = array
		(
			'width'  => $width,
			'height' => $height,
			'master' => $master,
		);

		$this->determine_orientation();

		return $this;
	}

	/**
	 * Crop an image to a specific width and height. You may also set the top
	 * and left offset.
	 * This method is chainable.
	 *
	 * @throws  Kohana_Exception
	 * @param   integer  width
	 * @param   integer  height
	 * @param   integer  top offset, pixel value or one of: top, center, bottom
	 * @param   integer  left offset, pixel value or one of: left, center, right
	 * @return  object
	 */
	public function crop($width, $height, $top = 'center', $left = 'center')
	{
		if ( ! $this->valid_size('width', $width))
			throw new Kohana_Exception('The width you specified, :width:, is not valid.', array(':width:' => $width));

		if ( ! $this->valid_size('height', $height))
			throw new Kohana_Exception('The height you specified, :height:, is not valid.', array(':height:' => $height));

		if ( ! $this->valid_size('top', $top))
			throw new Kohana_Exception('The top offset you specified, :top:, is not valid.', array(':top:' => $top));

		if ( ! $this->valid_size('left', $left))
			throw new Kohana_Exception('The left offset you specified, :left:, is not valid.', array(':left:' => $left));

		if (empty($width) AND empty($height))
			throw new Kohana_Exception('The dimensions specified for :function: are not valid.', array(':function:' => __FUNCTION__));

		$this->actions['crop'] = array
		(
			'width'  => $width,
			'height' => $height,
			'top'    => $top,
			'left'   => $left,
		);

		$this->determine_orientation();

		return $this;
	}

	/**
	 * Allows rotation of an image by 180 degrees clockwise or counter clockwise.
	 *
	 * @param   integer  degrees
	 * @return  object
	 */
	public function rotate($degrees)
	{
		$degrees = (int) $degrees;

		if ($degrees > 180)
		{
			do
			{
				// Keep subtracting full circles until the degrees have normalized
				$degrees -= 360;
			}
			while($degrees > 180);
		}

		if ($degrees < -180)
		{
			do
			{
				// Keep adding full circles until the degrees have normalized
				$degrees += 360;
			}
			while($degrees < -180);
		}

		$this->actions['rotate'] = $degrees;

		return $this;
	}

	/**
         * Overlay a second image on top of this one.
         *
         * @throws Kohana_Exception
         * @param  string  $overlay_file          path to an image file
         * @param  integer $x                     x offset for the overlay
         * @param  integer $y                     y offset for the overlay
         * @param  integer $transparency          transparency percent
         */
	public function composite($overlay_file, $x, $y, $transparency)
	{
		$image_info = getimagesize($overlay_file);

		// Check to make sure the image type is allowed
		if ( ! isset(Image::$allowed_types[$image_info[2]]))
			throw new Kohana_Exception('The specified image, :type:, is not an allowed image type.', array(':type:' => $overlay_file));

		$this->actions['composite'] = array
		(
			'overlay_file'  => $overlay_file,
			'mime'          => $image_info['mime'],
			'x'             => $x,
			'y'             => $y,
			'transparency'  => $transparency
		);

		return $this;
	}

	/**
	 * Flip an image horizontally or vertically.
	 *
	 * @throws  Kohana_Exception
	 * @param   integer  direction
	 * @return  object
	 */
	public function flip($direction)
	{
		if ($direction !== Image::HORIZONTAL AND $direction !== Image::VERTICAL)
			throw new Kohana_Exception('The flip direction specified is not valid.');

		$this->actions['flip'] = $direction;

		return $this;
	}

	/**
	 * Change the quality of an image.
	 *
	 * @param   integer  quality as a percentage
	 * @return  object
	 */
	public function quality($amount)
	{
		$this->actions['quality'] = max(1, min($amount, 100));

		return $this;
	}

	/**
	 * Sharpen an image.
	 *
	 * @param   integer  amount to sharpen, usually ~20 is ideal
	 * @return  object
	 */
	public function sharpen($amount)
	{
		$this->actions['sharpen'] = max(1, min($amount, 100));

		return $this;
	}

	/**
	 * Save the image to a new image or overwrite this image.
	 *
	 * @throws  Kohana_Exception
	 * @param   string   new image filename
	 * @param   integer  permissions for new image
	 * @param   boolean  keep or discard image process actions
	 * @return  object
	 */
	public function save($new_image = FALSE, $chmod = 0644, $keep_actions = FALSE, $background = NULL)
	{
		// If no new image is defined, use the current image
		empty($new_image) and $new_image = $this->image['file'];

		// Separate the directory and filename
		$dir  = pathinfo($new_image, PATHINFO_DIRNAME);
		$file = pathinfo($new_image, PATHINFO_BASENAME);

		// Normalize the path
		$dir = str_replace('\\', '/', realpath($dir)).'/';

		if ( ! is_writable($dir))
			throw new Kohana_Exception('The specified directory, :dir:, is not writable.', array(':dir:' => $dir));

		if ($status = $this->driver->process($this->image, $this->actions, $dir, $file, FALSE, $background))
		{
			if ($chmod !== FALSE)
			{
				// Set permissions
				chmod($new_image, $chmod);
			}
		}

		if ($keep_actions !== TRUE)
		{
			// Reset actions. Subsequent save() or render() will not apply previous actions.
			$this->actions = array();
		}

		return $status;
	}

	/**
	 * Output the image to the browser.
	 *
	 * @param   boolean  keep or discard image process actions
	 * @return	object
	 */
	public function render($keep_actions = FALSE, $background = NULL)
	{
		$new_image = $this->image['file'];

		// Separate the directory and filename
		$dir  = pathinfo($new_image, PATHINFO_DIRNAME);
		$file = pathinfo($new_image, PATHINFO_BASENAME);

		// Normalize the path
		$dir = str_replace('\\', '/', realpath($dir)).'/';

		// Process the image with the driver
		$status = $this->driver->process($this->image, $this->actions, $dir, $file, TRUE, $background);

		if ($keep_actions !== TRUE)
		{
			// Reset actions. Subsequent save() or render() will not apply previous actions.
			$this->actions = array();
		}

		return $status;
	}

	/**
	 * Sanitize a given value type.
	 *
	 * @param   string   type of property
	 * @param   mixed    property value
	 * @return  boolean
	 */
	protected function valid_size($type, & $value)
	{
		if (is_null($value))
			return TRUE;

		if ( ! is_scalar($value))
			return FALSE;

		switch ($type)
		{
			case 'width':
			case 'height':
				if (is_string($value) AND ! ctype_digit($value))
				{
					// Only numbers and percent signs
					if ( ! preg_match('/^[0-9]++%$/D', $value))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'top':
				if (is_string($value) AND ! ctype_digit($value))
				{
					if ( ! in_array($value, array('top', 'bottom', 'center')))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'left':
				if (is_string($value) AND ! ctype_digit($value))
				{
					if ( ! in_array($value, array('left', 'right', 'center')))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'master':
				if ($value !== Image::NONE AND
				    $value !== Image::AUTO AND
				    $value !== Image::WIDTH AND
				    $value !== Image::HEIGHT)
					return FALSE;
			break;
		}

		return TRUE;
	}

} // End Image
