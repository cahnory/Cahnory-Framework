<?php

	abstract class CahnoryORM_Record implements Iterator, ArrayAccess
	{
		
		/**
	     *	The record table alias id
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_aliasID		=	0;
		
		private	$_aliasCounter	=	0;
		
		/**
	     *	The record class name
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_class;
		
		/**
	     *	The columns (fields and relations) offset
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_columns	=	array();
		
		/**
	     *	The database object to query
	     *
	     *	@var CahnoryORM_Database
	     *	@access	private
	     */
		private	$_database;
		
		/**
	     *	The fields by offset
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_fields	=	array();
		
		/**
	     *	If the definition was set
	     *
	     *	@var bool
	     *	@access	private
	     */
		private	$_isDefined;
		
		/**
	     *	If the relations definition was set
	     *
	     *	@var bool
	     *	@access	private
	     */
		private	$_isRelated	=	false;
		
		/**
	     *	If the record values are saved
	     *
	     *	@var bool
	     *	@access	private
	     */	
		private	$_isSaved = false;
		
		/**
	     *	The record name
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_name;
		
		/**
	     *	The current offset
	     *
	     *	@var int
	     *	@access	private
	     */	
		private	$_offset	=	0;
		
		/**
	     *	All the record offsets
	     *
	     *	@var array
	     *	@access	private
	     */	
		private	$_offsets	=	array();
		
		/**
	     *	The primary key offset
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_primary	=	'id';
		
		/**
	     *	The prepared SQL queries
	     *
	     *	@var array
	     *	@access	private
	     *	@static
	     */
		static	private	$_queries	=	array();
		
		/**
	     *	The related records by offset
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_relations	=	array();
		
		/**
	     *	The table name
	     *
	     *	@var string
	     *	@access	private
	     */	
		private	$_table;
		
		/**
	     *	All the values by offset
	     *
	     *	@var array
	     *	@access	private
	     */	
		private	$_values	=	array();
		
		final public	function	__construct(CahnoryORM_Database $database)
		{
			$this->_class		=	get_class($this);
			$this->_name		=	$this->_class;
			$this->_database	=	$database;
		}
		
		public	function	__get($offset)
		{
			return	$this->getField($offset);
		}
		
		public	function	__set($offset, $value) {
			if($field = $this->getField($offset)) {
				return	$field->setValue($value);
			}
		}
		
		public	function	define()
		{
			if(!$this->_isDefined) {
				$this->setDefinition();
			
				//	Add primary column
				if($this->_primary && !isset($this->_columns[$this->_primary])) {
					$this->hasColumn($this->_primary, 'int', 11);
				}
				
				$this->_isDefined		=	true;
			}
		}
		
		/**
		 *	Fetch a row by record and sub records
		 *
		 *	@param array $row the result row
		 *
		 *	@return CahnoryORM_Record
		 *
		 *	@access private
		 */
		private	function	_fetch($row)
		{
			$record	=	$this->_database->getTable($this->_name);
			$record->fill(array_combine(
				array_keys($this->_values),
				array_splice($row, 0, sizeof($this->_values))
			));
			foreach($this->_relations as $relation) {
				$relation['record']->fill(array_combine(
					array_keys($relation['record']->_values),
					array_splice($row, 0, sizeof($relation['record']->_values))
				));
			}
			return	$record;
		}
		
		/**
		 *	Set record values using an array.
		 *	Array keys are used as offset.
		 *
		 *	@param array $values the values
		 *
		 *	@access public
		 */
		public	function	fill(array $values)
		{
			foreach($values as $offset => $value) {
				if(!array_key_exists($offset, $this->_columns)) {
					$this->_columns[$offset]->setValue($value);
				}
			}
		}
		
		/**
		 *	Set record values using an array.
		 *	Array keys are used as offset.
		 *
		 *	@param mixed $primary the primary value
		 *
		 *	@return mixed an array containing found records.
		 *	              If the primary argument's not null,
		 *	              a single record or NULL is returned.
		 *
		 *	@access public
		 */
		public	function	find($primary = NULL)
		{
			$this->_relate();
			
			$access	=	$this->_database->getAccess();
			$query	=	$this->getQuery('find');
			return	$query;
			$filter	=	$this->getQuery('filter');
			var_dump($filter);
			$access->query($this->getQuery('find'), array());
			
			$output	=	array();
			while($row = $access->fetch()) {
				$output[]	=	$this->_fetch($row);
			}
			exit();
		}
		
		public	function	getField($offset)
		{
			if(array_key_exists($offset, $this->_columns)) {
				return	$this->_columns[$offset];
			}
		}
		
		/**
		 *	Returns a query and make it if it wasn't
		 *
		 *	@param string $name the query name
		 *
		 *	@access protected
		 */
		public	function	getQuery($name, $id = 0)
		{
			if(!isset(self::$_queries[$this->_class][$name])) {
				if($name === 'find') {
					$select	=	'SELECT';
					$from	=	'FROM '.$this->_table.' t'.$id;
					$i		=	0;
					
					foreach($this->_fields as $offset => $field) {
						$select	.=	$i != 0 ? ',' : ' ';
						$select	.=	'`t'.$id.'`.`'.$offset.'`';
						$i++;
					}
					
					$relations	=	$this->_relations;
					while($relations) {
						$relation	=	array_shift($relations);
						$select	.=	'.``t'.$relation->_aliasID.'`.`'
								.	implode('.``t'.$relation->_aliasID.'`.`', array_keys($relation->_fields))
								.	'`';
								var_dump($relation->_relations);
						$relations	=	array_merge($relations, $relation->_relations);								
					}
					foreach($this->_relations as $offset => $relation) {
						$from	.=	' LEFT JOIN '.$relation->_table.' t'.$relation->_aliasID;
					}
					
					self::$_queries[$this->_class][$name]	=	$select.' '.$from;
					return	$select.' '.$from;
				} elseif($name === 'save') {
					
				} elseif($name === 'filter') {
				
				}
			}
		}
		
		/**
		 *	Returns the record name
		 *
		 *	@param string $name the record name
		 *
		 *	@access protected
		 */
		public	function	getName() { return	$this->_name; }
		
		/**
		 *	Defines the record name
		 *
		 *	@param string $name the record name
		 *
		 *	@access protected
		 */
		final protected	function	hasName($name)
		{
			$this->_name	=	$name;
		}
		
		/**
		 *	Defines the primary key offset
		 *
		 *	@param string $offset the column offset
		 *
		 *	@access protected
		 */
		final protected	function	hasPrimary($offset)
		{
			$this->_primary	=	$offset;
		}
		
		/**
		 *	Defines the table name
		 *
		 *	@param string $table the table name
		 *
		 *	@access protected
		 */
		final protected	function	hasTable($table)
		{
			$this->_table	=	$table;
		}
		
		/**
		 *	Defines a column
		 *
		 *	@param string $offset  the column offset
		 *	@param string $type    the column type
		 *	@param string $option  the column type option
		 *	@param string $default the column default value
		 *
		 *	@access protected
		 */
		final protected	function	hasColumn($offset, $type = NULL, $option = NULL, $default = NULL)
		{
			$this->_columns[$offset]	=	new CahnoryORM_Field($this, $offset, $type, $option, $default);
			$this->_fields[$offset]		=	$this->_columns[$offset];
			$this->_offsets[]			=	$offset;
		}
		
		final protected function	hasOne($name, $offset = NULL)
		{
			if($record = $this->_database->$name) {
				if($offset === NULL) $offset	=	$name;
				$record->_aliasID			=	++$this->_aliasCounter;
				$record->_aliasCounter		=&	$this->_aliasCounter;
				$record->_relate();
				$this->_columns[$offset]	=	$record;
				$this->_relations[$offset]	=	$record;
			}
		}
		
		/**
		 *	Prepares the relations
		 *
		 *	@access private
		 */
		private	function	_relate()
		{
			if(!$this->_isRelated) {
				$this->setRelations();
				$this->_isRelated	=	true;
			}
		}
		
		/**
		 *	Save the record and sub records in the database
		 *
		 *	@return bool if the record was correctly saved
		 *
		 *	@access private
		 */
		public	function	save()
		{
		}
		
		/**
		 *	Method overrided by extend classes. Used to
		 *	set the record definition (columns, table, ...)
		 *
		 *	@access public
		 */
		protected	function	setDefinition() {}
				
		/**
		 *	Method overrided by extend classes. Used
		 *	to set the relation with other records.
		 *
		 *	@access public
		 */
		protected	function	setRelations() {}
		
		/* !Iterator implement */
		public function rewind()
		{
			$this->_offset = 0;
		}
	
		public function current()
		{
			return $this->_columns[$this->_offsets[$this->_offset]];
		}
	
		public function key()
		{
			return $this->_offsets[$this->_offset];
		}
	
		public function next()
		{
			++$this->_offset;
		}
	
		public function valid()
		{
			$this->_relate();
			return	isset($this->_offsets[$this->_offset]);
		}
		
		/* !ArrayAccess implement [OK] */
		public function offsetSet($offset, $value)
		{
			if(!array_key_exists($offset, $this->_columns)) {
				$this->_columns[$offset]->setValue($value);
			}
		}
		
		public function offsetExists($offset)
		{
			return in_array($offset, $this->_offsets);
		}
		
		public function offsetUnset($offset)
		{
			if(!array_key_exists($offset, $this->_columns)) {
				$this->_columns[$offset]->setValue(NULL);
			}
		}
		
		public function offsetGet($offset)
		{
			if(array_key_exists($offset, $this->_columns)) {
				return	$this->_columns[$offset];
			}
		}	
	}

?>