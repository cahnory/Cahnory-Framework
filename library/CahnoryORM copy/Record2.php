<?php

	abstract class CahnoryORM_Record2 implements Iterator, ArrayAccess
	{
		/**
	     *	The record class name
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_class;
		
		/**
	     *	The columns settings by offset
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
	     *	If the definition was set
	     *
	     *	@var bool
	     *	@access	private
	     */
		private	$_defined;
		
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
	     *	The parent record or NULL
	     *
	     *	@var CahnoryORM_Record
	     *	@access	private
	     */
		private	$_parent;
		
		/**
	     *	The prepared SQL queries
	     *
	     *	@var array
	     *	@access	private
	     *	@static
	     */
		static	private	$_preparedQueries	=	array();
		
		/**
	     *	The primary key offset
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_primary	=	'id';
		
		/**
	     *	If the relations definition was set
	     *
	     *	@var bool
	     *	@access	private
	     */
		private	$_related	=	false;
		
		/**
	     *	The related records settings
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
		
		public	function	__construct(CahnoryORM_Database $database)
		{
			$this->_class		=	get_class($this);
			$this->_database	=	$database;
			$this->setDefinition();
			$this->_defined		=	true;
			
			//	Add primary column
			if($this->_primary && !isset($this->_columns[$this->_primary])) {
				$this->hasColumn($this->_primary, 'int', 11);
			}
		}
		
		public	function	__get($offset) { return	$this->getValue($offset); }
		public	function	__set($offset, $value) { return	$this->setValue($offset, $value); }
		
		public	function	find()
		{
			if(!$this->_related)	$this->setRelations();
			$access	=	$this->_database->getAccess();
			$access->query($this->getQuery('find'), array());
			$row	=	$access->fetch();
			var_dump($row, $this->_values);
			exit();
		}
		
		public	function	getQuery($name)
		{
			if(!isset(self::$_preparedQueries[$this->_class])) $this->_prepareQueries();
			
			return	isset(self::$_preparedQueries[$this->_class][$name])
				?	self::$_preparedQueries[$this->_class][$name]
				:	NULL;
		}
		
		public	function	getValue($offset)
		{
			if(!array_key_exists($offset, $this->_values)) {
				return	$this->_values[$offset];
			}
		}
		
		/**
		 *	Defines the primary key offset
		 *
		 *	@param string $offset the column offset
		 *
		 *	@access protected
		 */
		protected	function	hasPrimary($offset)
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
		protected	function	hasTable($table)
		{
			$this->_table	=	$table;
		}
		
		/**
		 *	Defines a column
		 *
		 *	@param string $offset  the column offset
		 *	@param string $type    the column type
		 *	@param string $value   the column type value
		 *	@param string $default the column default value
		 *
		 *	@access protected
		 */
		protected	function	hasColumn($offset, $type = NULL, $value = NULL, $default = NULL)
		{
			if(is_array($type)) {
				$column	=	array(
					'type'		=>	isset($type['type'])	? $type['type']		: NULL,
					'value'		=>	isset($type['value'])	? $type['value']	: NULL,
					'default'	=>	isset($type['default'])	? $type['default']	: NULL
				);
			} else {
				$column	=	array(
					'type'		=>	$type,
					'value'		=>	$value,
					'default'	=>	$default
				);
			}
			$this->_columns[$offset]	=	$column;
			$this->_values[$offset]		=	$column['default'];
			$this->_offsets[]			=	$offset;
		}
		
		/**
		 *	Defines a single relation
		 *
		 *	@param string $name    the record name
		 *	@param string $offset  the column offset
		 *	@param array  $options the relation options
		 *
		 *	@return	bool
		 *
		 *	@access protected
		 */
		protected	function	hasOne($name, $offset = array(), $options = NULL)
		{
			if(!$this->_defined)	return	false;
			if(!is_array($offset)) {
				$options	=	is_array($options) ? $options : array();
			} else {
				//	record name used as offset
				$options	=	$offset;
				$offset		=	$name;
			}
			if($record = $this->_database->getTable($name)) {				
				//	Add relation table to 'from' clause
				$relation		=	array(
					'record'	=>	$record,
					'foreign'	=>	isset($options['foreign']) ? $options['foreign'] : $record->_primary,
					'local'		=>	isset($options['local']) ? $options['local'] : $this->_primary,
					'type'		=>	isset($options['type']) ? $options['type'] : 'LEFT JOIN'
				);
			}
			$this->_relations[$offset]	=	$relation;
			$this->_offsets[]			=	$offset;
			return	true;
		}
		
		private	function	_prepareQueries()
		{
			$select	=	$this->_columns
					?	'`a0`.`'.implode('`,`a0`.`', array_keys($this->_columns)).'`'
					:	NULL;
			$from	=	'`'.$this->_table.'` `a0`';
			$i		=	1;
			foreach($this->_relations as $key => $relation) {
				$select	.=	$relation['record']->_columns
						?	',`a'.$i.'`.`'.implode('`,`a'.$i.'`.`', array_keys($relation['record']->_columns)).'`'
						:	NULL;
				$from	.=	' '.$relation['type'].' `'.$relation['record']->_table.'` `a'.$i.'`'
						.	' ON `a'.$i.'`.`'.$relation['foreign'].'` = `a0`.`'.$relation['local'].'`';				
				$i++;
			}
			self::$_preparedQueries[$this->_class]			=	array();
			self::$_preparedQueries[$this->_class]['find']	=	'SELECT '.trim($select,',').' FROM '.$from;
		}
		
		private	function	_prepareRelations()
		{
			$this->setRelations();
			$this->_related	=	true;
		}
		
		protected	function	setDefinition() {}
		protected	function	setRelations() {}
		
		public		function	setValue($offset, $value)
		{
			if(!array_key_exists($offset, $this->_values)) {
				$this->_values[$offset]	=	$value;
			}
		}
		
		/* !Iterator implement */
		function rewind() {
			$this->_offset = 0;
		}
	
		function current() {
			return $this->getValue($this->_offsets[$this->_offset]);
		}
	
		function key() {
			return $this->_offsets[$this->_offset];
		}
	
		function next() {
			++$this->_offset;
		}
	
		function valid() {
			if(!$this->_related)	$this->_prepareRelations();
			return	isset($this->_offsets[$this->_offset]);
		}
		
		/* !ArrayAccess implement */
		public function offsetSet($offset, $value)
		{
			$this->setValue($offset, $value);
		}
		public function offsetExists($offset)
		{
			return isset($this->_values[$offset]) || isset($this->_relations[$offset]);
		}
		public function offsetUnset($offset)
		{
			$this->setValue($offset, NULL);
		}
		public function offsetGet($offset)
		{
			return	$this->getValue($offset);
		}	
	}

?>