<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Object Relational Mapping (ORM) "tree" extension. Allows ORM objects to act
 * as trees, with parents and children.
 *
 * $Id: ORM_Tree.php 3923 2009-01-22 15:37:04Z samsoir $
 *
 * @package    ORM
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class ORM_Tree_Core extends ORM {

	// Name of the child
	protected $ORM_Tree_children;

	// Parent keyword name
	protected $ORM_Tree_parent_key = 'parent_id';

	/**
	 * Overload ORM::__get to support "parent" and "children" properties.
	 *
	 * @param   string  column name
	 * @return  mixed
	 */
	public function __get($column)
	{
		if ($column === 'parent')
		{
			if (empty($this->related[$column]))
			{
				// Load child model
				$model = ORM::factory(inflector::singular($this->ORM_Tree_children));

				if (array_key_exists($this->ORM_Tree_parent_key, $this->object))
				{
					// Find children of this parent
					$model->where($model->primary_key, $this->object[$this->ORM_Tree_parent_key])->find();
				}

				$this->related[$column] = $model;
			}

			return $this->related[$column];
		}
		elseif ($column === 'children')
		{
			if (empty($this->related[$column]))
			{
				$model = ORM::factory(inflector::singular($this->ORM_Tree_children));

				if ($this->ORM_Tree_children === $this->table_name)
				{
					// Load children within this table
					$this->related[$column] = $model
						->where($this->ORM_Tree_parent_key, $this->object[$this->primary_key])
						->find_all();
				}
				else
				{
					// Find first selection of children
					$this->related[$column] = $model
						->where($this->foreign_key(), $this->object[$this->primary_key])
						->where($this->ORM_Tree_parent_key, NULL)
						->find_all();
				}
			}

			return $this->related[$column];
		}

		return parent::__get($column);
	}

} // End ORM Tree