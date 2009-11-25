<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database expression.
 * 
 * $Id: Database_Expression.php 4679 2009-11-10 01:45:52Z isaiah $
 * 
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Database_Expression_Core {

	protected $expression;

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	public function __toString()
	{
		return $this->expression;
	}
}
