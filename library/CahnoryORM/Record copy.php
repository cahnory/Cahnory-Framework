<?php

	abstract class CahnoryORM_Record implements Iterator, ArrayAccess
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
		static	private	$_queries	=	array();
		
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
	     *	If the record values are saved
	     *
	     *	@var bool
	     *	@access	private
	     */	
		private	$_saved = false;
		
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
			
			self::$_queries[$this->_class]	=	array(
				'select'	=>	NULL,
				'updates'	=>	NULL
			);
			
			$this->setDefinition();
			$this->_defined		=	true;
			
			//	Add primary column
			if($this->_primary && !isset($this->_columns[$this->_primary])) {
				$this->hasColumn($this->_primary, 'int', 11);
			}
		}
		
		public	function	__get($offset) { return	$this->getValue($offset); }
		public	function	__set($offset, $value) { return	$this->setValue($offset, $value); }
		
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
				$this->setValue($offset, $value);
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
			if(!$this->_related)	$this->setRelations();
			
			$access	=	$this->_database->getAccess();
			$query	=	$this->getQuery('find');
			$filter	=	$this->getQuery('filter');
			var_dump($filter);
			$access->query($this->getQuery('find'), array());
			
			$output	=	array();
			while($row = $access->fetch()) {
				$output[]	=	$this->_fetch($row);
			}
			exit();
		}
		
		/**
		 *	Returns a query and make it if it wasn't
		 *
		 *	@param string $name the query name
		 *
		 *	@access protected
		 */
		public	function	getQuery($name)
		{
			if(!isset(self::$_queries[$this->_class][$name])) {
				if($name == 'find')		return	$this->_prepareFindQuery();
				if($name == 'save')		return	$this->_prepareSaveQuery();
				if($name == 'filter')	return	$this->_prepareFilterQuery();
			}
			
			return	isset(self::$_queries[$this->_class][$name])
				?	self::$_queries[$this->_class][$name]
				:	NULL;
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
		 *	Returns the value associated to an offset
		 *
		 *	@param string $offset the offset
		 *
		 *	@return mixed
		 *
		 *	@access protected
		 */
		public	function	getValue($offset)
		{
			if(!array_key_exists($offset, $this->_values)) {
				return	$this->_values[$offset];
			}
		}
		
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
		 *	@param string $value   the column type value
		 *	@param string $default the column default value
		 *
		 *	@access protected
		 */
		final protected	function	hasColumn($offset, $type = NULL, $value = NULL, $default = NULL)
		{
			//	Offset already used
			if(in_array($offset, $this->_offsets))	return false;
			
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
			
			self::$_queries[$this->_class]['select']	.=	', `<alias>`.`'.$offset.'`';
			self::$_queries[$this->_class]['updates']	.=	'`'.$offset.'` = VALUES(`'.$offset.'`), ';
			
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
		final protected	function	hasOne($name, $offset = array(), $options = NULL)
		{
			if(!$this->_defined)	return	false;
			
			if(!is_array($offset)) {
				$options	=	is_array($options) ? $options : array();
			} else {
				//	record name used as offset
				$options	=	$offset;
				$offset		=	$name;
			}
			
			//	Offset already used
			if(in_array($offset, $this->_offsets))	return false;
			
			if($record = $this->_database->getTable($name)) {
				$record->_parent	=	$this;
				$i					=	sizeof($this->_relations) + 1;
				$relation		=	array(
					'record'	=>	$record,
					'foreign'	=>	isset($options['foreign']) ? $options['foreign'] : $record->_primary,
					'local'		=>	isset($options['local']) ? $options['local'] : $this->_primary,
					'type'		=>	isset($options['type']) ? $options['type'] : 'LEFT JOIN'
				);
			
				self::$_queries[$this->_class]['select']	.=	str_replace('<alias>', 'a'.$i, self::$_queries[$record->_class]['select']);
				
				self::$_queries[$this->_class]['from']		.=	"\r".$relation['type'].' `'.$record->_table.'` `a'.$i.'`'
															.	' ON `a'.$i.'`.`'.$relation['foreign'].'` = `a0`.`'.$relation['local'].'`';
									
				$this->_relations[$offset]	=	$relation;
				$this->_offsets[]			=	$offset;
				return	true;
			}
			return	false;
		}
		
		/**
		 *	Makes the 'find' query string and store it for
		 *	all instances of this record.
		 *
		 *	@return string the query
		 *
		 *	@access private
		 */
		private	function	_prepareFindQuery()
		{
			self::$_queries[$this->_class]['find']	=	'SELECT '.trim(str_replace('<alias>','a0',self::$_queries[$this->_class]['select']), ', ')
													.	"\rFROM ".'`'.$this->_table.'` `a0`'.self::$_queries[$this->_class]['from'];
			unset(self::$_queries[$this->_class]['select'], self::$_queries[$this->_class]['from']);
			
			return	self::$_queries[$this->_class]['find'];
		}
		
		/**
		 *	Makes the 'find' query string and store it for
		 *	all instances of this record.
		 *
		 *	@return string the query
		 *
		 *	@access private
		 */
		private	function	_prepareSaveQuery()
		{
			if(!$this->_columns) {
				self::$_queries[$this->_class]['save']	=	NULL;
			} else {
				self::$_queries[$this->_class]['save']	=	'INSERT INTO `'.$this->_table.'`(`'.implode('`,`', array_keys($this->_columns)).'`)'
														.	"\rVALUES(?".str_repeat(',?', sizeof($this->_columns) - 1).')'
														.	"\rON DUPLICATE KEY UPDATE ".trim(self::$_queries[$this->_class]['updates'],', ');
			}
			unset(self::$_queries[$this->_class]['updates']);
			
			return	self::$_queries[$this->_class]['save'];
		}
		
		/**
		 *	Makes the 'find' query string and store it for
		 *	all instances of this record.
		 *
		 *	@return string the query
		 *
		 *	@access private
		 */
		private	function	_prepareFilterQuery($i = 0)
		{
			self::$_queries[$this->_class]['filter']	=	NULL;
			$params	=	array();
			$query	=	NULL;
			foreach($this->_columns as $offset => $value) {
				if($value === NULL)	continue;
				$query		.=	'`<alias>`.`'.$offset.'` = ? AND ';
				$params[]	=	$value;
			}
			$i	=	0;
			foreach($this->_relations as $relation) {
				$filter	=	$relation['record']->_prepareFilterQuery();
				$query	.=	str_replace('<alias>', 'a'.$i, $filter['query']);
				$params	=	array_merge($params, $filter['params']);
			}
			return	array('query' => trim($query,'AND '), 'params' => $params);
		}
		
		/**
		 *	Prepares the relations
		 *
		 *	@access private
		 */
		private	function	_prepareRelations()
		{
			$this->setRelations();
			$this->_related	=	true;
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
			if(!$this->_related)	$this->setRelations();
			
			if(!$this->_saved) {
				$access	=	$this->_database->getAccess();
				$access->query($this->getQuery('save'), array_values($this->_values));
				
				if($access->errorCode() == '00000') {
					if($this->getValue($this->_primary) === NULL) {
						$this->setValue($this->_primary, $access->lastInsertId());
					}
					$this->_saved	=	true;
				} else {
					return false;
				}
			}
			
			$loop	=	false;
			if($this->_saved) {			
				//	Save relations
				foreach($this->_relations as $offset => $relation) {
					if($relation['local'] == $this->_primary) {
						$relation['record']->setValue($relation['foreign'], $this->getValue($relation['local']));
					}
					if(!$relation['record']->_saved) {
						if($relation['record']->save()) {
							if($relation['record']->getValue($relation['foreign']) != $this->getValue($relation['local'])) {
								$this->setValue($relation['local'], $relation['record']->getValue($relation['foreign']));
								$loop	=	true;
							}
						}
					}
				}
			}
			return	$loop ? $this->save() : true;
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
				
		/**
		 *	Set a record value
		 *
		 *	@param $offset the column offset
		 *	@param $value  the new value
		 *
		 *	@access public
		 */
		public		function	setValue($offset, $value)
		{
			if(array_key_exists($offset, $this->_values) && $this->_values[$offser] !== $value) {
				$this->_values[$offset]	=	$value;
				$this->_saved			=	false;
			}
		}
		
		/* !Iterator implement */
		public function rewind()
		{
			$this->_offset = 0;
		}
	
		public function current()
		{
			return $this->getValue($this->_offsets[$this->_offset]);
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
			return in_array($offset, $this->_offsets);
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