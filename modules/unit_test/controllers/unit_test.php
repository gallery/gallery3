<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_test_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		// Run tests and show results!
		echo new Unit_Test;
	}

}