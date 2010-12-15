<?php

	abstract class CahnoryDB_Record implements Iterator, ArrayAccess
	{
		
		/**
	     *	The last record id set
	     *
	     *	@var CahnoryDB
	     *	@access	private
	     *	@static
	     */
		static	private	$_lastRecordID = 0;
		
		/**
	     *	The database relation object
	     *
	     *	@var CahnoryDB
	     *	@access	private
	     */
		private	$_db;
		
		/**
	     *	The record unique id
	     *
	     *	@var int
	     *	@access	private
	     */
		private	$_id;
		
		/**
	     *	The record class name
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_className;
		
		/**
	     *	The table name in the database
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_table;
		
		/**
	     *	The record table alias
	     *
	     *	The unicity is based on the record ID
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_alias;
		
		/**
	     *	The record name
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_recordName;
		
		/**
	     *	The record trail name
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_recordNameTrail	=	array();
		
		/**
	     *	The primary key
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_primary	=	'id';		
		
		/**
	     *	The records columns definitions
	     *	Each definition has at least name, type, value and default.
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_columns	=	array();
		
		/* !Relations */
		
		/**
	     *	The records relations definitions
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_relations	=	array();
		
		/**
	     *	The records relations foreign keys
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_foreign	=	array();
		
		/**
	     *	The records relations local keys
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_local	=	array();
		
		/**
	     *	The records relations record references
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_references	=	array();
		
		/**
	     *	The relation preparation state
	     *
	     *	@var bool
	     *	@access	private
	     */
		private	$_related	=	false;
		
		/**
	     *	The parent record
	     *
	     *	@var Recod
	     *	@access	private
	     */
		private	$_isOneOf;
		
		/* !Values */
		
		/**
	     *	The default values
	     *
	     *	Used when a value wasn't filled
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_defaultValues	=	array();
		
		/**
	     *	The filled values
	     *
	     *	Explicitely filled values. Values could be null here.
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_filledValues	=	array();
		
		/**
	     *	The record values
	     *
	     *	Use filled values if exist or default values if not.
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_values		=	array();
		
		/**
	     *	The record offsets
	     *
	     *	Columns and relations names
	     *
	     *	@var array
	     *	@access	private
	     */
		private	$_offsets		=	array();
		
		/**
	     *	The current record offset (iteration)
	     *
	     *	@var int
	     *	@access	private
	     */
		private	$_offset		=	0;
		
		/**
	     *	The number of filled values.
	     *
	     *	@var int
	     *	@access	private
	     */		
		private	$_nbFilledValues	=	0;
		
		/**
	     *	The save state of the record
	     *
	     *	If object wasn't created using a query result or
	     *	if one or more of its values were modified since
	     *	its creation.
	     *
	     *	@var array
	     *	@access	private
	     */		
		private	$_saved			=	true;
		
		/**
	     *	The save state of the record and its related records
	     *
	     *	@var array
	     *	@access	private
	     *	@see Record::$_saved;
	     */		
		private	$_deeplySaved	=	true;
		
		/* !Querys */
		
		/**
	     *	The SQL query 'FROM' clause
	     *
	     *	Compiled using the record table and the related
	     *	records tables joins.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_queryFrom;
		
		/**
	     *	The SQL query 'INSERT' clause
	     *
	     *	Compiled using the record columns and the related
	     *	records columns joined by ','.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_queryInsert	=	array('values'=>NULL,'columns'=>NULL,'updates'=>NULL);
		
		/**
	     *	The SQL query 'SELECT' clause
	     *
	     *	Compiled using the record columns and the related
	     *	records columns joined by ','.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_querySelect;
		
		/**
	     *	The SQL query 'WHERE' clause
	     *
	     *	Compiled using the record values and the related
	     *	records values joined by ' AND '.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_queryWhere;
		
		/**
	     *	The splits
	     *
	     *	Compiled using the record values and the related
	     *	records values joined by ' AND '.
	     *
	     *	@var string
	     *	@access	private
	     */
		private	$_queryFetchSplits	=	array();
				
		final	public	function	__construct(CahnoryDB $db, $name, $class)
		{
			$this->_className	=	$class;
			$this->_db			=	$db;
			$this->_recordName	=	$name;
			$this->_prepareRecord();
		}
		
		final	public	function	__clone()
		{
			//	prepare relations here ?
			self::$_lastRecordID++;
			$this->_id	=	self::$_lastRecordID;
			$this->_alias			=	'a'.$this->_id;
			$this->_querySelect		=	str_replace('<alias>', $this->_alias, $this->_querySelect);
			$this->_queryFrom		=	' `'.$this->_table.'` `'.$this->_alias.'`';
		}
		
		public	function	__get($name)
		{
			return	$this->getValue($name);
		}
		
		public	function	__set($name, $value)
		{
			$this->setValue($name, $value);
		}
		
		public	function	__toString()
		{
			return	(string)$this->getValue($this->_primary);
		}
		
		private	function	_fetchRow(array $row)
		{
			$record	=	$this->_db->getTable($this->_recordName);
			$cursor	=	0;
			foreach($this->_queryFetchSplits as $split){
				$obj	=	$record;
				foreach($split['trail'] as $trail) {
					$obj	=	$obj->getValue($trail);
				}
				$value	=	array_combine(
					array_keys($obj->_columns),
					array_slice($row, $cursor, $split['size'])
				);
				$obj->fill($value);
				$obj->_saved	=	true;
				$cursor	+=	$split['size'];
			}
			return	$record;
		}
		
		private	function	_prepareRecord()
		{
			$this->_id		=	self::$_lastRecordID++;
			$this->_alias	=	'a'.$this->_id;
			$this->setDefinition();
			
			//	Add primary column
			if($this->_primary && !isset($this->_columns[$this->_primary])) {
				$this->hasColumn($this->_primary, 'int', 255);
			}
			$this->_queryFetchSplits[]	=	array('size' => sizeof($this->_values), 'trail' => $this->_recordNameTrail);
			$this->_queryFrom			=	' `'.$this->_table.'` `<alias>`';
			$this->_queryInsert			=	'INSERT INTO '.$this->_table.'('.trim($this->_queryInsert['columns'],',').')'
										.	' VALUES('.trim($this->_queryInsert['values'],',').')'
										.	' ON DUPLICATE KEY UPDATE '.trim($this->_queryInsert['updates'],',');
		}
		
		private	function	_prepareRelations()
		{
			$this->setRelations();
			$this->_related	=	true;
		}
		
		private	function	_prepareFilter(array &$params = array())
		{
			if($this->_queryWhere && $this->_deeplySaved)	return	$this->_queryWhere;
			if($this->_filledValues) {
				$this->_queryWhere	=	NULL;
				foreach($this->_filledValues as $name => $value) {
					$this->_queryWhere	.=	$this->_alias.'.'.$name.' = ?';
					$params[]			=	$value;
				}
				$this->_queryWhere	=	array($this->_queryWhere);
			} else {
				$this->_queryWhere	=	array();
			}
			foreach($this->_relations as $record) {
				$record->_prepareFilter($params);
				if($record->_queryWhere) {
					$this->_queryWhere[]	=	$record->_queryWhere;
				}
			}
			$this->_queryWhere	=	implode(' AND ', $this->_queryWhere);
			return	$params;
		}
		
		public	function	fill($values)
		{
			foreach($values as $name => $value) {
				$this->setValue($name, $value);
			}
		}
		
		public	function	find($primary = NULL)
		{
			if(!$this->_related)	$this->_prepareRelations();
			
			if($primary !== NULL) {
				$id	=	$this->getValue($this->_primary);
				if($id === NULL)	$id	=	true;
				$this->setValue($this->_primary, $primary);
			}
			$params	=	$this->_prepareFilter();
			
			$query	=	'SELECT'.trim($this->_querySelect,',')
					.	' FROM'.$this->_queryFrom;
			if($this->_queryWhere)
				$query	.=	' WHERE '.$this->_queryWhere;
			
			$access	=	$this->_db->getAccess();
			$access->query($query, $params);
			
			if(isset($id) && $row = $access->fetch()) {
				$this->setValue($this->_primary, $id);
				$result	=	$this->_fetchRow($row);
			}else {
				$result	=	array();				
				while($row = $access->fetch()) {
					$result[]	=	$this->_fetchRow($row);
				}
			}
			return	$result;		
		}
		
		public	function	getValue($name)
		{
			if(!$this->_related)	$this->_prepareRelations();
			if(isset($this->_values[$name])) {
				return	$this->_values[$name];
			} elseif(isset($this->_relations[$name])) {
				if(array_key_exists($name, $this->_references) && !is_array($this->_relations[$name])) {
					$this->_relations[$name]	=	$this->_relations[$name]->find();
				}
				return	$this->_relations[$name];
			}
		}
		
		/**
		 *	Defines a column
		 *
		 *	@param string $name    the column name
		 *	@param string $type    the column type
		 *	@param string $value   the column type value
		 *	@param string $default the column default value
		 *
		 *	@access protected
		 */
		protected	function	hasColumn($name, $type = NULL, $value = NULL, $default = NULL)
		{
			if(is_array($type)) {
				$this->_columns[$name]	=	array(
					'type'		=>	isset($type['type'])	? $type['type']		: NULL,
					'value'		=>	isset($type['value'])	? $type['value']	: NULL,
					'default'	=>	isset($type['default'])	? $type['default']	: NULL
				);
			} else {
				$this->_columns[$name]	=	array(
					'type'		=>	$type,
					'value'		=>	$value,
					'default'	=>	$default
				);
			}
			$this->_defaultValues[$name]	=	$this->_columns[$name]['default'];
			$this->_values[$name]			=	$this->_columns[$name]['default'];
			$this->_offsets[]				=	$name;
			
			//	Query preparation
			$this->_querySelect				.=	' `<alias>`.`'.$name.'`,';
			$this->_queryInsert['values']	.=	'?,';
			$this->_queryInsert['columns']	.=	''.$name.',';
			$this->_queryInsert['updates']	.=	''.$name.' = VALUES('.$name.'),';
		}
		
		/**
		 *	Defines a one to many relation
		 *
		 *	@param string $name  the record name
		 *	@param string $alias the local column name
		 *	@param array $opt    the relation options
		 *
		 *	@access protected
		 */
		public	function	hasMany($name, $alias = array(), $opt = NULL)
		{
			//	Alias usage
			if(!is_array($alias)) {
				$opt	=	is_array($opt) ? $opt : array();
			} else {
				$opt	=	$alias;
				$alias	=	$name;
			}
			
			//	If the record exists
			if($record = $this->_db->getTable($name)) {
				$this->_references[$alias]	=	(isset($opt['refRecord']) ? $opt['refRecord'] : NULL);
				$this->_foreign[$alias]		=	(isset($opt['foreign']) ? $opt['foreign'] : $record->_primary);
				$this->_local[$alias]		=	(isset($opt['local']) ? $opt['local'] : $this->_primary);
									
				$this->_relations[$alias]	=	$record;
				$this->_offsets[]			=	$alias;
				
				if($this->_references[$alias] !== NULL) {
					//	many to many
				} else {
					$record->setValue($this->_foreign[$alias], $record->{$this->_local[$alias]});
				}
				
				$record->_isOneOf			=	$this;
			}
			
		}
		
		/**
		 *	Defines a single relation
		 *
		 *	@param string $name  the record name
		 *	@param string $alias the local column name
		 *	@param string $opt   the relation options
		 *
		 *	@access protected
		 */
		protected	function	hasOne($name, $alias = array(), $opt = NULL)
		{
			//	Alias usage
			if(!is_array($alias)) {
				$opt	=	is_array($opt) ? $opt : array();
			} else {
				$opt	=	$alias;
				$alias	=	$name;
			}
			
			//	If the record exists
			if($record = $this->_db->getTable($name)) {				
				//	Add relation table to 'from' clause
				$this->_foreign[$alias]		=	(isset($opt['foreign']) ? $opt['foreign'] : $record->_primary);
				$this->_local[$alias]		=	(isset($opt['local']) ? $opt['local'] : $this->_primary);
									
				$this->_relations[$alias]	=	$record;
				$this->_offsets[]			=	$alias;
				
				$record->_isOneOf			=	$this;
				$record->_recordNameTrail	=	$this->_recordNameTrail;
				$record->_recordNameTrail[]	=	$alias;
				
				$this->_querySelect			.=	$record->_querySelect;
				$this->_queryFrom			.=	' LEFT JOIN `'.$record->_table.'` `'.$record->_alias
											.	'` ON `'.$record->_alias.'`.`'.$this->_foreign[$alias]
											.	'` = `'.$this->_alias.'`.`'.$this->_local[$alias].'`';
				$this->_queryFetchSplits[]	=	array('size' => sizeof($record->_values), 'trail' => $record->_recordNameTrail);
			}
		}
		
		/**
		 *	Defines the primary key
		 *
		 *	@param string $column the column name
		 *
		 *	@access protected
		 */
		protected	function	hasPrimary($column)
		{
			$this->_primary	=	$column;
		}
		
		/**
		 *	Defines the table name
		 *
		 *	@param string $name the table name
		 *
		 *	@access protected
		 */
		protected	function	hasTable($name)
		{
			$this->_table	=	$name;
		}
		
		public	function	isEmpty()
		{
			return	$this->_nbFilledValues < 1;
		}
		
		public	function	isSaved()
		{
			return	$this->_saved;
		}
		
		public	function	save()
		{
			if(!$this->_saved) {
				$access	=	$this->_db->getAccess();
				$access->query($this->_queryInsert, array_values($this->_values));
				
				if($access->errorCode() == '00000') {
					if($this->getValue($this->_primary) === NULL) {
						$this->setValue($this->_primary, $access->lastInsertId());
					}
					$this->_saved	=	true;
				} else {
					return false;
				}
			}
			
			if($this->_saved) {			
				//	Save relations
				foreach($this->_relations as $name => $record) {
					if(!$record->_saved) {
						$record->setValue($this->_foreign[$name], $this->getValue($this->_local[$name]));
						if($record->save()) {
							$this->setValue($this->_local[$name], $record->getValue($this->_foreign[$name]));
						}
					}
				}
			}
			return	true;
		}
		
		protected	function	setDefinition() {}
		protected	function	setRelations() {}
		
		public	function	setValue($name, $value)
		{
			if(!$this->_related)	$this->_prepareRelations();
			if(array_key_exists($name, $this->_values)) {
				if($value === '' || $value === false)	$value	=	NULL;
				if((!array_key_exists($name, $this->_filledValues) && $value !== NULL)
				|| (array_key_exists($name, $this->_filledValues) && $this->_filledValues[$name] != $value)) {
					$this->_values[$name]			=	$value;
					$this->_filledValues[$name]		=	$value;
					$this->_saved					=	false;
					$this->_deeplySaved				=	false;
					if($this->_isOneOf) {
						$this->_isOneOf->_deeplySaved	=	false;
					}
					if($value !== NULL) {
						$this->_nbFilledValues++;
					} else {
						$this->_nbFilledValues--;
					}
				}
			}
		}
		
		public	function	value()
		{
			$args	=	func_get_args();
			if(sizeof($args) == 0) {
				return	$this->_values;
			} elseif(is_array($args[0])) {
				$this->fill($args[0]);
			} elseif(isset($this->_values[$key])) {
				if(sizeof($args) > 1) {
					$this->setValue($args[0], $args[1]);
				} else {
					return	$this->getValue($args[0]);
				}
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
	    public function offsetSet($offset, $value) {
	    	$this->setValue($offset, $value);
	    }
	    public function offsetExists($offset) {
	        return isset($this->_values[$offset]) || isset($this->_relations[$offset]);
	    }
	    public function offsetUnset($offset) {
	        $this->setValue($offset, NULL);
	    }
	    public function offsetGet($offset) {
	        return	$this->getValue($offset);
	    }
	}

?>