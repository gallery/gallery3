<?php defined("SYSPATH") or die("No direct script access.");
/**
 * FORGE hidden input library.
 *
 * $Id: Form_Hidden.php 3326 2008-08-09 21:24:30Z Shadowhand $
 *
 * @package    Forge
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Form_Hidden_Core extends Form_Input {

	protected $data = array
	(
		'name'  => '',
		'value' => '',
	);

	public function render()
	{
		return form::hidden($this->data['name'], $this->data['value']);
	}

} // End Form Hidden