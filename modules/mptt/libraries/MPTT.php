<?php

/**
* File: libraries/MPTT.php
*
* An implementation of Joe Celko's Nested Sets as a Kohana model with ORM support.
*
*         
* Many thanks to 
*   * Thunder who supplied a similar class for Code Igniter and gave permission to me
*         to release it under whatever license I deem necessary:
*         http://www.codeigniter.com/wiki/Nested_Sets/
*   * mpatek, his class was the initial start
*         http://bakery.cakephp.org/articles/view/modified-preorder-tree-traversal-component
*   * Propel, for inspiring some methods and the parent_id and scope stuff
* 
* MPTT class
*   author    - dlib
*   license   - BSD 
*/

class MPTT extends ORM {
	
	public $children;
	
	public $parent;
	
	protected $left_column     =    'lft';
	
	protected $right_column    =    'rgt';
	
	protected $parent_column   =    'parent_id';
	
	protected $scope_column    =    'scope';
	
	protected $_scope           =    1;
	
	/**
	* Patch through to ORM construct
	*
	* @return void
	*/
	public function __construct($id = FALSE)
	{
		parent::__construct($id);
		
		if (!empty($id))
		{
			//Set the right scope on new objects
			$scope_column=$this->scope_column;
			$this->set_scope($this->$scope_column);
			$this->where='';
		}
		
		
	}

	//////////////////////////////////////////
	//  Lock functions
	//////////////////////////////////////////

	/**
	 * Locks tree table
	 * This is a straight write lock - the database blocks until the previous lock is released
	 */
	protected function lock_tree($aliases = array())
	{
		$sql = "LOCK TABLE " . $this->table . " WRITE";
		return self::$db->query($sql);
	}

	/**
	 * Unlocks tree table
	 * Releases previous lock
	 */
	protected function unlock_tree()
	{
		$sql = "UNLOCK TABLES";
		return self::$db->query($sql);
	}

	/**
	* Bit of an odd function since the node is supplied
	* supply a node object and it will return a whole bunch of descendants
	* @return tree object
	* @param $root_node mixed
	
	*/
	public function get_tree($root_node)
	{
		
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		
		if(is_object($root_node))
		{
			$root=$root_node;
		}   
		else
		{
			$root=$this->get_node($root_node);
		}    
		
		$children=$this->new_node();
		$children->where('`'.$lft_col.'`>'.$root->$lft_col.' AND `'.$rgt_col.'`<'.$root->$rgt_col);
		$children->where($this->scope_column,$this->get_scope());         
		$children->orderby($lft_col,'ASC');
		$children=$children->find_all();
		
		if(count($children)>0)
		{
			
			$parent=$root;
			$parent->children=array();

			foreach($children as $child_data)
			{
				
				$child=$child_data;
				$child->children=array();
				$child->parent=$parent;
				$child->scope=$this->get_scope();
				
					while(!$this->_is_descendant_of($child, $parent)) 
					{
						$parent = $parent->parent;
					}      
					
				$parent->children[]=$child;
				$parent=$child;
				
			}
		}
		
	return $root;
	}
	/*
	* *****************
	* Retrieval methods
	* *****************
	*/   
	/**
	* Current object will obtain its children
	* @see http://trac.symfony-project.com/wiki/sfPropelActAsNestedSetBehaviorPlugin
	* @see http://www.phpriot.com/articles/nested-trees-2/5
	* @return 
	*/
	public function get_children($return=false)
	{
		if(!$this->has_descendants())
			return false;
			
		$parent_id=$this->id;
		
		$this->children=array();
		$children=$this->new_node();
		$children->where($this->parent_column,$parent_id);
		$children->where($this->scope_column,$this->get_scope());   
		$children->orderby($this->left_column);
		
		foreach($children->find_all() as $child)
		{
			$child->parent=$this;
			
			$this->children[]=$child;
		}
		if($return== true)
		{
			return $this->children;
		}
	return $this;
	}
	
	/**
	* The current object will obtain all descendants
	* @return ORM object
	* @param $node_id Object
	*/
	public function get_descendants()
	{
		if($this->has_descendants())
			return $this->get_tree($this);
	}
	/**
	* Return an array of all leaves in the entire tree
	* 
	* @return array of ORM objects
	*/
	public function get_leaves()
	{
		
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
				
		$leaves=$this->new_node();
		$leaves->where($this->scope_column,$this->get_scope());          
		$leaves->where($rgt_col.' = '.$lft_col . ' + 1');
		
		return $leaves->find_all();
	}
	/**
	* Get the path leading up to the current node
	* @return array with ORM objects
	* 
	*/
	public function get_path()
	{

		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;

		$path=$this->new_node();
		$path->where($this->scope_column,$this->get_scope());          
		$path->where($lft_col . ' <= '.$this->$lft_col . ' AND ' . $rgt_col . ' >=' .$this->$rgt_col . ' ORDER BY '.$lft_col); 
	

		return $path->find_all();        

	}
	
	/**
	* Returns the root node
	* @return array $resultNode The node returned
	*/
	function get_root()
	{   
		return $this->get_node_where('`'.$this->left_column . '` = 1 ');
	} 
	/**
	* Returns a node by id
	* @return object
	* @param $node_id integer [optional]
	*/
	function get_node($node_id)
	{
		$scope_column=$this->scope_column;
		$class = get_class($this);
		$node=new $class();
		
		$node=$node->find($node_id,true);
		
		return $node;
	}
	/**
	* Returns a new empty node
	* @return object
	* @param $node_id integer [optional]
	*/
	function new_node(){
		
		$class = get_class($this);
		$node=new $class();
		return $node;
	}
	/**
	* Returns one node with where condition
	* @return object
	* @param $node_id integer [optional]
	*/    
	function get_node_where($where)
	{
		$scope_column=$this->scope_column;
		$class = get_class($this);
		$node  = new $class();
		$node->where($where);
		$node->where($this->scope_column,$this->get_scope());
		
		$node=$node->find(false,false);
		return $node;
	}
	/**
	* Returns the first child node of the given parentNode
	* 
	* @return array $resultNode The first child of the parent node supplied
	*/
	function get_first_child()
	{ 
		$lft_col = $this->left_column;

		return $this->get_node_where($this->left_column . " = " . ($this->$lft_col+1));
	}
	
	/**
	* Returns the last child node of the given parentNode
	* 
	* @return array $resultNode the last child of the parent node supplied
	*/
	function get_last_child()
	{ 
		$rgt_col = $this->right_column;
		
		return $this->get_node_where($this->right_column . " = " . ($this->$rgt_col-1));
	}
	
	/**
	* Returns the node that is the immediately prior sibling of the given node
	* 
	* @return array $resultNode The node returned
	*/
	function get_prev_sibling()
	{ 
		$lft_col = $this->left_column;

	return $this->get_node_where($this->right_column . " = " . ($this->$lft_col-1));
	}
	
	/**
	* Returns the node that is the next sibling of the given node
	* 
	* @return array $resultNode The node returned
	*/
	function get_next_sibling()
	{ 
		$rgt_col = $this->right_column;

		return $this->get_node_where($this->left_column . " = " . ($this->$rgt_col+1));
	}    
	/**
	* Returns the node that represents the parent of the given node
	* 
	* @return array $resultNode the node returned
	*/
	function get_parent()
	{ 
	
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		
		$whereArg = "           $leftcol    < " . $this->$leftcol . 
					" AND       $rightcol   > " . $this->$rightcol . 
					" ORDER BY  $rightcol ASC";
		return $this->get_node_where($whereArg);
	}   
/*
	* *****************
	* Modifying methods
	* *****************
	*/
	/**
	* Adds the first entry to the table (only call once in an empty table) else corruption will follow
	* @return    $node an array of left and right values
	*/ 
	function make_root()
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$scp_col=$this->scope_column;
		
		if(is_numeric($this->$lft_col) )
		{
			Log::add('error', 'Cannot make existing node root' );
			//existing nodes cannot become root
			return false;
		}

		$this->$lft_col=1;
		$this->$rgt_col=2;
		$this->$scp_col=$this->get_scope();
		
		$this->save_node();
		
		return $this;
	}
	/**
	* Not yet implemented
	* 
	*/    
	function insert_as_parent_of($child_node)
	{
		
	}
	/**
	* inserts a the object node as the first child of the supplied parent node
	* @param array $parentNode The node array of the parent to use
	* 
	* @return 
	*/
	function insert_as_first_child_of($parent_node)
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$scp_col=$this->scope_column;        
		$parent_column=$this->parent_column;

		//Set parent id (id of the parent, is childs parent id)                  
		$this->$parent_column=$parent_node->id;
		
		$this->$lft_col=$parent_node->$lft_col+1;
		$this->$rgt_col=$parent_node->$lft_col+2;
		//Get scope from current object (obsolete)
		$this->$scp_col=$this->get_scope();
		
		$this->lock_tree();
		$this->modify_node($this->$lft_col,2);
		$this->save_node();
		$this->unlock_tree();
		
		return $this;
	}
	/**
	* Same as insertNewChild except the new node is added as the last child
	* @param array $parentNode The node array of the parent to use
	* 
	* @return 
	*/
	function insert_as_last_child_of($parent_node) 
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$scp_col=$this->scope_column;              
		$parent_column=$this->parent_column;        
		
		//Set parent id (id of the parent, is childs parent id)          
		$this->$parent_column=$parent_node->id;
		
		$this->$lft_col=$parent_node->$rgt_col;
		$this->$rgt_col=$parent_node->$rgt_col+1;
		$this->$scp_col=$this->get_scope();                
		
		$this->lock_tree();		
		$this->modify_node($this->$lft_col,2);
		$this->save_node();
		$this->unlock_tree();
		
		return $this;
	}   
	/**
	* Adds a new node to the left of the supplied focusNode
	* @param array $focusNode The node to use as the position marker
	* 
	* @return  
	*/
	function insert_as_prev_sibling_of($focus_node)
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$parent_column=$this->parent_column;        
		$scp_col=$this->scope_column;  
						
		//Set parent id (siblings have the same parent)        
		$this->$parent_column=$focus_node->$parent_column;
		
		$this->$lft_col=$focus_node->$lft_col;
		$this->$rgt_col=$focus_node->$lft_col+1; 
		$this->$scp_col=$this->get_scope();        
		
		$this->lock_tree();		
		$this->modify_node($this->$lft_col,2);
		$this->save_node();
		$this->unlock_tree();		
			
		return $this;
	}     
	/**
	* Adds a new node to the right of the supplied focusNode
	* @param array $focusNode The node to use as the position marker
	* 
	* @return 
	*/
	function insert_as_next_sibling_of($focus_node)
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		$parent_column=$this->parent_column;  
		$scp_col=$this->scope_column;                
				
		//Set parent id (siblings have the same parent)        
		$this->$parent_column=$focus_node->$parent_column;
		
		$this->$lft_col=$focus_node->$rgt_col+1;
		$this->$rgt_col=$focus_node->$rgt_col+2; 
		$this->$scp_col=$this->get_scope();           

		$this->lock_tree();		
		$this->modify_node($this->$lft_col,2);
		$this->save_node();
		$this->unlock_tree();		
			
		return $this;
	}    
	/**
	* Why not, kill the entire tree
	* 
	* @return 
	*/
	function delete_tree()
	{
		$where=array($this->scope_column=>$this->get_scope());

		self::$db->delete($this->table, $where);
		return;
	} 
	/**
	*
	* overrides delete of ORM
	*/
	public function delete()
	{
		return $this->delete_node();   
	}
	/**
	* Deletes the given node ((itself)  from the tree table
	* @param children boolean set to false to not delete children 
	* and move them up the tree
	* @return boolean
	*/
	function delete_node($children=true)
	{
		
		$table              =       $this->table;
		$leftcol            =       $this->left_column;
		$rightcol           =       $this->right_column;
		$leftanchor         =       $this->$leftcol;
		$leftval            =       $this->$leftcol;
		$rightval           =       $this->$rightcol;
		$parent_column      =       $this->parent_column;  
		if($children==true)
		{

			$where=$leftcol . '>='.$leftval .' 
					AND '. $rightcol .' <= ' . $rightval . ' 
					AND '. $this->scope_column .' = '.$this->get_scope() ;  
			
			$this->lock_tree();
			//Delete node and children              
			self::$db->delete($this->table, $where);
			//Modify other nodes to restore tree integrity
			$this->modify_node($rightval+1, $leftval  - $rightval - 1);
			$this->unlock_tree();
		}
		else
		{
			if($this->is_root()){
				Log::add('error', 'Cannot delete root node without deleting its children' );
				return false;
			}
			$this->lock_tree();
			//Get children before the parent is gone
			//Set parent ids right again
			$parent_node=$this->get_node($this->$parent_column);
			
			$children=$this->get_children(true);
						
			//Delete the node
			$where=array('id'=>$this->id);
			self::$db->delete($this->table, $where);     
			
			//First update
			$sql = 'UPDATE `' . $this->table . '` 
					SET '. $this->left_column .'='.$this->left_column .'-1, '.
						$this->right_column.' = '.$this->right_column . '-1
					WHERE '. $this->left_column .' >= '.$this->$leftcol . '
					AND '.$this->right_column .' <= '.$this->$rightcol .'
					AND '.$this->scope_column.' = '.$this->get_scope().';';
			self::$db->query($sql);
	
			//Second update
			$sql = 'UPDATE `' . $this->table . '` 
					SET '. $this->left_column .'='.$this->left_column .' -2
					WHERE '. $this->right_column .' > '.$this->$rightcol . '-1
						AND '.$this->left_column .' > '.$this->$rightcol .'-1
						AND '.$this->scope_column.' = '.$this->get_scope().';';
			self::$db->query($sql);       
						
			//Third update              
			$sql = 'UPDATE `' . $this->table . '` 
					SET '. $this->right_column .'='.$this->right_column .'-2 
					WHERE '. $this->right_column .' > '.$this->$rightcol . '-1
						AND '.$this->scope_column.' = '.$this->get_scope().';';
			self::$db->query($sql);      
			
			//Set the parent ids
			if(is_array($children))
			{
				foreach($children as $child)
				{
					$child->$parent_column=$parent_node->id;
					$child->save_node();
				}
			}
			$this->unlock_tree();

		}

		
		return true;
	}         
	
	/**
	* Delete descendants but not node itself
	*  
	* @return 
	*/
	function delete_descendants()
	{
		$this->lock_tree();
		$this->get_children();
		foreach($this->children as $child)
		{
			$child->delete_node();
			
		}
		$this->unlock_tree();
		$this->children=array();
		return true;  
	}
	/**
	* Deletes children but not descendants, 
	* descendants move up the tree
	* @return 
	*/
	function delete_children()
	{
		$this->get_children();
		foreach($this->children as $child)
		{
			$child->delete_node(false);
			
		}
		$this->unlock_tree();
		$this->children=array();
		return true;          
	}

	// -------------------------------------------------------------------------
	//  MODIFY/REORGANISE TREE
	//
	//  Methods to move nodes around the tree. Method names should be 
	//  relatively self-explanatory! Hopefully ;)
	//
	// -------------------------------------------------------------------------

	//inter-scope moves might be something for later
	/**
	* Moves the given node to make it the next sibling of "target"
	* 
	* @param array $target The node to use as the position marker
	* @return array $newpos The new left and right values of the node moved
	*/
	function move_to_next_sibling_of( $target)
	{
		$rgt_col = $this->right_column;
		$parent_column=$this->parent_column;
		
		//Set parent id (siblings have the same parent) 
		$parent_id=$target->$parent_column;
		
		//only move when scopes are equal
		if($target->get_scope()==$this->get_scope())
		{
			$this->update_parent_id($parent_id);

			return $this->move_sub_tree( $target->$rgt_col+1);
		}
		return false;            
	}

	/** 
	* Moves the given node to make it the prior sibling of "target"
	* 
	* @param array $target The node to use as the position marker
	* @return array $newpos The new left and right values of the node moved
	*/
	function move_to_prev_sibling_of( $target)
	{
		$lft_col = $this->left_column;
		$parent_column=$this->parent_column;
		
		//Set parent id (siblings have the same parent)         
		$parent_id=$target->$parent_column;
		
		//only move when scopes are equal
		if($target->get_scope()==$this->get_scope())
		{
			$this->update_parent_id($parent_id);
	
			return $this->move_sub_tree( $target->$lft_col);   
		}
		return false;

	}

	/** 
	* Moves the given node to make it the first child of "target"
	* 
	* @param array $target The node to use as the position marker
	* @return array $newpos The new left and right values of the node moved
	*/    
	function move_to_first_child_of( $target)
	{
		$lft_col = $this->left_column;
		$parent_column=$this->parent_column;
		
		//Set parent id (id of the parent, is childs parent id)  
		$parent_id=$target->id;
		
		//only move when scopes are equal
		if($target->get_scope()==$this->get_scope())
		{
			$this->update_parent_id($parent_id);
	
			return $this->move_sub_tree( $target->$lft_col+1);
		}
		return false;
	}

	/** 
	* Moves the given node to make it the last child of "target"
	* 
	* @param array $target The node to use as the position marker
	* @return array $newpos The new left and right values of the node moved
	*/
	function move_to_last_child_of($target)
	{
		$rgt_col = $this->right_column;          
		$parent_column=$this->parent_column;

		//Set parent id (id of the parent, is childs parent id)  
		$parent_id=$target->id;
		
		//only move when scopes are equal
		if($target->get_scope()==$this->get_scope())
		{
			$this->update_parent_id($parent_id);
	
			return $this->move_sub_tree($target->$rgt_col);
		}
		return false;
	}
	
	/*
	* *****************
	* Check methods
	* *****************
	*/
	/**
	* Returns true or false 
	* (in reality, it checks to see if the given left and
	* right values _appear_ to be valid not necessarily that they _are_ valid)
	* 
	* @return boolean 
	*/
	function is_valid_node()
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		
		return ($this->$leftcol < $this->$rightcol);
	}
	
	/**
	* Tests whether the given node has an ancestor 
	* (effectively the opposite of isRoot yes|no)
	* 
	* @return boolean
	*/
	function has_parent()
	{ 
		return $this->is_valid_node($this->get_parent());
	}
	
	/**
	* Tests whether the given node has a prior sibling or not
	* 
	* @return boolean
	*/
	function has_prev_sibling()
	{ 
		return $this->is_valid_node($this->get_prev_sibling());
	}
	
	/**
	* Test to see if node has siblings after itself
	* 
	* @return boolean
	*/
	function has_next_sibling()
	{ 
		return $this->is_valid_node($this->get_next_sibling());
	}
	
	/**
	* Test to see if node has children
	* 
	* @return boolean
	*/
	function has_descendants()
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		return (($this->$rightcol - $this->$leftcol) > 1);
	}

	/**
	* Test to see if the given node is also the root node
	* 
	* @return boolean
	*/
	function is_root()
	{
		$leftcol        =       $this->left_column;
		return ($this->$leftcol == 1);
	}
	
	/**
	* Test to see if the given node is a leaf node (ie has no children)
	* 
	* @return boolean
	*/
	function is_leaf()
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		return (($this->$rightcol - $this->$leftcol) == 1);
	}
	
	/**
	* Test to see if the node is a descendant of the given node
	* @param array $control_node the node to use as the parent or ancestor
	* @return boolean
	*/
	function is_descendant_of($control_node)
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		
		return (        ($this->$leftcol  >   $control_node->$leftcol) 
					and ($this->$rightcol <   $control_node->$rightcol)
			);
	}
	
	/**
	* Test to see if the node is a descendant of the given node
	* @param array $control_node the node to use as the parent or ancestor
	* @return boolean
	*/
	function is_child_of($control_node)
	{   
		$child_id=$this->id;
		$parent_id=$control_node->id;
		
		self::$db->select('count(*) as is_child');
		self::$db->from($this->table);       
		self::$db->where('id',$child_id);          
		self::$db->where($this->parent_column,$parent_id);                 
		self::$db->where($this->scope_column, $this->get_scope());
		
		$result=self::$db->get(); 
		
		if ($row = $result->current()) 
		{
			return $row->is_child > 0;
		}

		return false;
		
	}    
	
	/**
	* Test to determine whether the node is infact also the $control_node (is A === B)
	* @param array $control_node The node prototype to use for the comparison
	* @return boolean
	*/
	function is_equal_to($control_node)
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		
		return (($this->$leftcol==$control_node->$leftcol) and ($this->$rightcol==$control_node->$rightcol));
	}

	/**
	* Combination method of is_descendant and is_equal
	* 
	* @param array $controlNode The node prototype to use for the comparison
	* @return boolean
	*/
	function is_descendant_or_equal_to($controlNode)
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		
		return (($this->$leftcol>=$control_node->$leftcol) and ($this->$rightcol<=$control_node->$rightcol));
	}
	/*
	* *****************
	* Informational methods
	* *****************
	*/
	/**
	* Returns the tree level for the given node (assuming root node is at level 0)
	* 
	* @return integer The level of the supplied node
	*/
	function get_level()
	{
		$leftval        = (int)  $this->$lft_col;
		$rightval       = (int)  $this->$rgt_col;
		
		self::$db->select('COUNT(*) AS level');
		self::$db->from($this->table);       
		self::$db->where($this->left_column.' < '.$leftval);
		self::$db->where($this->right_column.' < '.$rightval);                            
		self::$db->where($this->scope_column, $this->get_scope());
		
		$query=self::$db->get();        
		if($query->count() > 0) {
			$result = $query->current();
			return (int) $result->level;
		} else {
			return 0;
		}
	}    
	/**
	* Output number of descendants this node has
	* @return integer
	* 
	*/
	function get_number_of_descendants(){
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
		// Generate WHERE
	
	return (int) ( $this->$rgt_col - $this->$lft_col -1)/2; 
		
	}     
	/**
	* Output number of children of this node
	* @return integer 
	*/
	function get_number_of_children()
	{ 
	
		self::$db->select('count(*) as num_children');
		self::$db->from($this->table);       
		self::$db->where($this->parent_column,$this->id);          
		self::$db->where($this->scope_column, $this->get_scope());
		
		$result=self::$db->get();
		
		if($row = $result->current())
			return (int) $row->num_children;

		return -1;    
	}
	/**
	* Get current scope of the object
	* @return integer
	*/
	function get_scope()
	{ 
		return $this->_scope;
	}
	/**
	* Set scope of current object, retrieved objects calls this in constructor
	*/
	function set_scope($value)
	{
		$this->_scope=$value;
		return $this;
	}



	/* *****************************************
	* Print methods, more or less debug methods
	* *****************************************
	*/
	/**
	* Debug tree
	*/
	function debug_tree($tree,  $disp_col, $ind = '')
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
				$parent_column=$this->parent_column;
		
		echo $ind .'#'.$tree->id.' '.$tree->$lft_col.'- '.$tree->$disp_col .' p:'.$tree->$parent_column.' -'.$tree->$rgt_col.'<br>';
		if(is_array($tree->children))
		{
			foreach($tree->children as $child)
			{
				$this->debug_tree($child,$disp_col,'....'.$ind);
			}    
		}


	}
	/**
	* Will rebuild tree according to the parent_id column
	* Warning, the order of the tree might not exactly be maintained
	* Might be slow for big trees
	* Call this method only with the root_id and its left value.
	* @return 
	* @param $parent_id Object
	* @param $left Object
	*/
	function rebuild_tree($parent_id, $left) {
		$this->lock_tree();
		// the right value of this node is the left value + 1
		$right = $left+1;
	
		// get all children of this node
		self::$db->select('id');                            
		self::$db->where($this->parent_column, $parent_id);
		self::$db->where($this->scope_column, $this->get_scope());
		self::$db->from($this->table);
		$result=self::$db->get();
		
		foreach ($result as $row) {
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			$right = $this->rebuild_tree($row->id, $right);
		}
		
		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
			
		self::$db->set($this->left_column, $left);
		self::$db->set($this->right_column, $right);
		self::$db->where('id',$parent_id);
		self::$db->where($this->scope_column, $this->get_scope());
		self::$db->update($this->table);
		// return the right value of this node + 1
		return $right+1;
		
		$this->unlock_tree();
	} 
	
	/*
	*  Protected functions
	*  
	*/
	/**
	* check whether child is child of parent (internal)
	* 
	* @return 
	* @param $child Object
	* @param $parent Object
	*/     
	protected function _is_descendant_of($child, $parent)
	{
		$lft_col = $this->left_column;
		$rgt_col = $this->right_column;
				
		return ($child->$lft_col > $parent->$lft_col && $child->$rgt_col < $parent->$rgt_col);
	}     
	
		/** 
	* The method that performs moving/renumbering operations 
	* 
	* @param array $targetValue Position integer to use as the target
	* @return array $newpos The new left and right values of the node moved
	* @access private
	*/
	protected function move_sub_tree($targetValue)
	{ 
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
				
		$sizeOfTree = $this->$rightcol - $this->$leftcol + 1;
		$this->modify_node($targetValue, $sizeOfTree);
		
		
		if($this->$leftcol >= $targetValue)
		{
			$this->$leftcol  += $sizeOfTree;
			$this->$rightcol += $sizeOfTree;
		}

		$newpos = $this->modify_node_range($this->$leftcol, $this->$rightcol, $targetValue - $this->$leftcol);

		$this->modify_node($this->$rightcol+1, - $sizeOfTree);
		
		if($this->$leftcol <= $targetValue)
		{ 
			$newpos[$this->left_column] -= $sizeOfTree;
			$newpos[$this->right_column] -= $sizeOfTree;
		}

		return $newpos;
	}
	/**
	* _modifyNodeRange
	*
	* @param $lowerbound integer value of lowerbound of range to move
	* @param $upperbound integer value of upperbound of range to move
	* @param $changeVal unsigned integer of change amount
	* @access private
	*/
	
	protected function modify_node_range($lowerbound, $upperbound, $changeVal)
	{
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		$table          =       $this->table;
		$scope_col      =       $this->scope_column;
		
		$sql = "UPDATE      $table 
				SET         $leftcol    =   $leftcol    +   $changeVal 
				WHERE       $leftcol    >=  $lowerbound  
				AND         $leftcol    <=  $upperbound
				AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		self::$db->query($sql);
		
		$sql = "UPDATE      $table
				SET         $rightcol   =   $rightcol   +   $changeVal
				WHERE       $rightcol   >=  $lowerbound
				AND         $rightcol   <=  $upperbound
				AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		self::$db->query($sql);
		
		$retArray = array(
							$this->left_column  =>  $lowerbound+$changeVal, 
							$this->right_column =>  $upperbound+$changeVal
						); 
		return $retArray;
	} // END: Method _modifyNodeRange     
	
	/**
	* Update the parent id of this record, the ORM class handles
	* it when the parent id didn't change
	* @return 
	* @param $node Object
	* @param $parent_id Object
	*/
	protected function update_parent_id($parent_id)
	{
		$parent_column=$this->parent_column;
		$this->$parent_column=$parent_id;
		return $this->save_node();
	}
	/**
	* _modifyNode
	*
	* Adds $changeVal to all left and right values that are greater than or
	* equal to $node_int
	* 
	* @param  $node_int The value to start the shift from
	* @param  $changeVal unsigned integer value for change
	* @access private
	*/
	protected function modify_node($node_int, $changeVal)
	{
		$leftcol        =       $this->left_column;
		$rightcol       =       $this->right_column;
		$table          =       $this->table;
		$scope_col      =        $this->scope_column;
		
		$sql =  "UPDATE     $table " .
				"SET        $leftcol = $leftcol + $changeVal ".
				"WHERE      $leftcol >= $node_int
				AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		self::$db->query($sql);
		
		$sql =  "UPDATE     $table " .
				"SET        $rightcol = $rightcol + $changeVal ".
				"WHERE      $rightcol >= $node_int
				AND ".$this->scope_column.' = '.$this->$scope_col.';';
		
		self::$db->query($sql);
		
		return true;
	} // END: _modifyNode
	/**
	*  save_node
	* 
	*  Inserts a new node into the tree, or saves the current one
	*
	*  @return boolean True/False dependent upon the success of the operation
	*  @access private
	*/
	protected function save_node( )
	{
		if ($this->save()) {
			// Return true on success
			return TRUE;
		}
		
		return false;
	}         
}    
